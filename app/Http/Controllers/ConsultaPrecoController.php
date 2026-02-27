<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaPrecoRequest;
use App\Http\Requests\UpdateConsultaPrecoRequest;
use App\Models\Cliente;
use App\Models\ConsultaPreco;
use App\Models\ConsultaPrecoFornecedor;
use App\Models\ConsultaPrecoGrupo;
use App\Models\Cor;
use App\Models\Fornecedor;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsultaPrecoController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX — lista todos os grupos de cotação
    // ──────────────────────────────────────────────────────────
    public function index()
    {
        // Expira automaticamente grupos vencidos
        ConsultaPrecoGrupo::where('status', 'Disponível')
            ->where('validade', '<', now())
            ->update(['status' => 'Expirado']);

        $grupos = ConsultaPrecoGrupo::with(['cliente', 'usuario', 'itens'])
            ->latest()
            ->paginate(20);

        return view('paginas.produtos.consulta_precos.index', compact('grupos'));
    }

    // ──────────────────────────────────────────────────────────
    // CREATE — formulário com múltiplos itens
    // ──────────────────────────────────────────────────────────
    public function criar_cotacao($cliente_id)
    {
        $cliente      = Cliente::findOrFail($cliente_id);
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores        = Cor::orderBy('nome')->get();

        return view('paginas.produtos.consulta_precos.create', compact(
            'cliente', 'fornecedores', 'cores'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // STORE — salva grupo + múltiplos itens
    // ──────────────────────────────────────────────────────────
    public function store(StoreConsultaPrecoRequest $request)
    {
        DB::beginTransaction();

        try {
            // 1. Cria o grupo
            $grupo = ConsultaPrecoGrupo::create([
                'cliente_id'  => $request->cliente_id,
                'usuario_id'  => auth()->id(),
                'status'      => 'Pendente',
                'observacao'  => $request->observacao_geral ?? null,
            ]);

            // 2. Cria cada item do grupo
            foreach ($request->itens as $itemData) {
                $item = ConsultaPreco::create([
                    'grupo_id'      => $grupo->id,
                    'cliente_id'    => $request->cliente_id,
                    'usuario_id'    => auth()->id(),
                    'descricao'     => $itemData['descricao'],
                    'quantidade'    => $itemData['quantidade'],
                    'cor_id'        => $itemData['cor_id'] ?? null,
                    'part_number'   => $itemData['part_number'] ?? null,
                    'observacao'    => $itemData['observacao'] ?? null,
                    'status'        => 'Pendente',
                ]);

                // 3. Vincula fornecedor(es) sugeridos pelo vendedor (opcional)
                if (!empty($itemData['fornecedor_ids'])) {
                    foreach ($itemData['fornecedor_ids'] as $fornecedorId) {
                        if (empty($fornecedorId)) continue; // ✅ ignora seleção vazia
                        ConsultaPrecoFornecedor::create([
                            'consulta_preco_id' => $item->id,
                            'fornecedor_id'     => $fornecedorId,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('consulta_preco.show_grupo', $grupo->id)
                ->with('success', 'Cotação criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar cotação: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao criar cotação: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // SHOW GRUPO — visualiza um grupo de cotação completo
    // ──────────────────────────────────────────────────────────
    public function showGrupo($grupoId)
    {
        $grupo = ConsultaPrecoGrupo::with([
            'cliente',
            'usuario',
            'orcamento',
            'itens.cor',
            'itens.fornecedores.fornecedor',
            'itens.fornecedorSelecionado.fornecedor',
        ])->findOrFail($grupoId);

        // Expira se necessário
        $grupo->verificarExpiracao();
        $grupo->refresh();

        return view('paginas.produtos.consulta_precos.show_grupo', compact('grupo'));
    }

    // ──────────────────────────────────────────────────────────
    // SHOW ITEM — visualiza item individual (mantém compatibilidade)
    // ──────────────────────────────────────────────────────────
    public function show(ConsultaPreco $consulta)
    {
        $consulta->load(['cor', 'fornecedores.fornecedor', 'fornecedorSelecionado.fornecedor', 'grupo.cliente']);
        return view('paginas.produtos.consulta_precos.show', compact('consulta'));
    }

    // ──────────────────────────────────────────────────────────
    // EDIT ITEM — compras preenche preços por fornecedor
    // ──────────────────────────────────────────────────────────
    public function edit($consult_id)
    {
        $consulta     = ConsultaPreco::with(['fornecedores.fornecedor', 'cor'])->findOrFail($consult_id);
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores        = Cor::orderBy('nome')->get();

        return view('paginas.produtos.consulta_precos.edit', compact('consulta', 'fornecedores', 'cores'));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE ITEM — compras salva preços e seleciona fornecedor
    // ──────────────────────────────────────────────────────────
    public function update(UpdateConsultaPrecoRequest $request, $consulta_id)
    {
        $consultaPreco = ConsultaPreco::findOrFail($consulta_id);

        DB::beginTransaction();

        try {
            // Atualiza dados básicos do item
            $consultaPreco->update([
                'descricao'    => $request->descricao,
                'quantidade'   => $request->quantidade,
                'cor_id'       => $request->cor_id ?? null,
                'part_number'  => $request->part_number ?? null,
                'observacao'   => $request->observacao ?? null,
                'comprador_id' => auth()->id(),
                //  só marca Disponível se houver seleção
                'status' => $request->filled('fornecedor_selecionado_id') ? 'Disponível' : $consultaPreco->status,
            ]);

            // Salva/atualiza preços por fornecedor
            if ($request->has('fornecedores')) {
                foreach ($request->fornecedores as $fornData) {
                    ConsultaPrecoFornecedor::updateOrCreate(
                        [
                            'consulta_preco_id' => $consultaPreco->id,
                            'fornecedor_id'     => $fornData['fornecedor_id'],
                        ],
                        [
                            'preco_compra'  => $this->brToDecimal($fornData['preco_compra'] ?? null),
                            'preco_venda'   => $this->brToDecimal($fornData['preco_venda'] ?? null),
                            'prazo_entrega' => $fornData['prazo_entrega'] ?? null,
                            'selecionado'   => false,
                            'observacao'    => $fornData['observacao'] ?? null,
                        ]
                    );
                }
            }

            // Marca fornecedor selecionado
            // DEPOIS
            ConsultaPrecoFornecedor::where('consulta_preco_id', $consultaPreco->id)
                ->update(['selecionado' => false]);

            if ($request->filled('fornecedor_selecionado_id')) {
                ConsultaPrecoFornecedor::where('consulta_preco_id', $consultaPreco->id)
                    ->where('fornecedor_id', $request->fornecedor_selecionado_id)
                    ->update(['selecionado' => true]);

                $fornSelecionado = ConsultaPrecoFornecedor::where('consulta_preco_id', $consultaPreco->id)
                    ->where('fornecedor_id', $request->fornecedor_selecionado_id)
                    ->first();

                if ($fornSelecionado) {
                    $consultaPreco->update([
                        'fornecedor_id' => $request->fornecedor_selecionado_id,
                        'preco_compra'  => $fornSelecionado->preco_compra,
                        'preco_venda'   => $fornSelecionado->preco_venda,
                        'prazo_entrega' => $fornSelecionado->prazo_entrega,
                        'status'        => 'Disponível',
                    ]);
                }
            }

            // Verifica se todos os itens do grupo estão disponíveis
            $grupo = $consultaPreco->grupo;
            if ($grupo) {
                if ($grupo->todosItensDisponiveis()) {
                    // Todos prontos — marca disponível e reinicia prazo de 48h
                    $grupo->marcarDisponivel();
                } else {
                    // Ainda tem itens sem seleção — volta para Pendente
                    $grupo->update([
                        'status'   => 'Pendente',
                        'validade' => null,
                    ]);
                }
            }

            DB::commit();

            $this->gerarCotacaoPdf($consultaPreco);

            return redirect()
                ->route('consulta_preco.show_grupo', $consultaPreco->grupo_id)
                ->with('success', 'Item atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar cotação: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // ADICIONAR FORNECEDOR A UM ITEM (AJAX/POST)
    // ──────────────────────────────────────────────────────────
    public function adicionarFornecedor(Request $request, $consultaId)
    {
        $request->validate([
            'fornecedor_id' => 'required|exists:fornecedores,id',
        ]);

        $jaExiste = ConsultaPrecoFornecedor::where('consulta_preco_id', $consultaId)
            ->where('fornecedor_id', $request->fornecedor_id)
            ->exists();

        if ($jaExiste) {
            return back()->with('warning', 'Este fornecedor já está vinculado a este item.');
        }

        ConsultaPrecoFornecedor::create([
            'consulta_preco_id' => $consultaId,
            'fornecedor_id'     => $request->fornecedor_id,
        ]);

        return back()->with('success', 'Fornecedor adicionado!');
    }

    // ──────────────────────────────────────────────────────────
    // APROVAR GRUPO — vendedor marca como aprovado e gera orçamento
    // ──────────────────────────────────────────────────────────
    public function aprovarGrupo(Request $request, $grupoId)
    {
        $grupo = ConsultaPrecoGrupo::with(['itens.fornecedorSelecionado', 'cliente'])->findOrFail($grupoId);

        if ($grupo->status === 'Expirado') {
            return back()->with('error', 'Esta cotação está expirada. Solicite nova cotação.');
        }

        if ($grupo->status !== 'Disponível') {
            return back()->with('error', 'A cotação ainda não está disponível para aprovação.');
        }

        DB::beginTransaction();

        try {
            // Marca grupo como aprovado
            $grupo->update(['status' => 'Aprovado']);

            // Emite evento/notificação se necessário
            Log::info("Grupo de cotação #{$grupo->id} aprovado pelo vendedor " . auth()->id());

            DB::commit();

            return redirect()
                ->route('consulta_preco.show_grupo', $grupo->id)
                ->with('success', 'Cotação aprovada! Agora você pode gerar o orçamento.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aprovar: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // GERAR ORÇAMENTO A PARTIR DO GRUPO
    // ──────────────────────────────────────────────────────────
    public function gerarOrcamento(Request $request, $grupoId)
    {
        $grupo = ConsultaPrecoGrupo::with(['itens.fornecedorSelecionado', 'cliente'])->findOrFail($grupoId);

        if (!in_array($grupo->status, ['Aprovado'])) {
            return back()->with('error', 'Apenas grupos aprovados podem gerar orçamento.');
        }

        if ($grupo->orcamento_id) {
            return redirect()
                ->route('orcamentos.show', $grupo->orcamento_id)
                ->with('info', 'Este grupo já possui um orçamento gerado.');
        }

        DB::beginTransaction();

        try {
            // Cria o orçamento
            $orcamento = Orcamento::create([
                'cliente_id'       => $grupo->cliente_id,
                'vendedor_id'      => auth()->id(),
                'usuario_logado_id'=> auth()->id(),
                'status'           => 'Pendente',
                'validade'         => Carbon::now()->addDays(2),
                'observacoes'      => 'Gerado a partir da cotação #' . $grupo->id,
            ]);

            // Adiciona itens da cotação como itens do orçamento
            foreach ($grupo->itens as $item) {
                $fornSelecionado = $item->fornecedorSelecionado;
                $precoVenda      = $fornSelecionado ? (float) $fornSelecionado->preco_venda : 0;

                OrcamentoItens::create([
                    'orcamento_id'                => $orcamento->id,
                    'produto_id'                  => null, // item cotado, sem produto cadastrado ainda
                    'quantidade'                  => $item->quantidade,
                    'valor_unitario'              => $precoVenda,
                    'valor_unitario_com_desconto' => $precoVenda,
                    'valor_com_desconto'          => $precoVenda * $item->quantidade,
                    'user_id'                     => auth()->id(),
                ]);

                // Vincula o item ao orçamento
                $item->update(['orcamento_id' => $orcamento->id]);
            }

            // Vincula grupo ao orçamento
            // DEPOIS
// Vincula grupo ao orçamento
            $grupo->update(['orcamento_id' => $orcamento->id]);

            DB::commit();

// ✅ Gera o PDF do orçamento
            $pdfGerado = false;
            try {
                $pdfService = new \App\Services\OrcamentoPdfService();
                $pdfGerado  = $pdfService->gerarOrcamentoPdf($orcamento->fresh());
            } catch (\Exception $e) {
                Log::error('Erro ao gerar PDF do orçamento a partir da cotação: ' . $e->getMessage());
            }

            return redirect()
                ->route('orcamentos.show', $orcamento->id)
                ->with($pdfGerado ? 'success' : 'warning', $pdfGerado
                    ? 'Orçamento gerado com sucesso a partir da cotação!'
                    : 'Orçamento gerado, mas houve falha ao gerar o PDF. Contate o suporte.'
                );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gerar orçamento da cotação: ' . $e->getMessage());
            return back()->with('error', 'Erro ao gerar orçamento: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────
    public function destroy($consulta_id)
    {
        $consultaPreco = ConsultaPreco::findOrFail($consulta_id);
        $grupoId       = $consultaPreco->grupo_id;
        $consultaPreco->delete();

        return redirect()
            ->route('consulta_preco.show_grupo', $grupoId)
            ->with('success', 'Item removido com sucesso.');
    }

    public function destroyGrupo($grupoId)
    {
        $grupo = ConsultaPrecoGrupo::findOrFail($grupoId);
        $grupo->itens()->delete();
        $grupo->delete();

        return redirect()
            ->route('consulta_preco.index')
            ->with('success', 'Cotação excluída com sucesso.');
    }

    // ──────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ──────────────────────────────────────────────────────────
    private function brToDecimal($valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        $valor = str_replace('.', '', trim($valor));
        $valor = str_replace(',', '.', $valor);
        return (float) $valor;
    }

    private function gerarCotacaoPdf(ConsultaPreco $cotacao): bool
    {
        try {
            $cotacao->load(['grupo.cliente', 'fornecedores.fornecedor', 'cor']);

            $token        = Str::uuid();
            $tokenExpiraEm = Carbon::now()->addDays(2);
            $linkSeguro   = route('cotacoes.view', ['token' => $token]);
            $qrCodeBase64 = base64_encode(
                QrCode::format('png')->size(130)->margin(1)->generate($linkSeguro)
            );

            $dadosPdf = [
                'cotacao'    => $cotacao,
                'qrCode'     => $qrCodeBase64,
                'linkSeguro' => $linkSeguro,
                'versao'     => $cotacao->versao ?? 1,
            ];

            $pdf        = Pdf::loadView('documentos_pdf.cotacao', $dadosPdf)->setPaper('a4');
            $canvas     = $pdf->getDomPDF()->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $text = "Página $pageNumber / $pageCount";
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $canvas->text(270, 820, $text, $font, 10);
            });

            $nomeArquivo = "cotacao_{$cotacao->id}_v{$cotacao->versao}.pdf";
            $path        = "cotacoes/{$nomeArquivo}";

            Storage::disk('public')->makeDirectory('cotacoes');
            Storage::disk('public')->put($path, $pdf->output());

            if (Storage::disk('public')->exists($path)) {
                $cotacao->update([
                    'token_acesso'    => $token,
                    'token_expira_em' => $tokenExpiraEm,
                    'pdf_path'        => $path,
                ]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF para cotação #{$cotacao->id}: " . $e->getMessage());
            return false;
        }
    }

    public function visualizarCotacao($token)
    {
        try {
            $cotacao = ConsultaPreco::where('token_acesso', $token)
                ->where('token_expira_em', '>', Carbon::now())
                ->firstOrFail();

            if (!$cotacao->pdf_path || !Storage::disk('public')->exists($cotacao->pdf_path)) {
                if (!$this->gerarCotacaoPdf($cotacao)) {
                    abort(500, 'Erro ao gerar PDF da cotação');
                }
            }

            $pdfPath = Storage::disk('public')->path($cotacao->pdf_path);

            return response()->file($pdfPath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="cotacao_' . $cotacao->id . '.pdf"',
            ]);

        } catch (\Exception $e) {
            abort(404, 'Cotação não encontrada ou token expirado');
        }
    }
}
