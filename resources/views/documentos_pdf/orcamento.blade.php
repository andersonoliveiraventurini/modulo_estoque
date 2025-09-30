<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h2,
        h3,
        h4 {
            margin: 6px 0;
            text-align: center;
        }

        /* Cabeçalho */
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header td {
            vertical-align: top;
        }

        .header .empresa {
            font-size: 14px;
            font-weight: bold;
        }

        .info-box {
            border: 1px solid #000;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 15px;
        }

        /* Tabelas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        th {
            background: #f2f2f2;
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }

        td {
            border: 1px solid #000;
            padding: 6px;
        }

        td.valor {
            text-align: right;
        }

        /* Totais */
        .totais {
            margin-top: 20px;
            width: 50%;
            float: right;
        }

        .totais td {
            padding: 6px;
        }

        .totais th {
            background: #e6e6e6;
            text-align: left;
            font-size: 12px;
        }

        /* Rodapé */
        .footer {
            margin-top: 40px;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div>
    <table width="100%" style="border-bottom:1px solid #000; padding-bottom:10px; margin-bottom:20px;">
        <tr>
            <!-- Coluna da Logo -->
            <td width="20%" align="left" style="vertical-align: top;">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" style="max-width: 90px;">
            </td>

            <!-- Coluna dos Dados -->
            <td width="55%" align="left" style="vertical-align: top;">
                <div style="flex: 1; text-align: left; font-size: 11px; line-height: 1.2; margin-left: 10px;">
                    <h2 style="margin: 0; font-size: 14px;">{{ config('app.name', 'Minha Empresa LTDA') }}</h2>
                    <p style="margin: 2px 0;"><strong>Endereço:</strong> Rua Exemplo, 123 - Centro - Campinas/SP</p>
                    <p style="margin: 2px 0;"><strong>Telefone:</strong> (11) 99999-9999</p>
                    <p style="margin: 2px 0;"><strong>E-mail:</strong> contato@minhaempresa.com.br</p>
                </div>
            </td>
            <td width="25%" align="right" style="vertical-align: top;">
                <div style="flex: 1; text-align: right; font-size: 11px; line-height: 1.2; margin-left: 10px;">
                    <p style="margin: 2px 0;"><strong>Orçamento nº:</strong> {{ $orcamento->id }}</p><br />
                    <p style="margin: 2px 0;"><strong>Data:</strong> {{ $orcamento->created_at->format('d/m/Y') }}</p>
                    <p style="margin: 2px 0;"><strong>Validade:</strong>
                        {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</p>
                    @php
                        $usuario = \App\Models\User::find($orcamento->vendedor_id); // Substitua 1 pelo ID desejado
                    @endphp
                    <p style="margin: 2px 0;"><strong>Vendedor: </strong>{{ $usuario->name }}
                    </p>

                </div>
            </td>
        </tr>
    </table>

    <!-- Dados do Cliente -->
    <h3>Dados do Cliente</h3>
    <table class="info-box">
        <tr>
            <td><strong>Nome:</strong></td>
            <td>{{ $orcamento->cliente->nome ?? '---' }}</td>
        </tr>
        <tr>
            <td><strong>Obra:</strong></td>
            <td>{{ $orcamento->obra ?? '---' }}</td>
        </tr>
        <tr>
            <td><strong>Endereço:</strong></td>
            <td>{{ $orcamento->cliente->endereco ?? '---' }}</td>
        </tr>
        <tr>
            <td><strong>Telefone:</strong></td>
            <td>{{ $orcamento->cliente->telefone ?? '---' }}</td>
        </tr>
        <tr>
            <td><strong>E-mail:</strong></td>
            <td>{{ $orcamento->cliente->email ?? '---' }}</td>
        </tr>
    </table>

    <!-- Itens do Orçamento -->
    <h3>Itens do Orçamento</h3>
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
                    <td align="center">{{ $item->quantidade }}</td>
                    <td>{{ $item->produto->nome ?? '---' }}</td>
                    <td class="valor">{{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td class="valor">{{ $item->desconto }}%</td>
                    <td class="valor">{{ number_format($item->valor_com_desconto, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Vidros -->
    @if ($orcamento->vidros->count() > 0)
        <h4>Vidros e Esteiras</h4>
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
                        <td align="center">{{ $vidro->quantidade }}</td>
                        <td class="valor">{{ $vidro->altura }}</td>
                        <td class="valor">{{ $vidro->largura }}</td>
                        <td class="valor">{{ number_format($vidro->preco_metro_quadrado, 2, ',', '.') }}</td>
                        <td class="valor">{{ $vidro->desconto }} %</td>
                        <td class="valor">{{ number_format($vidro->valor_com_desconto, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Totais -->
    <!-- Totais -->
    @php
        $totalProdutos = $orcamento->itens->sum(fn($item) => $item->quantidade * $item->valor_unitario);
        $totalItensComDesconto = $orcamento->itens->sum('valor_com_desconto');
        $totalVidros = $orcamento->vidros->sum(fn($v) => $v->valor_com_desconto);
        $totalComDescontos = $totalItensComDesconto + $totalVidros;

        // Total de descontos (percentuais + fixos)
        $descontosPercentuais = $orcamento->descontos->where('tipo', 'percentual');
        $descontosFixos = $orcamento->descontos->where('tipo', 'fixo');

        $valorDescontosFixos = $descontosFixos->sum('valor');
        $percentualAplicado = $descontosPercentuais->max('porcentagem') ?? 0;

        // Aplica desconto fixo no total
        $valorFinal = $totalComDescontos - $valorDescontosFixos;
    @endphp

    <h3 style="margin-top:30px;">Totais e Descontos</h3>

    <table style="width: 50%; float: right; border-collapse: collapse; font-size: 11px;">
        <tr style="background: #f2f2f2;">
            <th style="border: 1px solid #000; padding: 5px; text-align: left;">Descrição</th>
            <th style="border: 1px solid #000; padding: 5px; text-align: right;">Valor</th>
        </tr>
        <tr>
            <td style="border: 1px solid #000; padding: 5px;">Valor Total em Produtos</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: right;">
                R$ {{ number_format($totalProdutos, 2, ',', '.') }}
            </td>
        </tr>

        @if ($percentualAplicado > 0)
            <tr>
                <td style="border: 1px solid #000; padding: 5px;">Desconto Percentual</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: right;">
                    {{ number_format($percentualAplicado, 2, ',', '.') }} %
                </td>
            </tr>
        @endif

        @foreach ($descontosFixos as $desc)
            <tr>
                <td style="border: 1px solid #000; padding: 5px;">
                    {{ $desc->motivo }}
                </td>
                <td style="border: 1px solid #000; padding: 5px; text-align: right;">
                    - R$ {{ number_format($desc->valor, 2, ',', '.') }}
                </td>
            </tr>
        @endforeach

        <tr style="background: #e6e6e6; font-weight: bold;">
            <td style="border: 1px solid #000; padding: 5px;">Valor Final do Orçamento</td>
            <td style="border: 1px solid #000; padding: 5px; text-align: right;">
                R$ {{ number_format($valorFinal, 2, ',', '.') }}
            </td>
        </tr>
    </table>
</div>

    <!-- Rodapé -->
    <div class="footer">
        <p>Este orçamento é válido até {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}.</p>
        <p>Condições de pagamento e prazo de entrega podem variar conforme negociação.</p>
        <p>© {{ date('Y') }} {{ config('app.name') }} - Todos os direitos reservados.</p>
    </div>

</body>

</html>
