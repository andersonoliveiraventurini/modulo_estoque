<?php

namespace App\Http\Controllers;

use App\Services\OrcamentoPdfService;

use App\Http\Requests\StoreOrcamentoRequest;
use App\Http\Requests\UpdateOrcamentoRequest;
use App\Models\Cliente;
use App\Models\CondicoesPagamento;
use App\Models\ConsultaPreco;
use App\Models\Desconto;
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
use App\Models\PickingBatch;
use App\Models\PickingItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

use App\Models\SolicitacaoPagamento;

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

    public function balcao()
    {
        return view('paginas.orcamentos.index_balcao');
    }

    public function balcao_concluidos()
    {
        return view('paginas.orcamentos.index_balcao_concluidos');
    }

    public function orcamentos_concluidos()
    {
        return view('paginas.orcamentos.index_concluidos');
    }


    public function kanban_orcamentos()
    {
        return view('paginas.orcamentos.index_kanban_orcamentos');
    }

    public function copiarOrcamento()
    {
        $orcamentos = Orcamento::orderBy('id', 'desc')->get();
        $clientes = Cliente::orderBy('nome_fantasia')->get();
        return view('paginas.orcamentos.copiar_orcamento', compact('orcamentos', 'clientes'));
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
        $cliente = Cliente::with('enderecos')->findOrFail($cliente_id);

        // validação CNPJ ativo na Receita Federal        
        $ativo = Http::get("https://brasilapi.com.br/api/cnpj/v1/" . preg_replace('/\D/', '', $cliente->cnpj))
            ->json('descricao_situacao_cadastral') === 'ATIVA';

        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        $vendedores = User::whereHas('vendedor')->get();
        $opcoesTransporte = TipoTransporte::all();
        $condicao = CondicoesPagamento::all();
        return view('paginas.orcamentos.create', compact('produtos', 'cliente', 'fornecedores', 'cores', 'vendedores', 'opcoesTransporte', 'condicao', 'ativo'));
    }

    public function aprovarDesconto(Request $request, $id)
    {
        $orcamento = Orcamento::findOrFail($id);
        $acao = $request->input('acao');

        if ($acao === 'reprovar') {
            // Se for reprovado: muda status para cancelado e não gera PDF
            $orcamento->update([
                'status' => 'Cancelado',
                'desconto_aprovado' => 0,
            ]);

            // Se for requisição AJAX, retorna JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Desconto reprovado. O orçamento foi cancelado e o PDF não será gerado.'
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'Desconto reprovado. O orçamento foi cancelado e o PDF não será gerado.');
        }

        if ($acao === 'aprovar') {
            // Aprova o desconto e muda status para Pendente
            $orcamento->update([
                'status' => 'Pendente',
            ]);

            // Gera token de acesso e QR Code
            $token = Str::uuid();
            $tokenExpiraEm = Carbon::now()->addDays(2);
            $orcamento->update([
                'token_acesso' => $token,
                'token_expira_em' => $tokenExpiraEm,
            ]);

            $linkSeguro = route('orcamentos.view', ['token' => $token]);

            $qrCodeBase64 = base64_encode(
                QrCode::format('png')
                    ->size(130)
                    ->margin(1)
                    ->generate($linkSeguro)
            );

            try {
                // Gera PDF normalmente
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('documentos_pdf.orcamento', [
                    'orcamento' => $orcamento,
                    'percentualAplicado' => $orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0,
                    'qrCode' => $qrCodeBase64,
                ])->setPaper('a4');

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

                    // Se for requisição AJAX, retorna JSON
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Desconto aprovado! Orçamento atualizado e PDF gerado com sucesso.'
                        ]);
                    }

                    return redirect()
                        ->back()
                        ->with('success', 'Desconto aprovado! Orçamento atualizado e PDF gerado com sucesso.');
                } else {
                    // Se for requisição AJAX, retorna JSON
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Desconto aprovado, mas ocorreu um erro ao salvar o PDF.'
                        ], 500);
                    }

                    return redirect()
                        ->back()
                        ->with('error', 'Desconto aprovado, mas ocorreu um erro ao salvar o PDF.');
                }
            } catch (\Exception $e) {
                Log::error("Erro ao gerar PDF de aprovação: " . $e->getMessage());

                // Se for requisição AJAX, retorna JSON
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Desconto aprovado, mas ocorreu um erro ao gerar o PDF: ' . $e->getMessage()
                    ], 500);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Desconto aprovado, mas ocorreu um erro ao gerar o PDF.');
            }
        }

        // Se for requisição AJAX, retorna JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Ação inválida.'
            ], 400);
        }

        return redirect()->back()->with('error', 'Ação inválida.');
    }

   public function store(StoreOrcamentoRequest $request)
    {
        $itenscomdesconto = false;
        $necessitaAprovacaoPagamento = false;

        $brToDecimal = function ($valor) {
            if ($valor === null || $valor === '') {
                return null;
            }

            $negativo = false;
            $valor = trim($valor);
            if (strpos($valor, '-') !== false) {
                $negativo = true;
                $valor = str_replace('-', '', $valor);
            }

            $valor = str_replace(' ', '', $valor);

            if (strpos($valor, '.') !== false && strpos($valor, ',') !== false) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            } elseif (strpos($valor, ',') !== false) {
                $valor = str_replace(',', '.', $valor);
            } else {
                $valor = preg_replace('/[^\d\.]/', '', $valor);
            }

            if (substr_count($valor, '.') > 1) {
                $parts = explode('.', $valor);
                $decimal = array_pop($parts);
                $valor = implode('', $parts) . '.' . $decimal;
            }

            $float = (float) $valor;
            return $negativo ? -$float : $float;
        };

        $request->merge([
            'guia_recolhimento'   => $brToDecimal($request->guia_recolhimento),
            'desconto_especifico' => $brToDecimal($request->desconto_especifico),
            'desconto_aprovado'   => $brToDecimal($request->desconto_aprovado),
            'valor_total'         => $brToDecimal($request->valor_total),
        ]);

        $descontoPercentual = 0;
        if ($request->filled('desconto_aprovado') || $request->filled('desconto')) {
            $descontoPercentual = max(
                (float) ($request->desconto_aprovado ?? 0),
                (float) ($request->desconto ?? 0)
            );
        }

        $descontoEspecifico = $request->filled('desconto_especifico')
            ? (float) $request->desconto_especifico
            : null;

        if ($request->valor_total == "0,00" || $request->valor_total == 0) {
            $request->merge(['valor_total' => 0]);
        }

        $orcamento = Orcamento::create([
            'cliente_id'          => $request->cliente_id,
            'vendedor_id'         => Auth()->user()->id,
            'usuario_logado_id'   => Auth()->user()->id,
            'obra'                => $request->nome_obra,
            'valor_total_itens'   => $request->valor_total,
            'guia_recolhimento'   => $request->guia_recolhimento,
            'observacoes'         => $request->observacoes,
            'condicao_id'         => $request->condicao_id,
            'tipo_documento'      => $request->tipo_documento,
            'venda_triangular'    => $request->venda_triangular,
            'homologacao'         => $request->homologacao,
            'validade'            => Carbon::now()->addDays(2),
        ]);

        if ($request->venda_triangular == 1) {
            $orcamento->update([
                'cnpj_triangular' => $request->cnpj_triangular,
            ]);
        }

        // ✅ CRIA SOLICITAÇÃO DE PAGAMENTO SE CONDIÇÃO FOR "OUTROS" (ID 20)
        if ($request->condicao_id == 20) {
            $orcamento->update([
                'outros_meios_pagamento' => $request->outros_meios_pagamento,
            ]);

            // Cria a solicitação de pagamento para aprovação
            $orcamento->solicitacoesPagamento()->create([
                'descricao_pagamento'       => $request->outros_meios_pagamento,
                'justificativa_solicitacao' => $request->justificativa_pagamento ?? 'Solicitação de meio de pagamento especial conforme necessidade do cliente.',
                'numero_parcelas'           => $request->numero_parcelas ?? null,
                'valor_entrada'             => $request->valor_entrada ? $brToDecimal($request->valor_entrada) : null,
                'data_primeiro_vencimento'  => $request->data_primeiro_vencimento ?? null,
                'intervalo_dias'            => $request->intervalo_dias ?? null,
                'solicitado_por'            => Auth()->id(),
                'observacoes'               => $request->observacoes_pagamento ?? null,
                'status'                    => 'Pendente',
            ]);

            $necessitaAprovacaoPagamento = true;

            Log::info("Solicitação de pagamento criada para orçamento #{$orcamento->id}", [
                'orcamento_id' => $orcamento->id,
                'descricao'    => $request->outros_meios_pagamento,
                'vendedor_id'  => Auth()->id(),
            ]);
        }

        if ($request->tipos_transporte) {
            $orcamento->transportes()->sync($request->tipos_transporte);
        }

        // ✅ ITENS COM SUPORTE A DESCONTO POR PRODUTO
        if ($request->has('itens')) {
            foreach ($request->itens as $item) {
                $precoOriginal = (float) ($item['preco_original'] ?? $item['preco_unitario'] ?? 0);
                $precoUnitario = (float) ($item['preco_unitario'] ?? 0);
                $quantidade    = (float) ($item['quantidade'] ?? 0);

                $liberarDesconto = isset($item['liberar_desconto']) ? (int) $item['liberar_desconto'] : 1;
                $tipoDesconto    = $item['tipo_desconto'] ?? 'nenhum';
                $descontoProduto = (float) ($item['desconto_produto'] ?? 0);

                $subtotalOriginal = $precoOriginal * $quantidade;
                $subtotal         = $precoUnitario * $quantidade;

                $valorComDesconto         = $subtotal;
                $valorUnitarioComDesconto = $precoUnitario;
                $descontoAplicadoItem     = 0;

                // ✅ LÓGICA DE DESCONTO
                if ($liberarDesconto === 1) {
                    if ($tipoDesconto === 'produto' && $descontoProduto > 0) {
                        $valorComDesconto         = $subtotal;
                        $valorUnitarioComDesconto = $precoUnitario;

                        $orcamento->descontos()->create([
                            'motivo'      => "Desconto individual " . $quantidade . " em unidades do produto ID " . $item['id'],
                            'valor'       => $descontoProduto * $quantidade,
                            'porcentagem' => null,
                            'tipo'        => 'produto',
                            'produto_id'  => $item['id'],
                            'cliente_id'  => $request->cliente_id,
                            'user_id'     => Auth()->id(),
                        ]);

                        $itenscomdesconto = true;
                    } elseif ($tipoDesconto === 'percentual' && $descontoPercentual > 0) {
                        $valorComDesconto         = $subtotal - ($subtotal * ($descontoPercentual / 100));
                        $valorUnitarioComDesconto = $valorComDesconto / $quantidade;
                        $descontoAplicadoItem     = $descontoPercentual;
                    }
                }

                $orcamento->itens()->create([
                    'produto_id'                  => $item['id'],
                    'quantidade'                  => $quantidade,
                    'valor_unitario'              => $precoOriginal,
                    'valor_unitario_com_desconto' => $valorUnitarioComDesconto,
                    'desconto'                    => $descontoAplicadoItem,
                    'valor_com_desconto'          => $valorComDesconto,
                    'user_id'                     => $request->user()->id ?? null,
                ]);
            }
        }

        // ✅ VIDROS
        if ($request->has('vidros')) {
            foreach ($request->vidros as $vidro) {
                if (!empty($vidro['preco_m2']) && !empty($vidro['quantidade']) && !empty($vidro['altura']) && !empty($vidro['largura'])) {
                    $orcamento->vidros()->create([
                        'descricao'            => $vidro['descricao'] ?? null,
                        'quantidade'           => (float) $vidro['quantidade'] ?? 0,
                        'altura'               => (float) $vidro['altura'] ?? 0,
                        'largura'              => (float) $vidro['largura'] ?? 0,
                        'preco_metro_quadrado' => $brToDecimal($vidro['preco_m2']) ?? 0,
                        'desconto'             => $descontoPercentual ?? 0,
                        'valor_total'          => $brToDecimal($vidro['valor_total']) ?? 0,
                        'valor_com_desconto'   => $brToDecimal($vidro['valor_com_desconto']) ?? 0,
                        'user_id'              => $request->user()->id ?? null,
                    ]);
                }
            }
        }

        // ✅ DESCONTO PERCENTUAL
        if ($descontoPercentual > 0) {
            $totalDescontoPercentual = 0;

            if ($request->has('itens')) {
                foreach ($request->itens as $item) {
                    $liberarDesconto = isset($item['liberar_desconto']) ? (int) $item['liberar_desconto'] : 1;
                    $tipoDesconto    = $item['tipo_desconto'] ?? 'nenhum';

                    if ($liberarDesconto === 1 && $tipoDesconto === 'percentual') {
                        $precoUnitario = (float) ($item['preco_unitario'] ?? 0);
                        $quantidade    = (float) ($item['quantidade'] ?? 0);
                        $subtotal      = $precoUnitario * $quantidade;
                        $totalDescontoPercentual += $subtotal * ($descontoPercentual / 100);
                    }
                }
            }

            if ($request->has('vidros')) {
                foreach ($request->vidros as $vidro) {
                    if (!empty($vidro['valor_total'])) {
                        $valorTotal = $brToDecimal($vidro['valor_total']) ?? 0;
                        $totalDescontoPercentual += $valorTotal * ($descontoPercentual / 100);
                    }
                }
            }

            if ($totalDescontoPercentual > 0) {
                $orcamento->descontos()->create([
                    'motivo'      => 'Desconto percentual aplicado pelo vendedor',
                    'valor'       => $totalDescontoPercentual,
                    'porcentagem' => $descontoPercentual,
                    'tipo'        => 'percentual',
                    'cliente_id'  => $request->cliente_id,
                    'user_id'     => Auth()->id(),
                ]);
            }
        }

        // ✅ DESCONTO ESPECÍFICO
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

        if ($request->filled('endereco_cep')) {
            $endereco = Endereco::updateOrCreate(
                [
                    'tipo'       => 'entrega',
                    'cliente_id' => $request->cliente_id,
                ],
                array_filter([
                    'cep'         => $request->endereco_cep,
                    'logradouro'  => $request->endereco_logradouro,
                    'numero'      => $request->endereco_numero,
                    'complemento' => $request->endereco_compl,
                    'bairro'      => $request->endereco_bairro,
                    'cidade'      => $request->endereco_cidade,
                    'estado'      => $request->endereco_estado,
                    'tipo'        => 'entrega',
                ])
            );
        } elseif ($request->enderecos_cadastrados != "") {
            $orcamento->update(['endereco_id' => $request->enderecos_cadastrados]);
        }

        // ✅ VALIDAÇÃO FINAL: VERIFICA SE PRECISA APROVAÇÃO
        $temDescontoPercentual = $descontoPercentual > 0;
        $temDescontoEspecifico = $descontoEspecifico > 0;
        $temQualquerDesconto   = $temDescontoPercentual || $temDescontoEspecifico || $itenscomdesconto;

        $necessitaAprovacaoDesconto = $temQualquerDesconto && (
            $descontoPercentual > (float) ($request->desconto_aprovado ?? 0) ||
            (Auth()->user()->vendedor->desconto ?? 0) < $descontoPercentual ||
            $itenscomdesconto
        );

        // ✅ DETERMINA O STATUS FINAL DO ORÇAMENTO
        if (!$temQualquerDesconto && !$necessitaAprovacaoPagamento) {
            // Nenhuma aprovação necessária — aprova direto
            $orcamento->status = 'Aprovado';
            $orcamento->save();
        } elseif ($necessitaAprovacaoDesconto && $necessitaAprovacaoPagamento) {
            // Ambos precisam de aprovação — prioriza desconto
            $orcamento->status = 'Aprovar desconto';
            $orcamento->save();

            Log::info("Orçamento #{$orcamento->id} aguardando aprovação de desconto E pagamento", [
                'orcamento_id' => $orcamento->id,
                'status'       => $orcamento->status,
            ]);

            return redirect()
                ->route('orcamentos.index')
                ->with('warning', 'Orçamento criado, mas é necessária a aprovação do desconto e do meio de pagamento antes de gerar o PDF.');
        } elseif ($necessitaAprovacaoDesconto) {
            // Só desconto precisa de aprovação
            $orcamento->status = 'Aprovar desconto';
            $orcamento->save();

            return redirect()
                ->route('orcamentos.index')
                ->with('error', 'Orçamento criado, mas é necessária a aprovação do desconto.');
        } elseif ($necessitaAprovacaoPagamento) {
            // Só pagamento precisa de aprovação
            $orcamento->status = 'Aprovar pagamento';
            $orcamento->save();

            Log::info("Orçamento #{$orcamento->id} aguardando aprovação de pagamento", [
                'orcamento_id' => $orcamento->id,
                'status'       => $orcamento->status,
            ]);

            return redirect()
                ->route('orcamentos.index')
                ->with('info', 'Orçamento criado com sucesso! Aguardando aprovação do meio de pagamento especial para gerar o PDF.');
        }

        // ✅ NÃO PRECISA DE APROVAÇÃO — GERA O PDF NORMALMENTE
        $pdfService = new OrcamentoPdfService();
        $pdfGeradoComSucesso = $pdfService->gerarOrcamentoPdf($orcamento);

        if ($pdfGeradoComSucesso) {
            return redirect()
                ->route('orcamentos.show', $orcamento->id)
                ->with('success', 'Orçamento criado e PDF gerado com sucesso!');
        }

        return redirect()
            ->route('orcamentos.show', $orcamento->id)
            ->with('error', 'Orçamento criado com sucesso, mas ocorreu uma falha ao gerar o PDF. Por favor, contate o suporte.');
    }



    /*
     * Store a newly created resource in storage.
     * 
         public function store(StoreOrcamentoRequest $request)
    {
        // 1) Definir desconto percentual (cliente x vendedor)
        $descontoPercentual = null;

        if ($request->guia_recolhimento != null) {
            $request->merge([
                'guia_recolhimento' => str_replace(',', '.', str_replace('.', '', $request->guia_recolhimento)),
            ]);
        }

        $request->merge([
            'desconto_especifico' => str_replace(',', '.', str_replace('.', '', $request->desconto_especifico)),
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
            'vendedor_id'  =>  Auth()->user()->id,
            'usuario_logado_id'  => Auth()->user()->id,
            'obra'         => $request->nome_obra,
            'valor_total_itens'  => $request->valor_total,
            'guia_recolhimento'  => $request->guia_recolhimento,
            'observacoes'  => $request->observacoes,
            'condicao_id' => $request->condicao_id,
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

            // vincula o endereço ao orçamento;
        } elseif ($request->enderecos_cadastrados != "") {
            // caso tenha selecioando um endereço existente
            $orcamento->update(['endereco_id' => $request->enderecos_cadastrados]);
        }

        // se o desconto for menor que o autorizado para o cliente e o para o vendedor não precisa ser aprovado
        if ($descontoPercentual > $request->desconto_aprovado && Auth()->user()->vendedor->desconto < $descontoPercentual) {
            $orcamento->status = 'aprovar desconto';
            $orcamento->save();

            return redirect()
                ->route('orcamentos.index')
                ->with('error', 'Orçamento criado, mas é necessária a aprovação do desconto.');
        } else {
            // Chama a nova função para gerar o PDF e verifica o resultado
            $pdfGeradoComSucesso = $this->gerarOrcamentoPdf($orcamento);

            if ($pdfGeradoComSucesso) {
                // SUCESSO: Redireciona para a página de visualização do orçamento com uma mensagem de sucesso.
                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('success', 'Orçamento criado e PDF gerado com sucesso!');
            } else {
                // FALHA NO PDF: O orçamento foi criado, mas o PDF não.
                // Redireciona para a mesma página, mas com uma mensagem de erro clara.
                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('error', 'Orçamento criado com sucesso, mas ocorreu uma falha ao gerar o PDF. Por favor, contate o suporte.');
            }
        }
    }*/

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

    /*
    private function gerarOrcamentoPdf(Orcamento $orcamento): bool
    {
        try {
            // 1. GERAÇÃO DE TOKEN E LINK SEGURO
            $token = Str::uuid();
            $tokenExpiraEm = Carbon::now()->addDays(2);
            $linkSeguro = route('orcamentos.view', ['token' => $token]);

            // 2. GERAÇÃO DO QR CODE
            $qrCodeBase64 = base64_encode(
                QrCode::format('png')
                    ->size(130)
                    ->margin(1)
                    ->generate($linkSeguro)
            );

            // 3. GERAÇÃO DO PDF (passando a versão)
            $pdf = Pdf::loadView('documentos_pdf.orcamento', [
                'orcamento' => $orcamento,
                'qrCode' => $qrCodeBase64,
                'linkSeguro' => $linkSeguro,
                'versao' => $orcamento->versao ?? 1, // IMPORTANTE: Passar versão para o PDF
            ])->setPaper('a4');

            // 4. NUMERAÇÃO DE PÁGINAS
            $canvas = $pdf->getDomPDF()->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $text = "Página $pageNumber / $pageCount";
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $canvas->text(270, 820, $text, $font, 10);
            });

            // 5. SALVAMENTO DO ARQUIVO
            $path = "orcamentos/orcamento_{$orcamento->id}.pdf";
            Storage::disk('public')->put($path, $pdf->output());

            // 6. VERIFICAÇÃO E ATUALIZAÇÃO FINAL
            if (Storage::disk('public')->exists($path)) {
                $orcamento->update([
                    'token_acesso' => $token,
                    'token_expira_em' => $tokenExpiraEm,
                    'pdf_path' => $path,
                ]);
                return true;
            } else {
                Log::error("Falha ao salvar o PDF no caminho: " . $path);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Erro fatal ao gerar PDF para o orçamento #{$orcamento->id}: " . $e->getMessage());
            return false;
        }
    }*/

    public function processarAprovacaoDesconto(Request $request, $id)
    {
        // 1. Validação (sem alterações)
        $request->validate([
            'acao' => 'required|in:aprovar,reprovar',
        ]);

        // 2. Encontra o orçamento (sem alterações)
        $orcamento = Orcamento::findOrFail($id);

        // 3. Executa a lógica com base na ação escolhida
        if ($request->input('acao') === 'aprovar') {
            // --- CENÁRIO DE APROVAÇÃO ---

            $orcamento->update([
                'status' => 'Pendente',
                'workflow_status' => 'aguardando_separacao'
            ]);

            // Chama a função para gerar o PDF e verifica o resultado
            $pdfService = new OrcamentoPdfService();
            $pdfGeradoComSucesso = $pdfService->gerarOrcamentoPdf($orcamento);

            if ($pdfGeradoComSucesso) {
                // SUCESSO: Redireciona para a página de visualização do orçamento com uma mensagem de sucesso.
                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('success', 'Desconto aprovado e PDF gerado com sucesso!');
            } else {
                // FALHA NO PDF: O orçamento foi aprovado, mas o PDF falhou.
                // Redireciona para a mesma página, mas com uma mensagem de erro.
                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('error', 'Orçamento aprovado, mas ocorreu uma falha ao gerar o PDF. Por favor, contate o suporte.');
            }
        } else {
            // --- CENÁRIO DE REPROVAÇÃO ---

            // Duplica o orçamento antes de deletar o original
            $novoOrcamento = $this->duplicar($orcamento); // Passando o objeto diretamente

            // Deleta o orçamento original (Soft Delete, se configurado)
            $orcamento->delete();

            // SUCESSO: Redireciona para a página de edição do NOVO orçamento com uma mensagem de sucesso.
            return redirect()
                ->route('orcamentos.edit', $novoOrcamento->id)
                ->with('success', 'Desconto reprovado. O orçamento original foi arquivado e um novo foi criado para edição.');
        }
    }

    public function duplicar(int $id, ?int $clienteID = null)
    {
        if ($clienteID && !Cliente::whereKey($clienteID)->exists()) {
            abort(404, 'Cliente não encontrado');
        }

        $orcamentoOriginal = Orcamento::with(['itens', 'vidros', 'descontos', 'endereco', 'condicaoPagamento'])->findOrFail($id);

        // Novo nome da obra com data e hora
        $dataHora = Carbon::now()->format('d/m/Y H:i');
        $novaObra = "{$dataHora} - {$orcamentoOriginal->obra}";

        // Criar novo orçamento
        $novoOrcamento = Orcamento::create([
            'cliente_id'          => $clienteID ?? $orcamentoOriginal->cliente_id,
            'vendedor_id'         => Auth()->user()->id,
            'obra'                => $novaObra,
            'valor_total_itens'   => $orcamentoOriginal->valor_total_itens,
            'status'              => 'Pendente',
            'observacoes'         => $orcamentoOriginal->observacoes,
            'frete'               => $orcamentoOriginal->frete,
            'guia_recolhimento'   => $orcamentoOriginal->guia_recolhimento,
            'tipo_documento'      => $orcamentoOriginal->tipo_documento,
            'condicao_id'         => $orcamentoOriginal->condicao_id,
            'outros_meios_pagamento' => $orcamentoOriginal->outros_meios_pagamento,
            'validade'            => Carbon::now()->addDays(2),
        ]);

        // ✅ PRIORIDADE 1: VERIFICAR SE A CONDIÇÃO É "OUTROS" (ID 20)
        $necessitaAprovacaoPagamento = false;
        if ($orcamentoOriginal->condicao_id == 20 && $orcamentoOriginal->outros_meios_pagamento) {
            $novoOrcamento->solicitacoesPagamento()->create([
                'descricao_pagamento' => $orcamentoOriginal->outros_meios_pagamento,
                'justificativa_solicitacao' => 'Duplicação de orçamento #' . $orcamentoOriginal->id . ' que possuía meio de pagamento especial.',
                'solicitado_por' => Auth()->id(),
                'status' => 'Pendente',
            ]);

            $necessitaAprovacaoPagamento = true;

            Log::info("Solicitação de pagamento criada na duplicação do orçamento #{$orcamentoOriginal->id}", [
                'novo_orcamento_id' => $novoOrcamento->id,
                'descricao' => $orcamentoOriginal->outros_meios_pagamento,
                'vendedor_id' => Auth()->id(),
            ]);
        }

        // 1) Copiar itens
        $itensComDesconto = false;
        foreach ($orcamentoOriginal->itens as $item) {
            $produtoAtual = Produto::find($item->produto_id);
            $precoAtual   = $produtoAtual->preco_venda ?? $item->valor_unitario;

            // Verifica se o item original tinha desconto por produto registrado
            $temDescontoProduto = $orcamentoOriginal->descontos
                ->where('tipo', 'produto')
                ->where('produto_id', $item->produto_id)
                ->isNotEmpty();

            if ($temDescontoProduto) {
                // Mantém os valores originais do item COM desconto, pois o desconto
                // será re-criado na tabela descontos e aguardará nova aprovação.
                // valor_unitario       = preço original (sem desconto)
                // valor_com_desconto   = preço já com desconto aplicado
                // valor_unitario_com_desconto = preço unitário com desconto
                $valorUnitarioOriginal      = (float) $item->valor_unitario;
                $valorUnitarioComDesconto   = (float) $item->valor_unitario_com_desconto;
                $valorComDesconto           = (float) $item->valor_com_desconto;
                $descontoItem               = (float) $item->desconto;
                $itensComDesconto           = true;
            } else {
                // Item sem desconto individual: recalcula com preço atual
                $valorUnitarioOriginal      = $precoAtual;
                $valorUnitarioComDesconto   = $precoAtual;
                $valorComDesconto           = $precoAtual * $item->quantidade;
                $descontoItem               = 0;
            }

            $novoOrcamento->itens()->create([
                'produto_id'                  => $item->produto_id,
                'quantidade'                  => $item->quantidade,
                'valor_unitario'              => $valorUnitarioOriginal,
                'valor_unitario_com_desconto' => $valorUnitarioComDesconto,
                'desconto'                    => $descontoItem,
                'valor_com_desconto'          => $valorComDesconto,
                'user_id'                     => $item->user_id,
            ]);
        }

        // 2) Copiar vidros
        foreach ($orcamentoOriginal->vidros as $vidro) {
            $valor_final = $vidro->preco_metro_quadrado * $vidro->quantidade * ($vidro->altura / 100) * ($vidro->largura / 100);

            $novoOrcamento->vidros()->create([
                'descricao'            => $vidro->descricao,
                'quantidade'           => $vidro->quantidade,
                'altura'               => $vidro->altura,
                'largura'              => $vidro->largura,
                'preco_metro_quadrado' => $vidro->preco_metro_quadrado,
                'valor_total'          => $valor_final,
                'user_id'              => $vidro->user_id,
            ]);
        }

        // 3) Copiar descontos (SE HOUVER)
        $necessitaAprovacaoDesconto = false;
        foreach ($orcamentoOriginal->descontos as $descontoOriginal) {

            // Força leitura direta do atributo para evitar problema com fillable/cast
            $produtoIdOriginal = $descontoOriginal->getAttributes()['produto_id'] ?? $descontoOriginal->produto_id ?? null;

            $novoOrcamento->descontos()->create([
                'motivo'      => $descontoOriginal->motivo . ' (Duplicado)',
                'valor'       => $descontoOriginal->valor,
                'porcentagem' => $descontoOriginal->porcentagem,
                'tipo'        => $descontoOriginal->tipo,
                'produto_id'  => $produtoIdOriginal,
                'cliente_id'  => $clienteID ?? $descontoOriginal->cliente_id,
                'user_id'     => Auth()->id(),
                'aprovado_em'  => null,
                'aprovado_por' => null,
                'rejeitado_em' => null,
                'rejeitado_por' => null,
            ]);

            $necessitaAprovacaoDesconto = true;
        }


        // Se tinha itens com desconto mas não tinha registro na tabela descontos
        if ($itensComDesconto && !$necessitaAprovacaoDesconto) {
            $necessitaAprovacaoDesconto = true;
        }

        // 4) Copiar endereço de entrega
        if ($orcamentoOriginal->endereco) {
            $novoEndereco = Endereco::create([
                'tipo'        => $orcamentoOriginal->endereco->tipo,
                'cliente_id'  => $clienteID ?? $orcamentoOriginal->endereco->cliente_id,
                'cep'         => $orcamentoOriginal->endereco->cep,
                'logradouro'  => $orcamentoOriginal->endereco->logradouro,
                'numero'      => $orcamentoOriginal->endereco->numero,
                'complemento' => $orcamentoOriginal->endereco->complemento,
                'bairro'      => $orcamentoOriginal->endereco->bairro,
                'cidade'      => $orcamentoOriginal->endereco->cidade,
                'estado'      => $orcamentoOriginal->endereco->estado,
            ]);
            $novoOrcamento->update(['endereco_id' => $novoEndereco->id]);
        }

        // ✅ VALIDAÇÃO FINAL: PRIORIDADE PARA MEIO DE PAGAMENTO
        if ($necessitaAprovacaoPagamento && $necessitaAprovacaoDesconto) {
            // ✅ PRIORIDADE: Primeiro aprova PAGAMENTO, depois DESCONTO
            $novoOrcamento->update(['status' => 'Aprovar pagamento']);

            Log::info("Orçamento duplicado #{$novoOrcamento->id} aguardando aprovação de PAGAMENTO (depois desconto)", [
                'original_id' => $orcamentoOriginal->id,
                'status' => 'Aprovar pagamento',
                'tem_desconto_pendente' => true,
            ]);

            return redirect()
                ->route('orcamentos.index')
                ->with('warning', 'Orçamento duplicado com sucesso! É necessária a aprovação do meio de pagamento e depois do desconto antes de gerar o PDF.');
        } elseif ($necessitaAprovacaoPagamento) {
            // Só pagamento precisa de aprovação
            $novoOrcamento->update(['status' => 'Aprovar pagamento']);

            Log::info("Orçamento duplicado #{$novoOrcamento->id} aguardando aprovação de pagamento", [
                'original_id' => $orcamentoOriginal->id,
                'status' => 'Aprovar pagamento',
            ]);

            return redirect()
                ->route('orcamentos.index')
                ->with('info', 'Orçamento duplicado com sucesso! Aguardando aprovação do meio de pagamento especial para gerar o PDF.');
        } elseif ($necessitaAprovacaoDesconto) {
            // Só desconto precisa de aprovação
            $novoOrcamento->update(['status' => 'Aprovar desconto']);

            Log::info("Orçamento duplicado #{$novoOrcamento->id} aguardando aprovação de desconto", [
                'original_id' => $orcamentoOriginal->id,
                'status' => 'Aprovar desconto',
            ]);

            return redirect()
                ->route('orcamentos.index')
                ->with('error', 'Orçamento duplicado com sucesso! É necessária a aprovação do desconto antes de gerar o PDF.');
        }

        // ✅ NÃO PRECISA DE APROVAÇÃO - GERA O PDF NORMALMENTE
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

        Log::info("Orçamento duplicado #{$novoOrcamento->id} com PDF gerado", [
            'original_id' => $orcamentoOriginal->id,
            'status' => 'Pendente',
        ]);

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento duplicado e PDF gerado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show($orcamento_id)
    {
        $orcamento = Orcamento::with(['cliente', 'vendedor', 'endereco', 'itens.produto.fornecedor', 'vidros', 'descontos', 'transportes'])->findOrFail($orcamento_id);
        return view('paginas.orcamentos.show', compact('orcamento'));
    }

    public function atualizarStatus(Request $request, $id)
    {
        $orcamento = Orcamento::with(['itens'])->findOrFail($id);

        $status = $request->input('status');
        $validStatus = ['Aprovar desconto', 'Pendente', 'Aprovado', 'Cancelado', 'Rejeitado', 'Expirado'];

        if (!in_array($status, $validStatus)) {
            return response()->json(['message' => 'Status inválido!'], 422);
        }

        // Atualiza status comercial
        $orcamento->status = $status;
        if ($status == 'Aprovado') {
            $orcamento->workflow_status = 'aguardando_separacao';
        }
        $orcamento->usuario_logado_id = auth()->id();
        $orcamento->save();

        // Se não foi aprovado, apenas responde
        if ($status !== 'Aprovado' && $status !== 'Pendente') {
            // 2. Chame o novo método
            $loteCancelado = $orcamento->cancelarLoteDeSeparacaoAtivo();

            if ($loteCancelado) {
                return response()->json([
                    'message' => 'Status atualizado com sucesso!',
                    'redirect' => route('orcamentos.index')
                ]);
            } else {
                return response()->json(['message' => 'Nenhum lote de separação ativo foi encontrado para este orçamento.']);
            }
        }

        // Se aprovado, aciona separação conforme política
        if (!$orcamento->requer_separacao) {
            // Só marca que está aguardando separação (sem criar lote ainda)
            // Criar lote de separação + itens + reservas imediatamente
            // Usa transação para consistência
            \DB::transaction(function () use ($orcamento) {
                // Evita duplicar lote se já existir aberto/em_separacao
                $existe = \App\Models\PickingBatch::where('orcamento_id', $orcamento->id)
                    ->whereIn('status', ['aberto', 'em_separacao'])
                    ->exists();

                if (!$existe) {
                    $batch = \App\Models\PickingBatch::create([
                        'orcamento_id' => $orcamento->id,
                        'status' => 'em_separacao',
                        'started_at' => now(),
                        'criado_por_id' => auth()->id(),
                    ]);

                    foreach ($orcamento->itens as $oi) {
                        \App\Models\PickingItem::create([
                            'picking_batch_id' => $batch->id,
                            'orcamento_item_id' => $oi->id,
                            'produto_id' => $oi->produto_id,
                            'qty_solicitada' => $oi->quantidade,
                            'qty_separada' => 0,
                            'status' => 'pendente',
                        ]);
                    }

                    // Reservas de estoque
                    app(\App\Services\EstoqueService::class)->reservarParaOrcamento($orcamento);

                    // Seta workflow em separação
                    $orcamento->update(['workflow_status' => 'em_separacao']);
                }
            });
            return response()->json([
                'message' => 'Orçamento aprovado e Separação iniciada!',
                'redirect' => route('orcamentos.separacao.show', $orcamento->id)
            ]);
        }
    }

    public function atualizarPrecos(Orcamento $orcamento)
{
    try {
        DB::transaction(function () use ($orcamento) {

            // 1. Deletar descontos relacionados ao orçamento
            Desconto::where('orcamento_id', $orcamento->id)->delete();

            // =============================================
            // 2. DESTRUIR WORKFLOW DE SEPARAÇÃO/CONFERÊNCIA
            // =============================================

            // Busca todos os picking_batches do orçamento
            $pickingBatches = \App\Models\PickingBatch::where('orcamento_id', $orcamento->id)->get();

            foreach ($pickingBatches as $batch) {

                // Busca conferências vinculadas ao batch
                $conferencias = \App\Models\Conferencia::where('picking_batch_id', $batch->id)->get();

                foreach ($conferencias as $conferencia) {
                    // Deleta itens de conferência (cascade já faz isso, mas explícito por segurança)
                    \App\Models\ConferenciaItem::where('conferencia_id', $conferencia->id)->delete();
                    $conferencia->delete();
                }

                // Deleta picking_items do batch
                \App\Models\PickingItem::where('picking_batch_id', $batch->id)->delete();

                // Deleta o batch
                $batch->delete();
            }

            // Deleta reservas de estoque vinculadas ao orçamento
            // \App\Models\EstoqueReserva::where('orcamento_id', $orcamento->id)->delete();

            // 3. Atualizar cada item do orçamento com o preço atual do produto
            foreach ($orcamento->itens as $item) {
                $produto = Produto::find($item->produto_id);

                if (!$produto || is_null($produto->preco_venda)) {
                    throw new \Exception("Produto ID {$item->produto_id} não encontrado ou sem preço de venda definido.");
                }

                $precoAtual       = $produto->preco_venda;
                $valorComDesconto = $item->quantidade * $precoAtual;

                $item->update([
                    'valor_unitario'              => $precoAtual,
                    'valor_unitario_com_desconto' => $precoAtual,
                    'desconto'                    => 0,
                    'valor_com_desconto'          => $valorComDesconto,
                ]);
            }

            // 4. Recalcular totais
            $totalItens = $orcamento->itens()->sum(DB::raw('quantidade * valor_unitario'));

            // 5. Atualizar orçamento — incrementa versão e limpa workflow_status
            $orcamento->update([
                'status'          => 'Pendente',
                'workflow_status' => null,  // ✅ limpa o workflow
                'valor_total_itens'  => $totalItens,
                'desconto_total'     => 0,
                'valor_com_desconto' => $totalItens,
                'versao'             => $orcamento->versao + 1,
                'updated_at'         => now(),   
                // atualiza a validade desse orçamento para 2 dias a partir da data de atualização             
                'validade'            => Carbon::now()->addDays(2),
            ]);

            // 6. Remove PDF antigo
            if ($orcamento->pdf_path && Storage::disk('public')->exists($orcamento->pdf_path)) {
                Storage::disk('public')->delete($orcamento->pdf_path);
            }

            // 7. Recarrega orçamento fresco e gera novo PDF
            $orcamentoAtualizado = Orcamento::with(['itens.produto', 'cliente', 'vendedor'])
                ->find($orcamento->id);

            $pdfService = new OrcamentoPdfService();
            $pdfService->gerarOrcamentoPdf($orcamentoAtualizado);
        });

        return redirect()->route('orcamentos.show', $orcamento->id)
            ->with('success', 'Preços atualizados com sucesso! Workflow de separação destruído, nova versão gerada e PDF atualizado.');

    } catch (\Exception $e) {
        return redirect()->route('orcamentos.show', $orcamento->id)
            ->with('error', 'Erro ao atualizar preços: ' . $e->getMessage());
    }
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $orcamento = Orcamento::with([
            'cliente.enderecos',
            'itens.produto.fornecedor',
            'itens.produto.cor',
            'vidros',
            'consultaPrecos',
            'descontos'
        ])->findOrFail($id);

        $cliente = Cliente::with('enderecos')->find($orcamento->cliente_id);

        // Validação CNPJ ativo na Receita Federal
        $ativo = true;
        if ($cliente->cnpj) {
            try {
                $ativo = Http::get("https://brasilapi.com.br/api/cnpj/v1/" . preg_replace('/\D/', '', $cliente->cnpj))
                    ->json('descricao_situacao_cadastral') === 'ATIVA';
            } catch (\Exception $e) {
                Log::warning("Erro ao validar CNPJ: " . $e->getMessage());
            }
        }

        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        $vendedores = User::whereHas('vendedor')->get();
        $opcoesTransporte = TipoTransporte::all();
        $condicao = CondicoesPagamento::all();

        $desconto_percentual = $orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0;
        $desconto_especifico = $orcamento->descontos->where('tipo', 'fixo')->max('valor') ?? 0;

        // Prepara os itens de consulta para JavaScript
        $itensParaJs = old('itens') ?? $orcamento->consultaPrecos->map(function ($item) {
            return [
                'id' => $item->id,
                'nome' => $item->descricao,
                'quantidade' => $item->quantidade,
                'cor' => $item->cor,
                'fornecedor_id' => $item->fornecedor_id,
                'observacoes' => $item->observacao
            ];
        })->toArray();

        return view('paginas.orcamentos.edit', compact(
            'orcamento',
            'vendedores',
            'opcoesTransporte',
            'cores',
            'fornecedores',
            'produtos',
            'cliente',
            'desconto_percentual',
            'desconto_especifico',
            'condicao',
            'itensParaJs',
            'ativo'
        ));
    }


    // ============================================================
    // Método update — OrcamentoController
    // Substitua o método update existente pelo conteúdo abaixo.
    // ============================================================

    public function update(UpdateOrcamentoRequest $request, Orcamento $orcamento)
    {
        DB::beginTransaction();

        try {
            $itenscomdesconto            = false;
            $necessitaAprovacaoPagamento = false;

            // ----------------------------------------------------------------
            // Helper: converte valor no formato brasileiro para float
            // ----------------------------------------------------------------
            $brToDecimal = function ($valor) {
                if ($valor === null || $valor === '') {
                    return null;
                }

                $negativo = false;
                $valor    = trim((string) $valor);

                if (str_contains($valor, '-')) {
                    $negativo = true;
                    $valor    = str_replace('-', '', $valor);
                }

                $valor = str_replace(' ', '', $valor);

                if (str_contains($valor, '.') && str_contains($valor, ',')) {
                    $valor = str_replace('.', '', $valor);
                    $valor = str_replace(',', '.', $valor);
                } elseif (str_contains($valor, ',')) {
                    $valor = str_replace(',', '.', $valor);
                } else {
                    $valor = preg_replace('/[^\d\.]/', '', $valor);
                }

                if (substr_count($valor, '.') > 1) {
                    $parts   = explode('.', $valor);
                    $decimal = array_pop($parts);
                    $valor   = implode('', $parts) . '.' . $decimal;
                }

                $float = (float) $valor;
                return $negativo ? -$float : $float;
            };

            // ----------------------------------------------------------------
            // Normalizar valores monetários vindos do request
            // ----------------------------------------------------------------
            $request->merge([
                'guia_recolhimento'   => $brToDecimal($request->guia_recolhimento),
                'desconto_especifico' => $brToDecimal($request->desconto_especifico),
                'desconto_aprovado'   => $brToDecimal($request->desconto_aprovado),
                'valor_total'         => $brToDecimal($request->valor_total),
            ]);

            // ----------------------------------------------------------------
            // Calcular desconto percentual efetivo
            // ----------------------------------------------------------------
            $descontoPercentual = 0;
            if ($request->filled('desconto_aprovado') || $request->filled('desconto')) {
                $descontoPercentual = max(
                    (float) ($request->desconto_aprovado ?? 0),
                    (float) ($request->desconto ?? 0)
                );
            }

            $descontoEspecifico = $request->filled('desconto_especifico')
                ? (float) $request->desconto_especifico
                : null;

            $guiaRecolhimento = $request->filled('guia_recolhimento')
                ? (float) $request->guia_recolhimento
                : 0;

            // ----------------------------------------------------------------
            // Incrementar versão
            // ----------------------------------------------------------------
            $versaoAtual = $orcamento->versao ?? 0;
            $novaVersao  = $versaoAtual + 1;

            // ----------------------------------------------------------------
            // Atualizar dados básicos do orçamento
            // ----------------------------------------------------------------
            $orcamento->update([
                'obra'                => $request->obra,
                'prazo_entrega'       => $request->prazo_entrega,
                'vendedor_id'         => $request->vendedor_id ?? $orcamento->vendedor_id,
                'frete'               => $request->frete ?? 0,
                'valor_total_itens'   => $request->valor_total ?? $orcamento->valor_total_itens,
                'guia_recolhimento'   => $guiaRecolhimento,
                'observacoes'         => $request->observacoes,
                'versao'              => $novaVersao,
                'condicao_id'         => $request->condicao_id,
                'tipo_documento'      => $request->tipo_documento,
                'venda_triangular'    => $request->venda_triangular,
                'homologacao'         => $request->homologacao,
                'validade'            => Carbon::now()->addDays(1),
            ]);

            if ($request->venda_triangular == 1) {
                $orcamento->update(['cnpj_triangular' => $request->cnpj_triangular]);
            } else {
                $orcamento->update(['cnpj_triangular' => null]);
            }

            // ----------------------------------------------------------------
            // Condição de pagamento especial (ID 20)
            // ----------------------------------------------------------------
            if ($request->condicao_id == 20) {
                $orcamento->update([
                    'outros_meios_pagamento' => $request->outros_meios_pagamento,
                ]);

                $solicitacaoExistente = $orcamento->solicitacoesPagamento()
                    ->where('status', 'Pendente')
                    ->first();

                if (!$solicitacaoExistente) {
                    $orcamento->solicitacoesPagamento()->create([
                        'descricao_pagamento'        => $request->outros_meios_pagamento,
                        'justificativa_solicitacao'  => $request->justificativa_pagamento
                            ?? 'Solicitação de meio de pagamento especial conforme necessidade do cliente.',
                        'numero_parcelas'            => $request->numero_parcelas ?? null,
                        'valor_entrada'              => $request->valor_entrada
                            ? $brToDecimal($request->valor_entrada)
                            : null,
                        'data_primeiro_vencimento'   => $request->data_primeiro_vencimento ?? null,
                        'intervalo_dias'             => $request->intervalo_dias ?? null,
                        'solicitado_por'             => auth()->id(),
                        'observacoes'                => $request->observacoes_pagamento ?? null,
                        'status'                     => 'Pendente',
                    ]);

                    $necessitaAprovacaoPagamento = true;

                    Log::info("Solicitação de pagamento criada para orçamento #{$orcamento->id} na edição", [
                        'orcamento_id' => $orcamento->id,
                        'descricao'    => $request->outros_meios_pagamento,
                        'vendedor_id'  => auth()->id(),
                    ]);
                } else {
                    $solicitacaoExistente->update([
                        'descricao_pagamento'       => $request->outros_meios_pagamento,
                        'justificativa_solicitacao' => $request->justificativa_pagamento
                            ?? $solicitacaoExistente->justificativa_solicitacao,
                        'numero_parcelas'           => $request->numero_parcelas
                            ?? $solicitacaoExistente->numero_parcelas,
                        'valor_entrada'             => $request->valor_entrada
                            ? $brToDecimal($request->valor_entrada)
                            : $solicitacaoExistente->valor_entrada,
                        'data_primeiro_vencimento'  => $request->data_primeiro_vencimento
                            ?? $solicitacaoExistente->data_primeiro_vencimento,
                        'intervalo_dias'            => $request->intervalo_dias
                            ?? $solicitacaoExistente->intervalo_dias,
                        'observacoes'               => $request->observacoes_pagamento
                            ?? $solicitacaoExistente->observacoes,
                    ]);

                    $necessitaAprovacaoPagamento = ($solicitacaoExistente->status === 'Pendente');
                }
            }

            // ----------------------------------------------------------------
            // Endereço de entrega
            // ----------------------------------------------------------------
            if ($request->filled('entrega_cep')) {
                $dadosEndereco = array_filter([
                    'cep'         => $request->entrega_cep,
                    'cidade'      => $request->entrega_cidade,
                    'estado'      => $request->entrega_estado,
                    'bairro'      => $request->entrega_bairro,
                    'logradouro'  => $request->entrega_logradouro,
                    'numero'      => $request->entrega_numero,
                    'complemento' => $request->entrega_compl,
                    'tipo'        => 'entrega',
                ]);

                if ($orcamento->endereco) {
                    $orcamento->endereco->update($dadosEndereco);
                } else {
                    $endereco = Endereco::create(array_merge($dadosEndereco, [
                        'cliente_id' => $orcamento->cliente_id,
                    ]));
                    $orcamento->update(['endereco_id' => $endereco->id]);
                }
            } elseif ($request->filled('enderecos_cadastrados') && $request->enderecos_cadastrados != '') {
                $orcamento->update(['endereco_id' => $request->enderecos_cadastrados]);
            }

            // ----------------------------------------------------------------
            // Transportes
            // ----------------------------------------------------------------
            if ($request->tipos_transporte) {
                $orcamento->transportes()->sync($request->tipos_transporte);
            }

            // ----------------------------------------------------------------
            // Limpar descontos anteriores antes de recalcular
            // ----------------------------------------------------------------
            $orcamento->descontos()->delete();

            // ----------------------------------------------------------------
            // Produtos EXISTENTES (já estavam no orçamento)
            // ----------------------------------------------------------------
            if ($request->has('produtos')) {
                foreach ($request->produtos as $index => $produtoData) {

                    // Produto marcado para remoção
                    if (!empty($produtoData['_remove'])) {
                        $orcamento->itens()
                            ->where('produto_id', $produtoData['produto_id'])
                            ->delete();
                        continue;
                    }

                    // Recuperar preço original sem desconto
                    $precoOriginalSemDesconto = (float) ($produtoData['preco_original'] ?? 0);

                    if ($precoOriginalSemDesconto == 0) {
                        $itemExistente = $orcamento->itens()
                            ->where('produto_id', $produtoData['produto_id'])
                            ->first();
                        $precoOriginalSemDesconto = $itemExistente
                            ? (float) $itemExistente->valor_unitario
                            : (float) ($produtoData['valor_unitario'] ?? 0);
                    }

                    $quantidade      = (float) ($produtoData['quantidade'] ?? 0);
                    $liberarDesconto = isset($produtoData['liberar_desconto'])
                        ? (int) $produtoData['liberar_desconto']
                        : 1;
                    $tipoDesconto    = $produtoData['tipo_desconto'] ?? 'nenhum';
                    $descontoProduto = (float) ($produtoData['desconto_produto'] ?? 0);

                    // Valores padrão (sem desconto)
                    $valorUnitarioComDesconto = $precoOriginalSemDesconto;
                    $valorComDesconto         = $precoOriginalSemDesconto * $quantidade;
                    $descontoAplicadoItem     = 0;

                    Log::info("Processando produto existente na edição", [
                        'produto_id'       => $produtoData['produto_id'],
                        'preco_original'   => $precoOriginalSemDesconto,
                        'tipo_desconto'    => $tipoDesconto,
                        'desconto_produto' => $descontoProduto,
                        'quantidade'       => $quantidade,
                    ]);

                    // Aplicar desconto conforme tipo
                    if ($liberarDesconto === 1) {
                        if ($tipoDesconto === 'produto' && $descontoProduto > 0) {
                            $valorUnitarioComDesconto = $precoOriginalSemDesconto - $descontoProduto;
                            $valorComDesconto         = $valorUnitarioComDesconto * $quantidade;

                            $orcamento->descontos()->create([
                                'motivo'      => "Desconto individual de R$ "
                                    . number_format($descontoProduto, 2, ',', '.')
                                    . " por unidade do produto ID {$produtoData['produto_id']}",
                                'valor'       => $descontoProduto * $quantidade,
                                'porcentagem' => null,
                                'tipo'        => 'produto',
                                'produto_id'  => $produtoData['produto_id'],
                                'cliente_id'  => $orcamento->cliente_id,
                                'user_id'     => auth()->id(),
                            ]);

                            $itenscomdesconto = true;

                        } elseif ($tipoDesconto === 'percentual' && $descontoPercentual > 0) {
                            $subtotal                 = $precoOriginalSemDesconto * $quantidade;
                            $valorComDesconto         = $subtotal - ($subtotal * ($descontoPercentual / 100));
                            $valorUnitarioComDesconto = $valorComDesconto / $quantidade;
                            $descontoAplicadoItem     = $descontoPercentual;
                        }
                    }

                    $orcamento->itens()->updateOrCreate(
                        ['produto_id' => $produtoData['produto_id']],
                        [
                            'quantidade'                  => $quantidade,
                            'valor_unitario'              => $precoOriginalSemDesconto,
                            'valor_unitario_com_desconto' => $valorUnitarioComDesconto,
                            'desconto'                    => $descontoAplicadoItem,
                            'valor_com_desconto'          => $valorComDesconto,
                            'user_id'                     => $request->user()->id ?? null,
                        ]
                    );
                }
            }

            // ----------------------------------------------------------------
            // Produtos NOVOS adicionados via JavaScript
            // ----------------------------------------------------------------
            if ($request->has('itens')) {
                $itens        = $request->input('itens');
                $idsRecebidos = [];

                foreach ($itens as $item) {
                    if (empty($item['id'])) {
                        continue;
                    }

                    // Item de consulta de preço (sem preco_unitario)
                    if (empty($item['preco_unitario'])) {
                        $dadosItem = [
                            'descricao'     => $item['nome'] ?? null,
                            'quantidade'    => $item['quantidade'] ?? null,
                            'cor'           => $item['cor'] ?? null,
                            'fornecedor_id' => !empty($item['fornecedor_id']) ? $item['fornecedor_id'] : null,
                            'observacao'    => $item['observacoes'] ?? null,
                            'orcamento_id'  => $orcamento->id,
                            'usuario_id'    => auth()->id(),
                            'comprador_id'  => auth()->id(),
                            'status'        => 'Pendente',
                        ];

                        $consultaPreco = ConsultaPreco::where('id', $item['id'])
                            ->where('orcamento_id', $orcamento->id)
                            ->first();

                        if ($consultaPreco) {
                            $consultaPreco->update($dadosItem);
                            $idsRecebidos[] = $consultaPreco->id;
                        } else {
                            $novoItem       = ConsultaPreco::create($dadosItem);
                            $idsRecebidos[] = $novoItem->id;
                        }

                        continue;
                    }

                    // Produto novo com preço definido
                    $precoOriginalSemDesconto = (float) ($item['preco_original'] ?? $item['preco_unitario'] ?? 0);
                    $quantidade               = (float) ($item['quantidade'] ?? 0);
                    $liberarDesconto          = isset($item['liberar_desconto']) ? (int) $item['liberar_desconto'] : 1;
                    $tipoDesconto             = $item['tipo_desconto'] ?? 'nenhum';
                    $descontoProduto          = (float) ($item['desconto_produto'] ?? 0);

                    // Valores padrão (sem desconto)
                    $valorUnitarioComDesconto = $precoOriginalSemDesconto;
                    $valorComDesconto         = $precoOriginalSemDesconto * $quantidade;
                    $descontoAplicadoItem     = 0;

                    Log::info("Processando novo produto na edição", [
                        'produto_id'       => $item['id'],
                        'preco_original'   => $precoOriginalSemDesconto,
                        'tipo_desconto'    => $tipoDesconto,
                        'desconto_produto' => $descontoProduto,
                        'quantidade'       => $quantidade,
                    ]);

                    // Aplicar desconto conforme tipo
                    if ($liberarDesconto === 1) {
                        if ($tipoDesconto === 'produto' && $descontoProduto > 0) {
                            $valorUnitarioComDesconto = $precoOriginalSemDesconto - $descontoProduto;
                            $valorComDesconto         = $valorUnitarioComDesconto * $quantidade;

                            $orcamento->descontos()->create([
                                'motivo'      => "Desconto individual de R$ "
                                    . number_format($descontoProduto, 2, ',', '.')
                                    . " por unidade do produto ID {$item['id']}",
                                'valor'       => $descontoProduto * $quantidade,
                                'porcentagem' => null,
                                'tipo'        => 'produto',
                                'produto_id'  => $item['id'],
                                'cliente_id'  => $orcamento->cliente_id,
                                'user_id'     => auth()->id(),
                            ]);

                            $itenscomdesconto = true;

                        } elseif ($tipoDesconto === 'percentual' && $descontoPercentual > 0) {
                            $subtotal                 = $precoOriginalSemDesconto * $quantidade;
                            $valorComDesconto         = $subtotal - ($subtotal * ($descontoPercentual / 100));
                            $valorUnitarioComDesconto = $valorComDesconto / $quantidade;
                            $descontoAplicadoItem     = $descontoPercentual;
                        }
                    }

                    $orcamento->itens()->create([
                        'produto_id'                  => $item['id'],
                        'quantidade'                  => $quantidade,
                        'valor_unitario'              => $precoOriginalSemDesconto,
                        'valor_unitario_com_desconto' => $valorUnitarioComDesconto,
                        'desconto'                    => $descontoAplicadoItem,
                        'valor_com_desconto'          => $valorComDesconto,
                        'user_id'                     => $request->user()->id ?? null,
                    ]);
                }

                // Remover consultas de preço que não vieram no request
                if (!empty($idsRecebidos)) {
                    ConsultaPreco::where('orcamento_id', $orcamento->id)
                        ->whereNotIn('id', $idsRecebidos)
                        ->delete();
                }
            }

            // ----------------------------------------------------------------
            // Vidros REMOVIDOS
            // ----------------------------------------------------------------
            if ($request->has('vidros_removidos')) {
                foreach ($request->vidros_removidos as $vidroId) {
                    $vidro = $orcamento->vidros()->find($vidroId);
                    if ($vidro) {
                        $vidro->delete();
                    }
                }
            }

            // ----------------------------------------------------------------
            // Vidros EXISTENTES (edição)
            // ----------------------------------------------------------------
            if ($request->has('vidros_existentes')) {
                foreach ($request->vidros_existentes as $vidroData) {
                    if (empty($vidroData['id'])) {
                        continue;
                    }

                    $vidro = $orcamento->vidros()->find($vidroData['id']);
                    if (!$vidro) {
                        continue;
                    }

                    $vidro->update([
                        'descricao'            => $vidroData['descricao'] ?? '',
                        'quantidade'           => (float) ($vidroData['quantidade'] ?? 1),
                        'altura'               => (float) ($vidroData['altura'] ?? 0),
                        'largura'              => (float) ($vidroData['largura'] ?? 0),
                        'preco_metro_quadrado' => $brToDecimal($vidroData['preco_m2']) ?? 0,
                        'desconto'             => $descontoPercentual ?? 0,
                        'valor_total'          => $brToDecimal($vidroData['valor_total']) ?? 0,
                        'valor_com_desconto'   => $brToDecimal($vidroData['valor_com_desconto']) ?? 0,
                        'user_id'              => $request->user()->id ?? null,
                    ]);
                }
            }

            // ----------------------------------------------------------------
            // Vidros NOVOS
            // ----------------------------------------------------------------
            if ($request->has('vidros')) {
                foreach ($request->vidros as $vidroData) {
                    $temDados = isset($vidroData['preco_m2'], $vidroData['quantidade'], $vidroData['altura'], $vidroData['largura'])
                        && $vidroData['preco_m2'] !== null
                        && $vidroData['quantidade'] !== null
                        && $vidroData['altura'] !== null
                        && $vidroData['largura'] !== null;

                    if (!$temDados) {
                        continue;
                    }

                    $orcamento->vidros()->create([
                        'descricao'            => $vidroData['descricao'] ?? null,
                        'quantidade'           => (float) ($vidroData['quantidade'] ?? 0),
                        'altura'               => (float) ($vidroData['altura'] ?? 0),
                        'largura'              => (float) ($vidroData['largura'] ?? 0),
                        'preco_metro_quadrado' => $brToDecimal($vidroData['preco_m2']) ?? 0,
                        'desconto'             => $descontoPercentual ?? 0,
                        'valor_total'          => $brToDecimal($vidroData['valor_total']) ?? 0,
                        'valor_com_desconto'   => $brToDecimal($vidroData['valor_com_desconto']) ?? 0,
                        'user_id'              => $request->user()->id ?? null,
                    ]);
                }
            }

            // ----------------------------------------------------------------
            // Desconto PERCENTUAL global (registrar após processar todos os itens)
            // ----------------------------------------------------------------
            if ($descontoPercentual > 0) {
                $totalDescontoPercentual = 0;

                // Recarregar itens para garantir dados atualizados
                $orcamento->load('itens', 'vidros');

                foreach ($orcamento->itens as $item) {
                    if ($item->desconto > 0 && (float) $item->desconto === (float) $descontoPercentual) {
                        $subtotal                 = $item->valor_unitario * $item->quantidade;
                        $totalDescontoPercentual += $subtotal * ($descontoPercentual / 100);
                    }
                }

                foreach ($orcamento->vidros as $vidro) {
                    if ($vidro->valor_total > 0) {
                        $totalDescontoPercentual += $vidro->valor_total * ($descontoPercentual / 100);
                    }
                }

                if ($totalDescontoPercentual > 0) {
                    $orcamento->descontos()->create([
                        'motivo'      => 'Desconto percentual aplicado pelo vendedor',
                        'valor'       => $totalDescontoPercentual,
                        'porcentagem' => $descontoPercentual,
                        'tipo'        => 'percentual',
                        'cliente_id'  => $orcamento->cliente_id,
                        'user_id'     => auth()->id(),
                    ]);
                }
            }

            // ----------------------------------------------------------------
            // Desconto ESPECÍFICO (valor fixo em R$)
            // ----------------------------------------------------------------
            if ($descontoEspecifico) {
                $orcamento->descontos()->create([
                    'motivo'      => 'Desconto específico em reais',
                    'valor'       => $descontoEspecifico,
                    'porcentagem' => null,
                    'tipo'        => 'fixo',
                    'cliente_id'  => $orcamento->cliente_id,
                    'user_id'     => auth()->id(),
                ]);
            }

            DB::commit();

            // ----------------------------------------------------------------
            // Verificar necessidade de aprovações
            // ----------------------------------------------------------------
            $temDescontoPercentual = $descontoPercentual > 0;
            $temDescontoEspecifico = $descontoEspecifico > 0;
            $temQualquerDesconto   = $temDescontoPercentual || $temDescontoEspecifico || $itenscomdesconto;

            $descontoAprovadoCliente = (float) ($request->desconto_aprovado ?? 0);
            $descontoVendedor        = (float) (auth()->user()->vendedor->desconto ?? 0);

            $necessitaAprovacaoDesconto = $temQualquerDesconto && (
                $descontoPercentual > $descontoAprovadoCliente ||
                $descontoVendedor < $descontoPercentual         ||
                $itenscomdesconto
            );

            // ----------------------------------------------------------------
            // Definir status e redirecionar
            // ----------------------------------------------------------------
            if (!$temQualquerDesconto && !$necessitaAprovacaoPagamento) {
                // Nenhuma aprovação necessária — aprova direto
                $orcamento->status = 'Aprovado';
                $orcamento->save();
            } elseif ($necessitaAprovacaoDesconto && $necessitaAprovacaoPagamento) {
                $orcamento->status = 'Aprovar desconto';
                $orcamento->save();

                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('warning', "Orçamento atualizado (Revisão {$novaVersao}), mas é necessária a aprovação do desconto e do meio de pagamento.");
            } elseif ($necessitaAprovacaoDesconto) {
                $orcamento->status = 'Aprovar desconto';
                $orcamento->save();

                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('warning', "Orçamento atualizado (Revisão {$novaVersao}), mas é necessária a aprovação do desconto.");
            } elseif ($necessitaAprovacaoPagamento) {
                $orcamento->status = 'Aprovar pagamento';
                $orcamento->save();

                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('info', "Orçamento atualizado (Revisão {$novaVersao})! Aguardando aprovação do meio de pagamento especial.");
            }

            // ----------------------------------------------------------------
            // Nenhuma aprovação necessária — gera PDF normalmente
            // ----------------------------------------------------------------
            $pdfService          = new OrcamentoPdfService();
            $pdfGeradoComSucesso = $pdfService->gerarOrcamentoPdf($orcamento);

            if ($pdfGeradoComSucesso) {
                return redirect()
                    ->route('orcamentos.show', $orcamento->id)
                    ->with('success', "Orçamento atualizado (Revisão {$novaVersao}) e PDF gerado com sucesso!");
            }

            return redirect()
                ->route('orcamentos.show', $orcamento->id)
                ->with('warning', 'Orçamento atualizado, mas houve falha ao gerar o PDF. Contate o suporte.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ----------------------------------------------------------------
            // Erros de validação do Laravel — exibe campos com problema
            // ----------------------------------------------------------------
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            // ----------------------------------------------------------------
            // Qualquer outro erro — rollback + mensagem detalhada em dev
            // ----------------------------------------------------------------
            DB::rollBack();

            Log::error("Erro ao atualizar orçamento #{$orcamento->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            $mensagemErro = app()->environment('production')
                ? 'Erro interno ao atualizar o orçamento. Contate o suporte.'
                : '[' . class_basename($e) . '] '
                    . $e->getMessage()
                    . ' — Arquivo: ' . $e->getFile()
                    . ' — Linha: ' . $e->getLine();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $mensagemErro);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($orcamento_id)
    {
        $orcamento = Orcamento::findOrFail($orcamento_id);
        // cancela os lotes de separação ativos antes de excluir o orçamento
        $orcamento->cancelarLoteDeSeparacaoAtivo();
        $orcamento->delete();

        return redirect()->route('orcamentos.index')
            ->with('success', 'Orçamento excluído com sucesso!');
    }
}
