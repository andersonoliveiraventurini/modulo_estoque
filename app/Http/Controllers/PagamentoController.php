<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pagamento;
use App\Models\PagamentoForma;
use App\Models\PagamentoComprovante;
use App\Models\Orcamento;
use Illuminate\Support\Facades\Cache;
use App\Models\CondicoesPagamento;
use App\Models\ClienteCreditos;
use App\Models\ClienteCreditoMovimentacoes;
use App\Events\OrcamentoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\PagamentoPdfService;

class PagamentoController extends Controller
{
    /** ID da condição especial "Outros meios de pagamento" */
    const CONDICAO_ESPECIAL_OUTROS = 20;

    // ═════════════════════════════════════════════════════════════════════════
    // FORMULÁRIO
    // ═════════════════════════════════════════════════════════════════════════

    public function formPagamentoOrcamento($orcamentoId)
    {
        $orcamento = Orcamento::with(['cliente', 'vendedor', 'condicaoPagamento', 'itens'])
            ->findOrFail($orcamentoId);

        // Todas as condições ativas para o atendente escolher no balcão
        $condicoes = CondicoesPagamento::where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        // Condição especial (ID 20 = outros_meios_pagamento)
        $condicaoEspecial = (int) $orcamento->condicao_id === self::CONDICAO_ESPECIAL_OUTROS;

        // Condição pré-definida pelo orçamento
        $condicaoPadrao = $orcamento->condicaoPagamento;

        // Lista para o select: remove tipo "outros" se não for condição especial
        // (o atendente não pode selecionar "outros" manualmente)
        $condicoesBalcao = $condicoes->filter(function ($c) use ($condicaoEspecial) {
            return !($c->tipo === 'outros' && !$condicaoEspecial);
        })->values();

        // Desconto de balcão bloqueado apenas na condição especial 20;
        // nas demais é liberado dinamicamente quando o cliente pagar em pix/dinheiro
        $permiteDescontoBalcao = !$condicaoEspecial;

        // Nota fiscal pré-definida pelo orçamento
        $precisaNotaFiscal = strtolower($orcamento->tipo_documento ?? '') === 'nota fiscal';

        // Validação CNPJ ativo na Receita Federal
        $cliente = Cliente::with('enderecos')->findOrFail($orcamento->cliente_id);
        $cnpj = preg_replace('/\D/', '', $cliente->cnpj);

        $body = Cache::remember("cnpj_{$cnpj}", now()->addHours(24), function () use ($cnpj) {
            $response = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

            if ($response->status() === 429 || !$response->successful()) {
                return null;
            }

            $data = json_decode($response->body(), true);
            return json_last_error() === JSON_ERROR_NONE ? $data : null;
        });

        $ativo = strtoupper(trim($body['descricao_situacao_cadastral'] ?? '')) === 'ATIVA';

        return view('paginas.pagamentos.form-pagamento-balcao', compact(
            'orcamento',
            'condicoes',
            'condicoesBalcao',
            'ativo',
            'condicaoPadrao',
            'condicaoEspecial',
            'precisaNotaFiscal',
            'permiteDescontoBalcao'
        ));
    }

