<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h2,
        h3,
        h4 {
            margin: 4px 0;
            text-align: center;
            font-weight: 600;
        }

        /* ===========================
           CABEÇALHO
        =========================== */
        .header {
            width: 100%;
            border-bottom: 1.5px solid #000;
            margin-bottom: 15px;
        }

        .header td {
            vertical-align: top;
            font-size: 10.5px;
        }

        .header h2 {
            margin: 0;
            font-size: 13px;
        }

        .header p {
            margin: 1px 0;
            line-height: 1.2;
        }

        /* ===========================
           DADOS DO CLIENTE
        =========================== */
        .cliente-info {
            width: 100%;
            border: 1px solid #aaa;
            border-radius: 4px;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .cliente-info td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .cliente-info .label {
            font-weight: bold;
            color: #000;
            width: 10%;
            white-space: nowrap;
        }

        .cliente-info .value {
            color: #444;
            word-break: break-word;
        }

        .cliente-info tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* ===========================
           TABELAS DE ITENS E VIDROS
        =========================== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 10.5px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 5px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        td.valor {
            text-align: right;
        }

        table th:nth-child(1) {
            width: 6%;
        }

        table th:nth-child(2) {
            width: 40%;
        }

        table th:nth-child(3) {
            width: 14%;
        }

        table th:nth-child(4) {
            width: 10%;
        }

        table th:nth-child(5) {
            width: 15%;
        }

        /* ===========================
           TOTAIS
        =========================== */
        .totais {
            width: 45%;
            float: right;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10.5px;
        }

        .totais td {
            padding: 4px 6px;
            border-bottom: 1px solid #ddd;
        }

        .totais .valor {
            text-align: right;
        }

        .totais tr:last-child {
            background: #f5f5f5;
            font-weight: bold;
        }

        /* ===========================
           RODAPÉ
        =========================== */
        .footer {
            font-size: 9px;
            text-align: center;
            border-top: 1px solid #ccc;
            margin-top: 25px;
            padding-top: 5px;
            color: #666;
            line-height: 1.3;
        }

        /* ===========================
           EVITAR QUEBRAS DE PÁGINA
        =========================== */
        table,
        tr,
        td {
            page-break-inside: avoid;
        }

        .desconto-produto-label {
            font-size: 9px;
            color: #c00;
            display: block;
            margin-top: 2px;
        }

        .badge-revisao {
            font-size: 13px;
            color: #555;
            font-weight: normal;
        }
    </style>
</head>

<body>

    <!-- ===========================
         CABEÇALHO
    =========================== -->
    <table
        style="width:100%; font-family: Arial, sans-serif; font-size:12px; color:#333; border-collapse:collapse; margin-bottom:15px;">
        <tr>
            <td style="width:4rem;">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-width: 70px;">
            </td>
            <td style="padding:5px; border:1px solid #ccc; background-color:#f9f9f9;">
                <div style="text-align:center; margin-bottom:15px;">
                    <p style="margin:2px 0;"><strong>ACAV</strong> - Comércio de Acessórios LTDA<br /><br />
                        R. São Luís do Paraitinga, 1338 - Jardim do Trevo - Campinas - SP - CEP: 13030-105
                        <br /> (19) 3273-3783 (19) 3274-1717
                        <a href="mailto:contato@acavacessorios.com.br">contato@acavacessorios.com.br</a> - <a
                            href="http://www.acavacessorios.com.br" target="_blank">www.acavacessorios.com.br</a>
                    </p>
                </div>
            </td>
            <td style="width:4rem;">
                <img src="data:image/png;base64,{{ $qrCode }}" width="80" height="80">
            </td>
        </tr>
    </table>

    {{-- ===========================
         TÍTULO: versão só aparece se versao > 1
    =========================== --}}
    <h2 style="margin:0; font-size:20px; text-transform:uppercase;">
        ORÇAMENTO n° {{ $orcamento->id }}
        @if (!is_null($orcamento->versao) && $orcamento->versao > 1)
            <span class="badge-revisao">— Revisão {{ $orcamento->versao - 1 }}</span>
        @endif
        @if ($orcamento->complemento === 'Sim')
            <br /><span style="font-size:14px;">Complemento</span>
        @endif
        @if ($orcamento->relationLoaded('transportes') && $orcamento->transportes->count() > 0)
            <br /><span style="font-size:13px;">Transporte: {{ $orcamento->transportes->pluck('nome')->join(', ') }}</span>
        @endif
    </h2>

    <!-- ===========================
         DADOS DO CLIENTE
    =========================== -->
    @php
        $usuario = \App\Models\User::find($orcamento->vendedor_id);
    @endphp

    <table class="cliente-info">
        <tr>
            <td class="label">Cliente:</td>
            <td class="value">{{ $orcamento->cliente->nome ?? '---' }}</td>
            <td class="label">Telefone:</td>
            <td class="value">{{ $orcamento->cliente->telefone ?? '---' }}</td>
        </tr>
        <tr>
            <td class="label"><strong>Data do Orçamento:</strong></td>
            <td class="value">{{ $orcamento->created_at->format('d/m/Y') }}</td>
            <td class="label"><strong>Validade Orçamento:</strong></td>
            <td class="value">
                {{ $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') : '---' }}
            </td>
        </tr>
        <tr>
            <td class="label">Atendido por:</td>
            <td class="value">{{ $usuario->name ?? '---' }}</td>
            <td class="label">Prazo de Entrega:</td>
            <td class="value">{{ $orcamento->prazo_entrega ?? '---' }}</td>
        </tr>
        <tr>
            <td class="label">Condição pagamento:</td>
            <td class="value">
                @if ($orcamento->condicao_id == 20)
                    {{ $orcamento->outros_meios_pagamento }}
                @else 
                    {{ $orcamento->condicaoPagamento->nome }}
                @endif
            </td>
            <td class="label">Tipo de frete:</td>
            <td class="value">{{ $orcamento->frete ?? 'Não registrado' }}</td>
        </tr>
        @if (!is_null($orcamento->guia_recolhimento) && $orcamento->guia_recolhimento > 0)
            <tr>
                <td class="label">Guia de recolhimento:</td>
                <td class="value" colspan="3">
                    R$ {{ number_format($orcamento->guia_recolhimento, 2, ',', '.') }}
                </td>
            </tr>
        @endif
        <tr>
            <td class="label">Obra:</td>
            <td class="value" colspan="3">{{ $orcamento->obra ?? '---' }}</td>
        </tr>
        <tr>
            <td class="label">Endereço:</td>
            <td class="value" colspan="3">{{ $orcamento->cliente->endereco ?? '---' }}</td>
        </tr>
        @if (!is_null($orcamento->observacoes))
            <tr>
                <td class="label">Observações:</td>
                <td class="value" colspan="3">{{ $orcamento->observacoes }}</td>
            </tr>
        @endif
    </table>

    {{-- ===========================
         PRÉ-PROCESSAMENTO DE DESCONTOS
         ✅ Busca direto do banco (whereNull deleted_at) para ignorar soft deletes
    =========================== --}}
    @php
        // Query fresca — ignora qualquer cache em memória e respeita soft delete
        $descontosAtivos = $orcamento->descontos()->whereNull('deleted_at')->get();

        $percentualAplicado = $descontosAtivos->where('tipo', 'percentual')->max('porcentagem') ?? 0;

        // Descontos por produto aprovados — indexados por produto_id para lookup rápido
        $descontosPorProduto = $descontosAtivos
            ->where('tipo', 'produto')
            ->filter(fn($d) => !is_null($d->aprovado_em))
            ->keyBy('produto_id');
    @endphp

    <!-- ===========================
         ITENS DO ORÇAMENTO
    =========================== -->
    @if ($orcamento->itens->count() > 0)
        <h3>Itens do Orçamento</h3>
        <table>
            <thead>
                <tr>
                    <th>Qtd</th>
                    <th>Produto</th>
                    <th>Unitário</th>
                    <th>Unitário com desconto</th>
                    <th>Valor final</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orcamento->itens as $item)
                    @php
                        $descontoProduto = $descontosPorProduto->get($item->produto_id);
                    @endphp
                    <tr>
                        <td align="center">{{ $item->quantidade }}</td>
                        <td>
                            {{ $item->produto->nome ?? '---' }}
                            @if ($descontoProduto)
                                <span class="desconto-produto-label">
                                    ✔ Desconto especial: -R$ {{ number_format($descontoProduto->valor, 2, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td class="valor">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td class="valor">R$ {{ number_format($item->valor_unitario_com_desconto, 2, ',', '.') }}</td>
                        <td class="valor">R$ {{ number_format($item->valor_com_desconto, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- ===========================
         VIDROS E ESTEIRAS
    =========================== -->
    @if ($orcamento->relationLoaded('vidros') && $orcamento->vidros->count() > 0)
        <h4>Vidros e Esteiras</h4>
        <table>
            <thead>
                <tr>
                    <th>Qtd</th>
                    <th>Descrição</th>
                    <th style="width: 3rem">Altura (mm)</th>
                    <th style="width: 3rem">Largura (mm)</th>
                    <th style="width: 5rem">Preço m²</th>
                    @if ($percentualAplicado > 0)
                        <th style="width: 5rem">Unitário com desconto</th>
                    @endif
                    <th style="width: 5rem">Valor final</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orcamento->vidros as $vidro)
                    <tr>
                        <td align="center">{{ $vidro->quantidade }}</td>
                        <td>{{ $vidro->descricao }}</td>
                        <td class="valor">{{ $vidro->altura }}</td>
                        <td class="valor">{{ $vidro->largura }}</td>
                        <td class="valor">R$ {{ number_format($vidro->preco_metro_quadrado, 2, ',', '.') }}</td>
                        @if ($percentualAplicado > 0)
                            <td class="valor">
                                R$ {{ number_format(
                                    $vidro->preco_metro_quadrado - $vidro->preco_metro_quadrado * ($percentualAplicado / 100),
                                    2, ',', '.'
                                ) }}
                            </td>
                        @endif
                        <td class="valor">R$ {{ number_format($vidro->valor_com_desconto, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- ===========================
         TOTAIS E DESCONTOS
    =========================== -->
    @php
        $totalItensComDesconto = $orcamento->itens->sum('valor_com_desconto');
        $totalVidros           = ($orcamento->relationLoaded('vidros'))
                                    ? $orcamento->vidros->sum(fn($v) => $v->valor_com_desconto)
                                    : 0;
        $totalComDescontos     = $totalItensComDesconto + $totalVidros;

        // ✅ Usa a mesma collection já filtrada (sem soft deletes)
        $descontosPercentuais  = $descontosAtivos->where('tipo', 'percentual');
        $descontosFixos        = $descontosAtivos->where('tipo', 'fixo');
        $descontosProdutoAprov = $descontosAtivos
                                    ->where('tipo', 'produto')
                                    ->filter(fn($d) => !is_null($d->aprovado_em));

        $valorDescontosFixos   = $descontosFixos->sum('valor');
        $percentualAplicado    = $descontosPercentuais->max('porcentagem') ?? 0;

        $freteNumerico         = is_numeric($orcamento->frete) ? (float) $orcamento->frete : 0;
        $guiaNumerico          = is_numeric($orcamento->guia_recolhimento) ? (float) $orcamento->guia_recolhimento : 0;

        $valorFinal            = $totalComDescontos - $valorDescontosFixos + $freteNumerico + $guiaNumerico;
    @endphp

    <h3>Totais e Descontos</h3>
    <table class="totais">
        @if ($orcamento->itens->count() > 0)
            <tr>
                <td>Valor Total em Produtos</td>
                <td class="valor">R$ {{ number_format($totalItensComDesconto, 2, ',', '.') }}</td>
            </tr>
        @endif

        @if ($orcamento->relationLoaded('vidros') && $orcamento->vidros->count() > 0)
            <tr>
                <td>Valor Total em Vidros</td>
                <td class="valor">R$ {{ number_format($totalVidros, 2, ',', '.') }}</td>
            </tr>
        @endif

        @if ($guiaNumerico > 0)
            <tr>
                <td>Guia de Recolhimento</td>
                <td class="valor">R$ {{ number_format($guiaNumerico, 2, ',', '.') }}</td>
            </tr>
        @endif

        @if ($freteNumerico > 0)
            <tr>
                <td>Frete</td>
                <td class="valor">R$ {{ number_format($freteNumerico, 2, ',', '.') }}</td>
            </tr>
        @endif

        @if ($percentualAplicado > 0)
            <tr>
                <td>Desconto Percentual</td>
                <td class="valor">{{ number_format($percentualAplicado, 2, ',', '.') }}%</td>
            </tr>
        @endif

        @foreach ($descontosFixos as $desc)
            <tr>
                <td>{{ $desc->motivo }}</td>
                <td class="valor">- R$ {{ number_format($desc->valor, 2, ',', '.') }}</td>
            </tr>
        @endforeach

        @foreach ($descontosProdutoAprov as $desc)
            <tr>
                <td>Desconto especial: {{ $desc->produto->nome ?? ('Produto #' . $desc->produto_id) }}</td>
                <td class="valor">- R$ {{ number_format($desc->valor, 2, ',', '.') }}</td>
            </tr>
        @endforeach

        <tr>
            <td><strong>Valor Final do Orçamento</strong></td>
            <td class="valor"><strong>R$ {{ number_format($valorFinal, 2, ',', '.') }}</strong></td>
        </tr>
    </table>

    <!-- ===========================
         RODAPÉ
    =========================== -->
    <div class="footer"
        style="position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 11px; color: #666;">
        <p>
            Este orçamento é válido até
            {{ $orcamento->validade ? \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') : 'data não definida' }}.
            © {{ date('Y') }} {{ config('app.name') }} - Todos os direitos reservados.
        </p>
    </div>

</body>

</html>