<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrcamentoRequest;
use App\Http\Requests\UpdateOrcamentoRequest;
use App\Models\Cliente;
use App\Models\Cor;
use App\Models\Endereco;
use App\Models\Fornecedor;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\OrcamentoItem;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\TipoTransporte;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DragonCode\Contracts\Cashier\Auth\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrcamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paginas.orcamentos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    public function clienteOrcamento($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        if (!$cliente) {
            return redirect()->route('clientes.index')->with('error', 'Cliente não encontrado.');
        }
        return view('paginas.orcamentos.cliente_index', compact('cliente'));
    }


    public function criarOrcamento($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        $vendedores = User::whereHas('vendedor')->get();
        $opcoesTransporte = TipoTransporte::all();
        return view('paginas.orcamentos.create', compact('produtos', 'cliente', 'fornecedores', 'cores', 'vendedores', 'opcoesTransporte'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrcamentoRequest $request)
    {
        // 1) Definir desconto percentual (cliente x vendedor)
        $descontoPercentual = null;

        $request->merge([
            'desconto_especifico' => str_replace(',', '.', str_replace('.', '', $request->desconto_especifico)),
            'guia_recolhimento' => str_replace(',', '.', str_replace('.', '', $request->guia_recolhimento)),
            'desconto_aprovado' => str_replace(',', '.', str_replace('.', '', $request->desconto_aprovado)),
        ]);

        if ($request->filled('desconto_aprovado') || $request->filled('desconto')) {
            $descontoPercentual = max(
                (float) $request->desconto_aprovado ?? 0,
                (float) $request->desconto ?? 0
            );
        }

        // 2) Definir desconto específico em valor (reais)
        $descontoEspecifico = $request->filled('desconto_especifico')
            ? (float) $request->desconto_especifico
            : null;

        if ($request->valor_total == "0,00") {
            $request->merge(['valor_total' => 0]);
        }

        // Criação do orçamento (sem endereço ainda)
        $orcamento = Orcamento::create([
            'cliente_id'   => $request->cliente_id,
            'vendedor_id'  => Auth()->user()->id,
            'obra'         => $request->nome_obra,
            'valor_total_itens'  => $request->valor_total,
            'status'       => 'pendente',
            'guia_recolhimento'  => $request->guia_recolhimento ?? 0,
            'observacoes'  => $request->observacoes,
            'validade'     => Carbon::now()->addDays(2), // sempre +2 dias
        ]);


        if ($request->tipos_transporte) {
            $orcamento->transportes()->sync($request->tipos_transporte); 
        }

        if ($request->has('itens')) {
            foreach ($request->itens as $item) {
                $valorUnitario = $item['preco_unitario'] ?? 0;
                $quantidade    = $item['quantidade'] ?? 0;
                $subtotal      = $valorUnitario * $quantidade;
                $valornitariodesconto = $item['preco_unitario_com_desconto'] ?? null;

                // Aplica desconto percentual no item (se existir)
                $valorComDesconto = $subtotal;
                if ($descontoPercentual) {
                    $valorComDesconto = $subtotal - ($subtotal * ($descontoPercentual / 100));
                }

                $orcamento->itens()->create([
                    'produto_id'         => $item['id'],
                    'quantidade'         => $quantidade,
                    'valor_unitario'     => $valorUnitario,
                    'valor_unitario_com_desconto' => $valornitariodesconto,
                    'desconto'           => $descontoPercentual ?? 0,
                    'valor_com_desconto' => $valorComDesconto,
                    'user_id'            => $request->user()->id ?? null,
                ]);
            }
        }

        // 5) Criar vidros
        if ($request->has('vidros')) {
            foreach ($request->vidros as $vidro) {
                if ($vidro['preco_m2'] && $vidro['quantidade'] && $vidro['altura'] && $vidro['largura']) {
                    $orcamento->vidros()->create([
                        'descricao'            => $vidro['descricao'] ?? null,
                        'quantidade'           => $vidro['quantidade'] ?? 0,
                        'altura'               => $vidro['altura'] ?? 0,
                        'largura'              => $vidro['largura'] ?? 0,
                        'preco_metro_quadrado' => $vidro['preco_m2'] ?? 0,
                        'desconto'             => $descontoPercentual ?? 0,
                        'valor_total'         => $vidro['valor_total'] ?? 0,
                        'valor_com_desconto'   => $vidro['valor_com_desconto'] ?? 0,
                        'user_id'              => $request->user()->id ?? null,
                    ]);
                }
            }
        }

        // 6) Salvar descontos na tabela "descontos"
        if ($descontoPercentual) {
            $orcamento->descontos()->create([
                'motivo'      => 'Desconto percentual aplicado (cliente ou vendedor)',
                'valor'       => 0,
                'porcentagem' => $descontoPercentual,
                'tipo'        => 'percentual',
                'cliente_id'  => $request->cliente_id,
                'user_id'     => Auth()->id(),
            ]);
        }

        if ($descontoEspecifico) {
            $orcamento->descontos()->create([
                'motivo'      => 'Desconto específico em reais',
                'valor'       => $descontoEspecifico,
                'porcentagem' => null,
                'tipo'        => 'fixo',
                'cliente_id'  => $request->cliente_id,
                'user_id'     => Auth()->id(),
            ]);
        }

        // Se o request trouxe endereço de entrega → cria/atualiza
        if ($request->filled('endereco_cep')) {
            $endereco = Endereco::updateOrCreate(
                [
                    'tipo'       => 'entrega',
                    'cliente_id' => $request->cliente_id,
                ],
                array_filter([
                    'cep'        => $request->endereco_cep,
                    'logradouro' => $request->endereco_logradouro,
                    'numero'     => $request->endereco_numero,
                    'complemento' => $request->endereco_compl,
                    'bairro'     => $request->endereco_bairro,
                    'cidade'     => $request->endereco_cidade,
                    'estado'     => $request->endereco_estado,
                    'tipo'       => 'entrega',
                ])
            );

            // vincula o endereço ao orçamento
            $orcamento->update(['endereco_id' => $endereco->id]);
        }

        // Gera token e expiração de 2 dias
        $token = Str::uuid();
        $tokenExpiraEm = Carbon::now()->addDays(2);

        // Atualiza modelo e mantém sincronizado
        $orcamento->forceFill([
            'token_acesso' => $token,
            'token_expira_em' => $tokenExpiraEm,
        ])->save();

        // Gera link seguro
        $linkSeguro = route('orcamentos.view', ['token' => $orcamento->token_acesso]);

        // Gera QR Code em base64 (GD processa PNG sem Imagick)
        $qrCodeBase64 = base64_encode(
            QrCode::format('png')
                ->size(130)
                ->margin(1)
                ->generate($linkSeguro)
        );

        try {
            $pdf = Pdf::loadView('documentos_pdf.orcamento', [
                'orcamento' => $orcamento,
                'percentualAplicado' => $descontoPercentual,
                'qrCode' => $qrCodeBase64,
            ])->setPaper('a4');

            // Numeração de páginas
            $canvas = $pdf->getDomPDF()->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $text = "Página $pageNumber / $pageCount";
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $canvas->text(270, 820, $text, $font, 10);
            });

            $path = "orcamentos/orcamento_{$orcamento->id}.pdf";
            Storage::disk('public')->put($path, $pdf->output());

            if (Storage::disk('public')->exists($path)) {
                $orcamento->update(['pdf_path' => $path]);

                return redirect()
                    ->route('orcamentos.index')
                    ->with('success', 'Orçamento criado com sucesso!');
            } else {
                return redirect()
                    ->route('orcamentos.index')
                    ->with('error', 'Orçamento criado, mas falha ao gerar o PDF.');
            }

        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF: " . $e->getMessage());
            return redirect()
                ->route('orcamentos.index')
                ->with('error', 'Orçamento criado, mas falha ao gerar o PDF: ' . $e->getMessage());
        }

    }

    public function visualizarPublico($token)
    {
        // Busca o orçamento pelo token
        $orcamento = Orcamento::where('token_acesso', $token)->firstOrFail();

        // ✅ Verifica se o token ainda é válido
        if (!$orcamento->token_expira_em || now()->greaterThan($orcamento->token_expira_em)) {
            abort(403, 'O link deste orçamento expirou. Solicite um novo ao vendedor.');
        }

        // ✅ Verifica se o PDF existe
        if (!$orcamento->pdf_path || !Storage::disk('public')->exists($orcamento->pdf_path)) {
            abort(404, 'PDF não encontrado.');
        }

        // ✅ Retorna o PDF para visualização inline no navegador
        return response()->file(
            Storage::disk('public')->path($orcamento->pdf_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="orcamento_' . $orcamento->id . '.pdf"',
            ]
        );
    }

    // ...

    public function duplicar($id)
    {
        $orcamentoOriginal = Orcamento::with(['itens', 'vidros', 'descontos', 'endereco'])->findOrFail($id);

        // Novo nome da obra com data e hora
        $dataHora = Carbon::now()->format('d/m/Y H:i');
        $novaObra = "{$dataHora} - {$orcamentoOriginal->obra}";

        // Criar novo orçamento
        $novoOrcamento = Orcamento::create([
            'cliente_id'   => $orcamentoOriginal->cliente_id,
            'vendedor_id'  => $orcamentoOriginal->vendedor_id,
            'obra'         => $novaObra,
            'valor_total_itens' => $orcamentoOriginal->valor_total_itens,
            'status'       => 'pendente',
            'observacoes'  => $orcamentoOriginal->observacoes,
            'frete'        => $orcamentoOriginal->frete,
            'guia_recolhimento' => $orcamentoOriginal->guia_recolhimento,
            'validade'     => Carbon::now()->addDays(2),
        ]);

        // 1) Copiar itens
        foreach ($orcamentoOriginal->itens as $item) {
            $novoOrcamento->itens()->create([
                'produto_id'         => $item->produto_id,
                'quantidade'         => $item->quantidade,
                'valor_unitario'     => $item->valor_unitario,
                'valor_unitario_com_desconto' => $item->valor_unitario_com_desconto,
                'desconto'           => $item->desconto,
                'valor_com_desconto' => $item->valor_com_desconto,
                'user_id'            => $item->user_id,
            ]);
        }

        // 2) Copiar vidros
        foreach ($orcamentoOriginal->vidros as $vidro) {
            $novoOrcamento->vidros()->create([
                'descricao'            => $vidro->descricao,
                'quantidade'           => $vidro->quantidade,
                'altura'               => $vidro->altura,
                'largura'              => $vidro->largura,
                'preco_metro_quadrado' => $vidro->preco_metro_quadrado,
                'desconto'             => $vidro->desconto,
                'valor_total'          => $vidro->valor_total,
                'valor_com_desconto'   => $vidro->valor_com_desconto,
                'user_id'              => $vidro->user_id,
            ]);
        }

        // 3) Copiar descontos
        foreach ($orcamentoOriginal->descontos as $desconto) {
            $novoOrcamento->descontos()->create([
                'motivo'      => $desconto->motivo,
                'valor'       => $desconto->valor,
                'porcentagem' => $desconto->porcentagem,
                'tipo'        => $desconto->tipo,
                'cliente_id'  => $desconto->cliente_id,
                'user_id'     => $desconto->user_id,
            ]);
        }

        // 4) Copiar endereço de entrega
        if ($orcamentoOriginal->endereco) {
            $novoEndereco = Endereco::create([
                'tipo'       => $orcamentoOriginal->endereco->tipo,
                'cliente_id' => $orcamentoOriginal->endereco->cliente_id,
                'cep'        => $orcamentoOriginal->endereco->cep,
                'logradouro' => $orcamentoOriginal->endereco->logradouro,
                'numero'     => $orcamentoOriginal->endereco->numero,
                'complemento'=> $orcamentoOriginal->endereco->complemento,
                'bairro'     => $orcamentoOriginal->endereco->bairro,
                'cidade'     => $orcamentoOriginal->endereco->cidade,
                'estado'     => $orcamentoOriginal->endereco->estado,
            ]);
            $novoOrcamento->update(['endereco_id' => $novoEndereco->id]);
        }

        // 5) Gerar token e expiração
        $novoOrcamento->update([
            'token_acesso' => Str::uuid(),
            'token_expira_em' => Carbon::now()->addDays(2),
        ]);

        // 6) Gerar PDF com QR Code
        $linkSeguro = route('orcamentos.view', ['token' => $novoOrcamento->token_acesso]);
        $qrCodeBase64 = base64_encode(
            QrCode::format('png')->size(130)->margin(1)->generate($linkSeguro)
        );

        $pdf = Pdf::loadView('documentos_pdf.orcamento', [
            'orcamento' => $novoOrcamento,
            'percentualAplicado' => $novoOrcamento->descontos->where('tipo', 'percentual')->first()?->porcentagem ?? null,
            'qrCode' => $qrCodeBase64,
        ])->setPaper('a4');

        $canvas = $pdf->getDomPDF()->getCanvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
            $text = "Página $pageNumber / $pageCount";
            $font = $fontMetrics->get_font("Helvetica", "normal");
            $canvas->text(270, 820, $text, $font, 10);
        });

        $path = "orcamentos/orcamento_{$novoOrcamento->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());
        $novoOrcamento->update(['pdf_path' => $path]);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento duplicado com sucesso!');
    }




    /**
     * Display the specified resource.
     */
    public function show(Orcamento $orcamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orcamento $orcamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrcamentoRequest $request, Orcamento $orcamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($orcamento_id)
    {
        $orcamento = Orcamento::findOrFail($orcamento_id);
        $orcamento->delete();

        return redirect()->route('orcamentos.index')
            ->with('success', 'Orçamento excluído com sucesso!');
    }
}
