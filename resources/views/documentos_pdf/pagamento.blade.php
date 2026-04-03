<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprovante de Pagamento #{{ $pagamento->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            margin: 1cm 1.5cm;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.45;
        }

        /* ── Faixa de status ────────────────────────────────────────── */
        .status-bar {
            padding: 7px 24px;
            font-size: 10.5px;
            font-weight: bold;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #fff;
            text-align: center;
        }
        .status-pago     { background-color: #27ae60; }
        .status-estorno  { background-color: #c0392b; }

        /* ── Conteúdo principal ─────────────────────────────────────── */
        .content { padding: 16px 24px; }

        /* ── Seção ──────────────────────────────────────────────────── */
        .section { margin-bottom: 14px; }

        .section-title {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #1e3a5f;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 4px;
            margin-bottom: 9px;
        }

        /* ── Grid de campos ─────────────────────────────────────────── */
        .fields-table { width: 100%; border-collapse: collapse; }
        .fields-table td { vertical-align: top; padding: 0 10px 8px 0; }
        .fields-table td:last-child { padding-right: 0; }

        .field-label {
            font-size: 8px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .field-value {
            font-size: 11px;
            color: #1a1a1a;
            font-weight: 500;
        }
        .field-value.mono {
            font-family: 'Courier New', monospace;
            font-size: 10.5px;
        }

        /* ── Tabela de itens do orçamento ───────────────────────────── */
        .table-itens {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            font-size: 10px;
        }
        .table-itens thead tr {
            background-color: #1e3a5f;
            color: #fff;
        }
        .table-itens thead th {
            padding: 7px 8px;
            font-size: 9px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-itens thead th.right { text-align: right; }
        .table-itens tbody tr { border-bottom: 1px solid #e8edf2; }
        .table-itens tbody tr:nth-child(even) { background-color: #f5f8fb; }
        .table-itens tbody td { padding: 6px 8px; vertical-align: middle; }
        .table-itens tbody td.right { text-align: right; }
        .table-itens tfoot tr { background-color: #eef3f8; font-weight: bold; }
        .table-itens tfoot td { padding: 6px 8px; }
        .table-itens tfoot td.right { text-align: right; }

        /* ── Tabela de formas de pagamento ──────────────────────────── */
        .table-formas {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .table-formas thead tr { background-color: #2c5f8a; color: #fff; }
        .table-formas thead th {
            padding: 7px 10px;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table-formas thead th.right { text-align: right; }
        .table-formas tbody tr { border-bottom: 1px solid #dde4ed; }
        .table-formas tbody tr:nth-child(even) { background-color: #f0f4f9; }
        .table-formas tbody td { padding: 7px 10px; vertical-align: middle; }
        .table-formas tbody td.right { text-align: right; }

        /* ── Badge tipo de método ───────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-dinheiro    { background-color: #d4edda; color: #155724; }
        .badge-pix         { background-color: #cce5ff; color: #004085; }
        .badge-cartao      { background-color: #e2d9f3; color: #432874; }
        .badge-boleto      { background-color: #fff3cd; color: #856404; }
        .badge-credito     { background-color: #d1ecf1; color: #0c5460; }
        .badge-outros      { background-color: #e2e3e5; color: #383d41; }

        /* ── Caixa de totais ────────────────────────────────────────── */
        .totais-box {
            background-color: #f0f4f9;
            border: 1.5px solid #c5d5e8;
            border-radius: 5px;
            padding: 12px 16px;
            margin-top: 12px;
        }
        .totais-table { width: 100%; border-collapse: collapse; }
        .totais-table td { padding: 3px 6px; vertical-align: middle; }
        .totais-table td.label-col {
            width: 55%;
            font-size: 11px;
            color: #444;
        }
        .totais-table td.value-col {
            width: 45%;
            text-align: right;
            font-size: 11px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .totais-table tr.separator td {
            border-top: 1px solid #c5d5e8;
            padding-top: 6px;
        }
        .totais-table tr.total-final td.label-col {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a5f;
        }
        .totais-table tr.total-final td.value-col {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a5f;
        }
        .totais-table tr.troco td.label-col { color: #856404; }
        .totais-table tr.troco td.value-col { color: #856404; }
        .totais-table tr.desconto td.value-col { color: #155724; }

        /* ── Alerta de estorno ──────────────────────────────────────── */
        .estorno-box {
            background-color: #fdf3f2;
            border: 2px solid #e74c3c;
            border-radius: 5px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }
        .estorno-box .estorno-title {
            font-size: 11px;
            font-weight: bold;
            color: #c0392b;
            margin-bottom: 5px;
        }
        .estorno-box .estorno-detail { font-size: 10px; color: #7b241c; }

        /* ── Documento fiscal ───────────────────────────────────────── */
        .doc-fiscal-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 8px 12px;
            display: inline-block;
        }

        /* ── Observações ────────────────────────────────────────────── */
        .obs-box {
            background-color: #fffef0;
            border-left: 3px solid #f0c040;
            padding: 8px 12px;
            font-size: 10.5px;
            color: #444;
            border-radius: 0 4px 4px 0;
        }

        /* ── Rodapé ─────────────────────────────────────────────────── */
        .footer {
            border-top: 1px solid #dde4ed;
            padding: 10px 24px;
            margin-top: 4px;
        }
        .footer-table { width: 100%; }
        .footer-table td { vertical-align: middle; font-size: 8.5px; color: #888; }
        .footer-table td.right { text-align: right; }

        /* ── Assinatura ─────────────────────────────────────────────── */
        .assinatura-table { width: 100%; margin-top: 20px; }
        .assinatura-table td {
            width: 48%;
            text-align: center;
            padding: 0 12px;
            vertical-align: bottom;
        }
        .assinatura-linha {
            border-top: 1px solid #555;
            padding-top: 5px;
            font-size: 9px;
            color: #555;
            margin-top: 24px;
        }

        /* ── Utilidades ─────────────────────────────────────────────── */
        .text-muted  { color: #777; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .font-bold   { font-weight: bold; }
        .mt-4        { margin-top: 4px; }
        .mt-8        { margin-top: 8px; }
        .spacer-row  { height: 6px; }

        /* ── Devoluções e Trocas ────────────────────────────────────── */
        .politica-box {
            margin-top: 15px;
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }
        .politica-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: left;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .politica-text {
            line-height: 1.15;
            text-align: justify;
        }
        .politica-text p {
            text-indent: 0.5cm;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

    {{-- ══════════════════════════════════════════════════════════════
         CABEÇALHO — padrão ACAV
    ══════════════════════════════════════════════════════════════ --}}
    @include('documentos_pdf.partials.cabecalho', [
        'docLabel'  => 'Comprovante',
        'docNumero' => str_pad($pagamento->id, 6, '0', STR_PAD_LEFT),
        'docData'   => \Carbon\Carbon::parse($pagamento->data_pagamento)->format('d/m/Y H:i'),
    ])

    {{-- Título do documento --}}
    <h2 style="margin:0 0 6px; font-size:16px; text-transform:uppercase; text-align:center; font-family: DejaVu Sans, sans-serif;">
        Comprovante de Pagamento
        @if($tipoRegistro === 'orcamento')
            — Orçamento #{{ $registro->id }}
        @else
            — Pedido #{{ $registro->id }}
        @endif
    </h2>

    {{-- Faixa de status --}}
    <div class="status-bar {{ $pagamento->estornado ? 'status-estorno' : 'status-pago' }}">
        {{ $pagamento->estornado ? '⚠ PAGAMENTO ESTORNADO' : '✓ PAGAMENTO CONFIRMADO' }}
    </div>

    <div class="content">

        {{-- ── Alerta de estorno ──────────────────────────────────── --}}
        @if($pagamento->estornado)
            <div class="estorno-box">
                <div class="estorno-title">Pagamento Estornado</div>
                <div class="estorno-detail">
                    <strong>Data:</strong>
                    {{ \Carbon\Carbon::parse($pagamento->data_estorno)->format('d/m/Y H:i') }}
                    &nbsp;&nbsp;
                    <strong>Operador:</strong>
                    {{ $pagamento->usuarioEstorno->name ?? '—' }}
                </div>
                <div class="estorno-detail mt-4">
                    <strong>Motivo:</strong> {{ $pagamento->motivo_estorno }}
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             SEÇÃO 1 — DADOS DO CLIENTE E VENDA
        ══════════════════════════════════════════════════════════ --}}
        <div class="section">
            <div class="section-title">Dados do Cliente e Venda</div>
            <table class="fields-table">
                <tr>
                    <td style="width:35%">
                        <div class="field-label">Cliente</div>
                        <div class="field-value">{{ $registro->cliente->nome ?? '—' }}</div>
                    </td>
                    <td style="width:25%">
                        <div class="field-label">CNPJ / CPF</div>
                        <div class="field-value mono">{{ $registro->cliente->cnpj ?? $registro->cliente->cpf ?? '—' }}</div>
                    </td>
                    <td style="width:20%">
                        <div class="field-label">{{ $tipoRegistro === 'orcamento' ? 'Orçamento' : 'Pedido' }} Nº</div>
                        <div class="field-value font-bold">#{{ $registro->id }}</div>
                    </td>
                    <td style="width:20%">
                        <div class="field-label">Vendedor</div>
                        <div class="field-value">{{ $registro->vendedor->name ?? '—' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-label">Condição de Pagamento (orçamento)</div>
                        <div class="field-value">{{ $registro->condicaoPagamento->nome ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="field-label">Condição Aplicada</div>
                        <div class="field-value">{{ $pagamento->condicaoPagamento->nome ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="field-label">Data do Pagamento</div>
                        <div class="field-value">{{ \Carbon\Carbon::parse($pagamento->data_pagamento)->format('d/m/Y H:i') }}</div>
                    </td>
                    <td>
                        <div class="field-label">Operador</div>
                        <div class="field-value">{{ $pagamento->user->name ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SEÇÃO 2 — ITENS DO ORÇAMENTO / PEDIDO
        ══════════════════════════════════════════════════════════ --}}
        @if($registro->itens && $registro->itens->count())
        <div class="section">
            <div class="section-title">Itens da Venda</div>
            <table class="table-itens">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:45%">Produto / Descrição</th>
                        <th class="right" style="width:10%">Qtd</th>
                        <th class="right" style="width:15%">Preço Unit.</th>
                        <th class="right" style="width:10%">Desc.</th>
                        <th class="right" style="width:15%">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registro->itens as $i => $item)
                        @php
                            $unitario  = (float) ($item->preco_unitario ?? $item->valor_unitario ?? 0);
                            $descItem  = (float) ($item->desconto ?? 0);
                            $subtotal  = (float) ($item->subtotal ?? ($item->quantidade * $unitario - $descItem));
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                {{ $item->produto->nome ?? $item->descricao ?? '—' }}
                                @if(!empty($item->referencia))
                                    <br><span class="text-muted" style="font-size:9px">Ref: {{ $item->referencia }}</span>
                                @endif
                            </td>
                            <td class="right">{{ number_format($item->quantidade, 0, ',', '.') }}</td>
                            <td class="right">R$ {{ number_format($unitario, 2, ',', '.') }}</td>
                            <td class="right">
                                @if($descItem > 0)
                                    <span style="color:#c0392b">R$ {{ number_format($descItem, 2, ',', '.') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="right font-bold">R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right">Subtotal dos itens</td>
                        <td class="right">R$ {{ number_format($registro->valor_total_itens ?? $registro->valor_total ?? 0, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             SEÇÃO 3 — FORMAS DE PAGAMENTO UTILIZADAS
        ══════════════════════════════════════════════════════════ --}}
        <div class="section">
            <div class="section-title">Formas de Pagamento Utilizadas</div>
            <table class="table-formas">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:30%">Método</th>
                        <th style="width:15%">Tipo</th>
                        <th class="right" style="width:12%">Parcelas</th>
                        <th class="right" style="width:16%">Vlr. Parcela</th>
                        <th class="right" style="width:16%">Valor Total</th>
                        <th style="width:6%">Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagamento->formas as $i => $forma)
                        @php
                            // formas usa condicaoPagamento (CondicoesPagamento com campo 'tipo')
                            $condicao   = $forma->condicaoPagamento;
                            $nomeMetodo = $condicao->nome ?? '—';
                            $tipo       = strtolower($condicao->tipo ?? 'outros');
                            $badgeClass = match(true) {
                                $tipo === 'dinheiro'                               => 'badge-dinheiro',
                                $tipo === 'pix'                                    => 'badge-pix',
                                in_array($tipo, ['cartao_credito','cartao_debito']) => 'badge-cartao',
                                str_starts_with($tipo, 'boleto')                   => 'badge-boleto',
                                $tipo === 'credito_cliente'                        => 'badge-credito',
                                default                                            => 'badge-outros',
                            };
                            $tipoLabel = match(true) {
                                $tipo === 'dinheiro'                               => 'Dinheiro',
                                $tipo === 'pix'                                    => 'PIX',
                                $tipo === 'cartao_credito'                         => 'Cartão Créd.',
                                $tipo === 'cartao_debito'                          => 'Cartão Déb.',
                                str_starts_with($tipo, 'boleto')                   => 'Boleto',
                                str_starts_with($tipo, 'cheque')                   => 'Cheque',
                                $tipo === 'credito_cliente'                        => 'Crédito',
                                $tipo === 'transferencia'                          => 'Transferência',
                                default                                            => 'Outros',
                            };
                            // pagamento_formas não tem parcelas/valor_parcela — padrão 1x
                            $parcelas     = (int) ($forma->parcelas ?? 1);
                            $valorParcela = (float) ($forma->valor_parcela ?? 0);
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="font-bold">{{ $nomeMetodo }}</td>
                            <td><span class="badge {{ $badgeClass }}">{{ $tipoLabel }}</span></td>
                            <td class="right">
                                @if($parcelas > 1 || $tipo === 'cartao_credito')
                                    {{ $parcelas }}x
                                @else
                                    1x
                                @endif
                            </td>
                            <td class="right">
                                @if(($parcelas > 1 || $tipo === 'cartao_credito') && $valorParcela > 0)
                                    R$ {{ number_format($valorParcela, 2, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="right font-bold">R$ {{ number_format($forma->valor, 2, ',', '.') }}</td>
                            <td class="text-center">
                                @if($forma->usa_credito)
                                    <span style="color:#0c5460;font-weight:bold;">✓</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SEÇÃO 4 — RESUMO FINANCEIRO
        ══════════════════════════════════════════════════════════ --}}
        <div class="section">
            <div class="section-title">Resumo Financeiro</div>
            <div class="totais-box">
                <table class="totais-table">
                    <tr>
                        <td class="label-col">Valor total dos itens</td>
                        <td class="value-col">R$ {{ number_format($registro->valor_total_itens ?? $registro->valor_total ?? 0, 2, ',', '.') }}</td>
                    </tr>

                    @if((float)($pagamento->desconto_aplicado ?? 0) > 0)
                    <tr class="desconto">
                        <td class="label-col">Desconto aprovado</td>
                        <td class="value-col">– R$ {{ number_format($pagamento->desconto_aplicado, 2, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if((float)($pagamento->desconto_balcao ?? 0) > 0)
                    <tr class="desconto">
                        <td class="label-col">Desconto no balcão (PIX/Dinheiro)</td>
                        <td class="value-col">– R$ {{ number_format($pagamento->desconto_balcao, 2, ',', '.') }}</td>
                    </tr>
                    @endif

                    <tr class="separator total-final">
                        <td class="label-col">Valor Final da Venda</td>
                        <td class="value-col">R$ {{ number_format($pagamento->valor_final, 2, ',', '.') }}</td>
                    </tr>

                    <tr class="spacer-row"><td colspan="2"></td></tr>

                    <tr>
                        <td class="label-col">Valor recebido</td>
                        <td class="value-col">R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</td>
                    </tr>

                    @if((float)($pagamento->troco ?? 0) > 0)
                    <tr class="troco">
                        <td class="label-col">Troco</td>
                        <td class="value-col">R$ {{ number_format($pagamento->troco, 2, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             SEÇÃO 5 — DOCUMENTO FISCAL
        ══════════════════════════════════════════════════════════ --}}
        <div class="section">
            <div class="section-title">Documento Fiscal</div>
            <table class="fields-table">
                <tr>
                    <td style="width:25%">
                        <div class="field-label">Tipo</div>
                        <div class="field-value">
                            @if($pagamento->tipo_documento === 'nota_fiscal')
                                📄 Nota Fiscal
                            @else
                                🧾 Cupom Fiscal
                            @endif
                        </div>
                    </td>
                    @if($pagamento->numero_documento)
                    <td style="width:25%">
                        <div class="field-label">Número do Documento</div>
                        <div class="field-value mono">{{ $pagamento->numero_documento }}</div>
                    </td>
                    @endif
                    @if($pagamento->cnpj_cpf_nota)
                    <td style="width:30%">
                        <div class="field-label">CNPJ/CPF da Nota</div>
                        <div class="field-value mono">{{ $pagamento->cnpj_cpf_nota }}</div>
                    </td>
                    @endif
                    <td>
                        <div class="field-label">Emitido por</div>
                        <div class="field-value">{{ $pagamento->user->name ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             OBSERVAÇÕES (se houver)
        ══════════════════════════════════════════════════════════ --}}
        @if($pagamento->observacoes)
        <div class="section">
            <div class="section-title">Observações</div>
            <div class="obs-box">{{ $pagamento->observacoes }}</div>
        </div>
        @endif

        {{-- ── Assinaturas ─────────────────────────────────────────── --}}
        <table class="assinatura-table">
            <tr>
                <td>
                    <div class="assinatura-linha">Assinatura do Cliente</div>
                </td>
                <td></td>
                <td>
                    <div class="assinatura-linha">Assinatura do Responsável</div>
                </td>
            </tr>
        </table>

        {{-- ══════════════════════════════════════════════════════════════
             COMPROVANTES
        ══════════════════════════════════════════════════════════════ --}}
        @if($pagamento->comprovantes && $pagamento->comprovantes->count() > 0)
            <div style="page-break-before: always;"></div>
            <div class="section">
                <div class="section-title" style="text-align: center; font-size: 14px; margin-bottom: 20px;">
                    Comprovantes Anexados
                </div>
                
                @foreach($pagamento->comprovantes as $comprovante)
                    @if($comprovante->isImagem())
                        <div style="text-align: center; margin-bottom: 30px; page-break-inside: avoid;">
                            <div style="font-size: 11px; font-weight: bold; margin-bottom: 10px; color: #444;">
                                {{ $comprovante->nome_original }}
                                @if($comprovante->pagamentoForma && $comprovante->pagamentoForma->condicaoPagamento)
                                    — {{ $comprovante->pagamentoForma->condicaoPagamento->nome }}
                                @endif
                            </div>
                            @php
                                $src = '';
                                if (\Illuminate\Support\Facades\Storage::disk('private')->exists($comprovante->path)) {
                                    $mime = $comprovante->mime_type ?? 'image/jpeg';
                                    $data = \Illuminate\Support\Facades\Storage::disk('private')->get($comprovante->path);
                                    $src = 'data:' . $mime . ';base64,' . base64_encode($data);
                                }
                            @endphp
                            @if($src)
                                <img src="{{ $src }}" style="max-width: 100%; max-height: 700px; border: 1px solid #ccc; border-radius: 4px;" alt="Comprovante">
                            @else
                                <div class="obs-box" style="display: inline-block;">Imagem indisponível ou não encontrada.</div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             SEÇÃO: DEVOLUÇÕES E TROCAS
        ══════════════════════════════════════════════════════════ --}}
        <div class="politica-box">
            <div class="politica-title">DEVOLUÇÕES E TROCAS</div>
            <div class="politica-text">
                <p>Solicitamos que o pedido seja conferido no ato da retirada/entrega (cor, descrição e quantidade), pois devoluções sem defeito poderão não ser aceitas.</p>
                <p>Para realizar devoluções, é necessário informar previamente o vendedor, que irá gerar um romaneio com os itens.</p>
                <p>O documento deve ser conferido pelo motorista, assinado e enviado junto às mercadorias.</p>
                <p>Trocas sem defeito devem ser solicitadas em até 10 (dez) dias corridos da compra, sujeitas à análise.</p>
                <p>Devoluções serão processadas em até 1 dia útil.</p>
                <p>O crédito ficará disponível somente após o processamento, não podendo ser utilizado no ato.</p>
            </div>
        </div>

    </div>{{-- /content --}}

    {{-- ══════════════════════════════════════════════════════════════
         RODAPÉ
    ══════════════════════════════════════════════════════════════ --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    Documento gerado automaticamente pelo sistema em
                    {{ \Carbon\Carbon::now()->format('d/m/Y \à\s H:i:s') }}
                </td>
                <td class="right">
                    Pagamento #{{ str_pad($pagamento->id, 6, '0', STR_PAD_LEFT) }}
                    &nbsp;|&nbsp;
                    {{ $tipoRegistro === 'orcamento' ? 'Orçamento' : 'Pedido' }} #{{ $registro->id }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
