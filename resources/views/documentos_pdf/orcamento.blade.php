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
    </style>
</head>

<body>
    <!-- ===========================
         CABEÇALHO
    =========================== -->
    <table class="header">
        <tr>
            <td width="20%">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-width: 70px;">
            </td>
            <td width="50%">
                <h2>{{ config('app.name', 'Minha Empresa LTDA') }}</h2>
                <p>Rua Exemplo, 123 - Centro - Campinas/SP</p>
                <p>(11) 99999-9999 - contato@minhaempresa.com.br</p>
            </td>
            <td width="30%" style="text-align:right;">
                <div style="position: fixed; bottom: 40px; right: 40px; text-align: center;">
                    <img src="data:image/png;base64,{{ $qrCode }}" width="110" height="110">
                    <p style="font-size: 9px;">Link válido por 2 dias</p>
                </div>  
                <p><strong>Orçamento nº:</strong> {{ $orcamento->id }}</p>
                <p><strong>Data:</strong> {{ $orcamento->created_at->format('d/m/Y') }}</p>
                <p><strong>Validade:</strong> {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</p>
                @php
                    $usuario = \App\Models\User::find($orcamento->vendedor_id);
                @endphp
                <p><strong>Vendedor:</strong> {{ $usuario->name }}</p>
            </td>
        </tr>
    </table>

    <!-- ===========================
         DADOS DO CLIENTE
    =========================== -->
    <h3>Dados do Cliente</h3>
    <table class="cliente-info">
        <tr>
            <td class="label">Nome:</td>
            <td class="value">{{ $orcamento->cliente->nome ?? '---' }}</td>
            <td class="label">Obra:</td>
            <td class="value">{{ $orcamento->obra ?? '---' }}</td>
        </tr>
        <tr>
            <td class="label">E-mail:</td>
            <td class="value">{{ $orcamento->cliente->email ?? '---' }}</td>
            <td class="label">Telefone:</td>
            <td class="value">{{ $orcamento->cliente->telefone ?? '---' }}</td>
        </tr>
        <tr>
            <td class="label">Endereço:</td>
            <td class="value" colspan="3">{{ $orcamento->cliente->endereco ?? '---' }}</td>
        </tr>
    </table>

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
                    <tr>
                        <td align="center">{{ $item->quantidade }}</td>
                        <td>{{ $item->produto->nome ?? '---' }}</td>
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
    @if ($orcamento->vidros->count() > 0)
        <h4>Vidros e Esteiras</h4>
        <table>
            <thead>
                <tr>
                    <th>Qtd</th>
                    <th>Descrição</th>
                    <th style="width: 3rem">Altura (mm)</th>
                    <th style="width: 3rem">Largura (mm)</th>
                    <th style="width: 5rem">Preço m²</th>
                    <th style="width: 5rem">Desc.</th>
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
                        <td class="valor">R$
                            {{ number_format($vidro->preco_metro_quadrado * ($percentualAplicado / 100), 2, ',', '.') }}
                        </td>
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
        $totalProdutos = $orcamento->itens->sum(fn($item) => $item->quantidade * $item->valor_unitario);
        $totalItensComDesconto = $orcamento->itens->sum('valor_com_desconto');
        $totalVidros = $orcamento->vidros->sum(fn($v) => $v->valor_com_desconto);
        $totalComDescontos = $totalItensComDesconto + $totalVidros;
        $descontosPercentuais = $orcamento->descontos->where('tipo', 'percentual');
        $descontosFixos = $orcamento->descontos->where('tipo', 'fixo');
        $valorDescontosFixos = $descontosFixos->sum('valor');
        $percentualAplicado = $descontosPercentuais->max('porcentagem') ?? 0;
        $valorFinal = $totalComDescontos - $valorDescontosFixos;
    @endphp

    <h3>Totais e Descontos</h3>
    <table class="totais">
        @if ($orcamento->itens->count() > 0)
            <tr>
                <td>Valor Total em Produtos</td>
                <td class="valor">R$ {{ number_format($totalItensComDesconto, 2, ',', '.') }}</td>
            </tr>
        @endif
        @if ($orcamento->vidros->count() > 0)
            <tr>
                <td>Valor Total em Vidros</td>
                <td class="valor">R$ {{ number_format($totalVidros, 2, ',', '.') }}</td>
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

        @if ($orcamento->frete > 0)
            <tr>
                <td>Frete</td>
                <td class="valor">R$ {{ number_format($orcamento->frete, 2, ',', '.') }}</td>
            </tr>
        @endif

        <tr>
            <td>Valor Final do Orçamento</td>
            <td class="valor">R$ {{ number_format($valorFinal + $orcamento->frete, 2, ',', '.') }}</td>
        </tr>
    </table>

    <!-- ===========================
         RODAPÉ
    =========================== -->
    <div class="footer"
        style="position: fixed; bottom: 20px;left: 0; right: 0; text-align: center;font-size: 11px;color: #666;">
        <p>Este orçamento é válido até {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}. ©
            {{ date('Y') }} {{ config('app.name') }} - Todos os direitos reservados.</p>
    </div>

</body>

</html>