    public function verComprovantePdf(Pagamento $pagamento)
    {
        abort_if(
            empty($pagamento->pdf_path) || ! Storage::disk('public')->exists($pagamento->pdf_path),
            404,
            'Comprovante PDF não encontrado.'
        );

        $conteudo = Storage::disk('public')->get($pagamento->pdf_path);

        return response($conteudo, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="comprovante-pagamento-' . $pagamento->id . '.pdf"')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    // ═════════════════════════════════════════════════════════════════════════
    // SALVAR
    // ═════════════════════════════════════════════════════════════════════════

    public function salvarPagamentoOrcamento(Request $request, $orcamentoId)
{
    try {
        $validated = $request->validate([
            'formas_pagamento'               => 'required|array|min:1',
            'formas_pagamento.*.condicao_id' => 'required|exists:condicoes_pagamento,id',
            'formas_pagamento.*.valor'       => 'required|numeric|min:0.01',
            'desconto_balcao'                => 'nullable|numeric|min:0',
            'precisa_nota_fiscal'            => 'nullable|boolean',
            'cnpj_cpf_nota'                  => 'nullable|string|max:20',
            'observacoes'                    => 'nullable|string|max:1000',
            'comprovantes'                   => 'nullable|array|max:10',
            'comprovantes.*'                 => 'file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ], [
            'formas_pagamento.required'               => 'Informe pelo menos uma forma de pagamento',
            'formas_pagamento.*.condicao_id.required' => 'Selecione a condição de pagamento',
            'formas_pagamento.*.condicao_id.exists'   => 'Condição de pagamento inválida',
            'formas_pagamento.*.valor.required'       => 'Informe o valor',
            'formas_pagamento.*.valor.numeric'        => 'Valor deve ser numérico',
            'formas_pagamento.*.valor.min'            => 'Valor deve ser maior que zero',
            'comprovantes.max'                        => 'Máximo de 10 comprovantes',
            'comprovantes.*.mimes'                    => 'Formatos aceitos: PDF, JPG, PNG, WEBP',
            'comprovantes.*.max'                      => 'Cada comprovante deve ter no máximo 10 MB',
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->validator)->withInput();
    }
 
    // ── Executa a transaction e captura o pagamento criado ────────────────
    // A transaction NÃO retorna mais o redirect direto, para que possamos
    // chamar o PagamentoPdfService APÓS o commit, fora de qualquer transação.
    $pagamento = null;
    $erroTransacao = null;
 
    try {
        $pagamento = DB::transaction(function () use ($request, $orcamentoId, $validated) {
            $orcamento = Orcamento::with(['cliente'])->findOrFail($orcamentoId);
 
            // ── Já pago? ──────────────────────────────────────────────────
            if (Pagamento::where('orcamento_id', $orcamentoId)->where('estornado', false)->exists()) {
                throw new \Exception('Este orçamento já possui um pagamento registrado.');
            }
 
            // ── Proteção: bloqueia tipo "outros" se não for condição 20 ───
            $condicaoEspecial = (int) $orcamento->condicao_id === self::CONDICAO_ESPECIAL_OUTROS;
            foreach ($validated['formas_pagamento'] as $forma) {
                $cond = CondicoesPagamento::find($forma['condicao_id']);
                if (! $condicaoEspecial && $cond?->tipo === 'outros') {
                    throw new \Exception('A condição "Outros" não está disponível para este orçamento.');
                }
            }
 
            // ── Valores base ──────────────────────────────────────────────
            $valorTotal       = (float) $orcamento->valor_total_itens;
            $descontoOriginal = (float) ($orcamento->totalDescontosAprovados() ?? 0);
            $descontoBalcao   = (float) ($request->desconto_balcao ?? 0);
 
            // ── Valida desconto de balcão ─────────────────────────────────
            $valorPixDinheiro = 0;
            foreach ($validated['formas_pagamento'] as $forma) {
                $cond = CondicoesPagamento::find($forma['condicao_id']);
                if ($cond?->permiteDescontoBalcao()) {
                    $valorPixDinheiro += (float) $forma['valor'];
                }
            }
 
            if ($descontoBalcao > 0 && $valorPixDinheiro <= 0) {
                throw new \Exception('Desconto de balcão só é permitido quando parte do pagamento for em Dinheiro ou PIX.');
            }
 
            $maxDesconto = $valorPixDinheiro * 0.03;
            if ($descontoBalcao > $maxDesconto + 0.01) {
                throw new \Exception(
                    'Desconto máximo é 3% do valor em Dinheiro/PIX (R$ ' . number_format($maxDesconto, 2, ',', '.') . ').'
                );
            }
 
            // ── Valor final e troco ───────────────────────────────────────
            $valorFinal = $valorTotal - $descontoOriginal - $descontoBalcao;
            $valorPago  = (float) array_sum(array_column($validated['formas_pagamento'], 'valor'));
 
            if ($valorPago < $valorFinal - 0.01) {
                throw new \Exception(
                    'Valor pago insuficiente! Falta: R$ ' . number_format($valorFinal - $valorPago, 2, ',', '.')
                );
            }
 
            $troco = max(0, $valorPago - $valorFinal);
 
            // ── Cria o pagamento ──────────────────────────────────────────
            $pagamento = Pagamento::create([
                'orcamento_id'          => $orcamentoId,
                'condicao_pagamento_id' => $orcamento->condicao_id,
                'desconto_aplicado'     => $descontoOriginal,
                'desconto_balcao'       => $descontoBalcao,
                'valor_final'           => $valorFinal,
                'valor_pago'            => $valorPago,
                'troco'                 => $troco,
                'data_pagamento'        => now(),
                'tipo_documento'        => $request->boolean('precisa_nota_fiscal') ? 'nota_fiscal' : 'cupom_fiscal',
                'cnpj_cpf_nota'         => $request->cnpj_cpf_nota,
                'observacoes'           => $request->observacoes,
                'user_id'               => Auth::id(),
            ]);
 
            // ── Salva cada forma de pagamento ─────────────────────────────
            foreach ($validated['formas_pagamento'] as $index => $forma) {
                $cond       = CondicoesPagamento::find($forma['condicao_id']);
                $usaCredito = $cond?->isCreditoCliente() ?? false;
 
                if ($usaCredito) {
                    $this->utilizarCreditos(
                        $orcamento->cliente_id,
                        $forma['valor'],
                        $pagamento->id,
                        'orcamento'
                    );
                }
 
                $pagamentoForma = PagamentoForma::create([
                    'pagamento_id'          => $pagamento->id,
                    'condicao_pagamento_id' => $forma['condicao_id'],
                    'valor'                 => $forma['valor'],
                    'usa_credito'           => $usaCredito,
                    'parcelas'              => 1,
                    'observacoes'           => null,
                ]);
 
                // Comprovantes vinculados a esta forma específica
                $keyComprovantes = "comprovantes_forma_{$index}";
                if ($request->hasFile($keyComprovantes)) {
                    foreach ($request->file($keyComprovantes) as $arquivo) {
                        $this->salvarComprovante($arquivo, $pagamento->id, $pagamentoForma->id);
                    }
                }
            }
 
            // Comprovantes gerais
            if ($request->hasFile('comprovantes')) {
                foreach ($request->file('comprovantes') as $arquivo) {
                    $this->salvarComprovante($arquivo, $pagamento->id, null);
                }
            }
 
            // Atualiza status do orçamento
            $orcamento->update(['status' => 'Pago', 'data_pagamento' => now()]);

            // ── Dispara evento de pagamento (baixa automática no estoque, integração e-commerce, etc) ──
            event(new OrcamentoPago($orcamento));

            // Retorna o pagamento criado para uso fora da transaction
            return $pagamento;
        });
 
    } catch (\Exception $e) {
        return back()
            ->withErrors(['erro' => $e->getMessage()])
            ->withInput();
    }
 
    // ── Gera o PDF FORA da transaction ────────────────────────────────────
    $pdfGerado = app(PagamentoPdfService::class)->gerar($pagamento);
 
    if ($pdfGerado) {
        return redirect()->route('orcamentos.index')
            ->with('success', "Pagamento #{$pagamento->id} registrado com sucesso!");
    }
 
    // PDF falhou: pagamento foi salvo, mas comprovante não foi gerado.
    // Redireciona para o show do pagamento com aviso visível.
    return redirect()->route('pagamentos.show', $pagamento->id)
        ->with('success', "Pagamento #{$pagamento->id} registrado com sucesso!")
        ->with('warning', 'O comprovante PDF não pôde ser gerado automaticamente. Verifique os logs ou tente gerar manualmente.');
}

    // ═════════════════════════════════════════════════════════════════════════
    // DOWNLOAD DE COMPROVANTE
    // ═════════════════════════════════════════════════════════════════════════

    public function downloadComprovante(PagamentoComprovante $comprovante)
    {
        abort_unless(
            Storage::disk('private')->exists($comprovante->path),
            404,
            'Comprovante não encontrado.'
        );

        return Storage::disk('private')->download($comprovante->path, $comprovante->nome_original);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // SHOW
    // ═════════════════════════════════════════════════════════════════════════

    public function show($id)
    {
        $pagamento = Pagamento::with([
            'orcamento.cliente',
            'formas.condicaoPagamento',
            'formas.comprovantes.user',
            'comprovantes.user',
            'user',
        ])->findOrFail($id);

        return view('paginas.pagamentos.show', compact('pagamento'));
    }

    // ═════════════════════════════════════════════════════════════════════════
    // ESTORNAR
    // ═════════════════════════════════════════════════════════════════════════

    public function estornar(Request $request, $id)
    {
        $request->validate([
            'motivo_estorno' => 'required|string|min:10',
        ]);

        try {
            return DB::transaction(function () use ($request, $id) {
                $pagamento = Pagamento::with(['formas', 'orcamento'])->findOrFail($id);

                if ($pagamento->estornado) {
                    return back()->withErrors(['erro' => 'Este pagamento já foi estornado.']);
                }

                // Estorna créditos utilizados
                foreach ($pagamento->formas()->where('usa_credito', true)->get() as $forma) {
                    $movimentacoes = ClienteCreditoMovimentacoes::where('referencia_tipo', 'orcamento')
                        ->where('referencia_id', $pagamento->id)
                        ->where('tipo_movimentacao', 'utilizacao')
                        ->get();

                    foreach ($movimentacoes as $mov) {
                        $credito        = ClienteCreditos::find($mov->credito_id);
                        $saldoAnterior  = $credito->valor_disponivel;
                        $saldoPosterior = $saldoAnterior + $mov->valor_movimentado;

                        ClienteCreditoMovimentacoes::create([
                            'credito_id'        => $credito->id,
                            'cliente_id'        => $credito->cliente_id,
                            'tipo_movimentacao' => 'estorno',
                            'valor_movimentado' => $mov->valor_movimentado,
                            'saldo_anterior'    => $saldoAnterior,
                            'saldo_posterior'   => $saldoPosterior,
                            'motivo'            => "Estorno: {$request->motivo_estorno}",
                            'referencia_tipo'   => 'orcamento',
                            'referencia_id'     => $pagamento->id,
                            'usuario_id'        => Auth::id(),
                        ]);

                        $credito->valor_disponivel = $saldoPosterior;
                        if ($credito->status === 'utilizado' && $saldoPosterior > 0) {
                            $credito->status = 'ativo';
                        }
                        $credito->save();
                    }
                }

                $pagamento->update([
                    'estornado'          => true,
                    'data_estorno'       => now(),
                    'motivo_estorno'     => $request->motivo_estorno,
                    'usuario_estorno_id' => Auth::id(),
                ]);

                // ── Retorno automático ao estoque no estorno ────────────────────
                if ($pagamento->orcamento) {
                    $pagamento->orcamento->load('itens.produto');
                    foreach ($pagamento->orcamento->itens->whereNotNull('produto_id') as $item) {
                        if ($item->produto) {
                            $item->produto->increment('estoque_atual', $item->quantidade);
                        }
                    }
                }

                $pagamento->orcamento?->update(['status' => 'Aprovado']);

                return redirect()->route('pagamentos.show', $pagamento->id)
                    ->with('success', 'Pagamento estornado com sucesso!');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['erro' => 'Erro ao estornar: ' . $e->getMessage()]);
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    // MÉTODOS PRIVADOS
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Salva um arquivo de comprovante no disco private.
     */
    private function salvarComprovante($arquivo, int $pagamentoId, ?int $pagamentoFormaId): PagamentoComprovante
    {
        $path = $arquivo->store("pagamentos/{$pagamentoId}/comprovantes", 'private');

        return PagamentoComprovante::create([
            'pagamento_id'       => $pagamentoId,
            'pagamento_forma_id' => $pagamentoFormaId,
            'nome_original'      => $arquivo->getClientOriginalName(),
            'path'               => $path,
            'mime_type'          => $arquivo->getMimeType(),
            'tamanho'            => $arquivo->getSize(),
            'user_id'            => Auth::id(),
        ]);
    }

    /**
     * Utiliza créditos do cliente em ordem FIFO por validade.
     */
    protected function utilizarCreditos(
        int $clienteId,
        float $valorUtilizar,
        int $pagamentoId,
        string $referenciaTipo
    ): array {
        $creditos = ClienteCreditos::where('cliente_id', $clienteId)
            ->where('status', 'ativo')
            ->where('valor_disponivel', '>', 0)
            ->where(fn($q) => $q->whereNull('data_validade')->orWhere('data_validade', '>=', now()))
            ->orderBy('data_validade')
            ->orderBy('created_at')
            ->get();

        if ($creditos->isEmpty()) {
            throw new \Exception('Cliente não possui créditos disponíveis');
        }

        $saldoTotal = $creditos->sum('valor_disponivel');
        if ($saldoTotal < $valorUtilizar) {
            throw new \Exception('Créditos insuficientes. Disponível: R$ ' . number_format($saldoTotal, 2, ',', '.'));
        }

        $valorRestante      = $valorUtilizar;
        $creditosUtilizados = [];

        foreach ($creditos as $credito) {
            if ($valorRestante <= 0) break;

            $valorUsar      = min($credito->valor_disponivel, $valorRestante);
            $saldoAnterior  = $credito->valor_disponivel;
            $saldoPosterior = $saldoAnterior - $valorUsar;

            ClienteCreditoMovimentacoes::create([
                'credito_id'        => $credito->id,
                'cliente_id'        => $clienteId,
                'tipo_movimentacao' => 'utilizacao',
                'valor_movimentado' => $valorUsar,
                'saldo_anterior'    => $saldoAnterior,
                'saldo_posterior'   => $saldoPosterior,
                'motivo'            => "Utilização no pagamento #{$pagamentoId}",
                'referencia_tipo'   => $referenciaTipo,
                'referencia_id'     => $pagamentoId,
                'usuario_id'        => Auth::id(),
            ]);

            $credito->valor_disponivel = $saldoPosterior;
            if ($saldoPosterior == 0) $credito->status = 'utilizado';
            $credito->save();

            $creditosUtilizados[] = ['credito_id' => $credito->id, 'valor_usado' => $valorUsar];
            $valorRestante -= $valorUsar;
        }

        return $creditosUtilizados;
    }
}
