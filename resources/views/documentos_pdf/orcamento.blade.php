<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .totais {
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>

<body>
    <table width="100%" style="border-bottom:1px solid #000; padding-bottom:10px; margin-bottom:20px;">
        <tr>
            <!-- Coluna da Logo -->
            <td width="20%" align="left" style="vertical-align: top;">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-width: 80px;">
            </td>

            <!-- Coluna dos Dados -->
            <td width="55%" align="right" style="vertical-align: top;">
                <div style="flex: 1; text-align: right; font-size: 11px; line-height: 1.2; margin-left: 10px;">
                    <h2 style="margin: 0; font-size: 14px;">{{ config('app.name', 'Minha Empresa LTDA') }}</h2>
                    <p style="margin: 2px 0;"><strong>Endereço:</strong> Rua Exemplo, 123 - Centro - Campinas/SP</p>
                    <p style="margin: 2px 0;"><strong>Telefone:</strong> (11) 99999-9999</p>
                    <p style="margin: 2px 0;"><strong>E-mail:</strong> contato@minhaempresa.com.br</p>
                </div>
            </td>
            <td width="25%" align="right" style="vertical-align: top;">
                <div style="flex: 1; text-align: right; font-size: 11px; line-height: 1.2; margin-left: 10px;">
                    <p style="margin: 2px 0;"><strong>Orçamento Nº:</strong> 00000{{ $orcamento->id }}</p><br />
                    <p style="margin: 2px 0;"><strong>Data:</strong> {{ $orcamento->created_at->format('d/m/Y') }}</p>
                    <p style="margin: 2px 0;"><strong>Validade:</strong>
                        {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <h3 style="text-align: center; margin-top: 10px;">Dados do Cliente</h3>
    <p><strong>Nome:</strong> {{ $orcamento->cliente->nome ?? '---' }}</p>
    <p><strong>Obra:</strong> {{ $orcamento->obra ?? '---' }}</p>
    <p><strong>Endereço:</strong> {{ $orcamento->cliente->endereco ?? '---' }}</p>
    <p><strong>Telefone:</strong> {{ $orcamento->cliente->telefone ?? '---' }}</p>
    <p><strong>E-mail:</strong> {{ $orcamento->cliente->email ?? '---' }}</p>
    <hr />
    <h3 style="text-align: center; margin-top: 10px;">Itens do Orçamento</h3>
    <!-- Tabela de produtos -->
    <table>
        <thead>
            <tr>
                <th>Qtd</th>
                <th>Produto</th>
                <th>Unitário (R$)</th>
                <th>Desconto</th>
                <th>Total (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orcamento->itens as $item)
                <tr>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->produto->nome ?? '---' }}</td>
                    <td>{{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td>{{ $item->desconto }}%</td>
                    <td>{{ number_format($item->valor_com_desconto, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($orcamento->vidros->count() > 0)
        <h4 style="margin-top:20px;">Vidros e Esteiras</h4>
        <table>
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Qtd</th>
                    <th>Altura (mm)</th>
                    <th>Largura (mm)</th>
                    <th>Preço m² (R$)</th>
                    <th>Desconto</th>
                    <th>Total (R$)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orcamento->vidros as $vidro)
                    <tr>
                        <td>{{ $vidro->descricao }}</td>
                        <td>{{ $vidro->quantidade }}</td>
                        <td>{{ $vidro->altura }}</td>
                        <td>{{ $vidro->largura }}</td>
                        <td>{{ number_format($vidro->preco_metro_quadrado, 2, ',', '.') }}</td>
                        <td>{{ $vidro->desconto }} %</td>
                        <td>{{ number_format($vidro->valor_com_desconto, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Totais -->
    @php
        $totalProdutos = $orcamento->itens->sum(fn($item) => $item->quantidade * $item->valor_unitario);
        $totalDescontos = $orcamento->itens->sum('desconto');
        $totalComDesconto = $orcamento->itens->sum('valor_com_desconto');
        $percentualDesconto = $totalProdutos > 0 ? ($totalDescontos / $totalProdutos) * 100 : 0;
    @endphp

    <div class="totais">
        <p><strong>Valor Total em Produtos:</strong> R$ {{ number_format($totalProdutos, 2, ',', '.') }}</p>
        <p><strong>Valor Total de Descontos:</strong> R$ {{ number_format($totalDescontos, 2, ',', '.') }}</p>
        <p><strong>Percentual de Desconto:</strong> {{ number_format($percentualDesconto, 2, ',', '.') }}%</p>
        <p><strong>Total com Desconto:</strong> R$ {{ number_format($totalComDesconto, 2, ',', '.') }}</p>
    </div>
</body>
</html>
