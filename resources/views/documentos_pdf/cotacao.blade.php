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

        /* CABEÇALHO */
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

        /* DADOS DO CLIENTE */
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
            width: 15%;
            white-space: nowrap;
        }

        .cliente-info .value {
            color: #444;
            word-break: break-word;
        }

        .cliente-info tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* TABELAS */
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

        /* TOTAIS */
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

        /* RODAPÉ */
        .footer {
            font-size: 9px;
            text-align: center;
            border-top: 1px solid #ccc;
            margin-top: 25px;
            padding-top: 5px;
            color: #666;
            line-height: 1.3;
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
        }

        /* EVITAR QUEBRAS DE PÁGINA */
        table,
        tr,
        td {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <!-- CABEÇALHO -->
    <table
        style="width:100%; font-family: Arial, sans-serif; font-size:12px; color:#333; border-collapse:collapse; margin-bottom:15px;">
        <tr>
            <td style="width:4rem;">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-width: 70px;">
            </td>
            <td style="padding:5px; border:1px solid #ccc; background-color:#f9f9f9;">
                <div style="text-align:center; margin-bottom:15px;">
                    <p style="margin:2px 0;">
                        <strong>ACAV</strong> - Comércio de Acessórios LTDA<br /><br />
                        R. São Luís do Paraitinga, 1338 - Jardim do Trevo - Campinas - SP - CEP: 13030-105
                        <br /> (19) 3273-3783 (19) 3274-1717
                        <a href="mailto:contato@acavacessorios.com.br">contato@acavacessorios.com.br</a> -
                        <a href="http://www.acavacessorios.com.br" target="_blank">www.acavacessorios.com.br</a>
                    </p>
                </div>
            </td>
            <td style="width:4rem;">
                <img src="data:image/png;base64,{{ $qrCode }}" width="80" height="80">
            </td>
        </tr>
    </table>

    <h2 style="margin:0; font-size:20px; text-transform:uppercase;">
        COTAÇÃO Nº {{ $cotacao->id }}
        @if ($cotacao->versao > 1)
            - Revisão: {{ $cotacao->versao }}
        @endif
    </h2>

    <!-- DADOS DA COTAÇÃO -->
    <table class="cliente-info">
        <tr>
            <td class="label">Cliente:</td>
            <td class="value">{{ $cotacao->cliente->nome ?? '---' }}</td>
            <td class="label">Telefone:</td>
            <td class="value">{{ $cotacao->cliente->telefone ?? '---' }}</td>
        </tr>
        <tr>
            <td class="label">Data da Cotação:</td>
            <td class="value">{{ $cotacao->created_at->format('d/m/Y') }}</td>
            <td class="label">Validade:</td>
            <td class="value">
                {{ $cotacao->validade ? \Carbon\Carbon::parse($cotacao->validade)->format('d/m/Y') : '---' }}</td>
        </tr>
        <tr>
            <td class="label">Solicitado por:</td>
            <td class="value">{{ $cotacao->usuario->name ?? '---' }}</td>
            <td class="label">Prazo de Entrega:</td>
            <td class="value">{{ $cotacao->prazo_entrega ?? '---' }}</td>
        </tr>
        <tr>
            <td class="label">Fornecedor:</td>
            <td class="value">{{ $cotacao->fornecedor->nome ?? '---' }}</td>
            <td class="label">Status:</td>
            <td class="value">{{ $cotacao->status }}</td>
        </tr>
        @if ($cotacao->cliente && $cotacao->cliente->endereco)
            <tr>
                <td class="label">Endereço:</td>
                <td class="value" colspan="3">{{ $cotacao->cliente->endereco }}</td>
            </tr>
        @endif
        @if ($cotacao->observacao)
            <tr>
                <td class="label">Observações:</td>
                <td class="value" colspan="3">{{ $cotacao->observacao }}</td>
            </tr>
        @endif
    </table>

    <!-- INFORMAÇÕES DA COTAÇÃO -->
    <h3>Detalhes da Cotação</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Item</th>
                <th style="width: 50%;">Descrição</th>
                <th style="width: 15%;">Quantidade</th>
                <th style="width: 15%;">Cor/Classificação</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center">Cotação #{{ $cotacao->id }}</td>
                <td>{{ $cotacao->descricao ?? 'Consulta de preço' }}</td>
                <td class="valor">{{ $cotacao->quantidade ?? '---' }}</td>
                <td align="center">
                    @if ($cotacao->cor)
                        <span
                            style="display:inline-block; width:15px; height:15px; background-color:{{ $cotacao->cor }}; border:1px solid #000; vertical-align:middle;"></span>
                        {{ $cotacao->cor }}
                    @else
                        ---
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- VALORES -->
    <h3>Valores</h3>
    <table class="totais" style="width: 100%; float: none;">
        {{--@if ($cotacao->preco_compra)
            <tr>
                <td>Preço de Compra (Fornecedor)</td>
                <td class="valor">R$ {{ number_format($cotacao->preco_compra, 2, ',', '.') }}</td>
            </tr>
        @endif--}}

        @if ($cotacao->preco_venda)
            <tr>
                <td><strong>Preço de Venda</strong></td>
                <td class="valor"><strong>R$ {{ number_format($cotacao->preco_venda, 2, ',', '.') }}</strong></td>
            </tr>
        @endif

        {{--@if ($cotacao->preco_compra && $cotacao->preco_venda)
            @php
                $margem = (($cotacao->preco_venda - $cotacao->preco_compra) / $cotacao->preco_compra) * 100;
            @endphp
            <tr>
                <td>Margem de Lucro</td>
                <td class="valor">{{ number_format($margem, 2, ',', '.') }}%</td>
            </tr>
        @endif--}}
    </table>

    <!-- RODAPÉ -->
    <div class="footer">
        <p>
            Esta cotação é válida até
            {{ $cotacao->validade ? \Carbon\Carbon::parse($cotacao->validade)->format('d/m/Y') : '---' }}.
            © {{ date('Y') }} {{ config('app.name') }} - Todos os direitos reservados.
        </p>
    </div>

</body>

</html>
