<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Estoque Mínimo</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-red { color: #d00; }
        .text-green { color: #080; }
        .footer { margin-top: 20px; font-size: 8px; color: #777; text-align: center; }
        .header-info { margin-bottom: 10px; font-size: 11px; }
    </style>
</head>
<body>
    @include('documentos_pdf.partials.cabecalho', [
        'docLabel' => 'Relatório de Estoque Mínimo',
        'docNumero' => $codigo ?? now()->format('YmdHis'),
        'docData' => now()->format('d/m/Y H:i')
    ])

    <div class="header-info">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($fim)->format('d/m/Y') }} ({{ number_format($numMeses, 1, ',', '.') }} meses)
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>SKU</th>
                <th class="text-center">Vendas Total</th>
                <th class="text-center">Média Mensal</th>
                <th class="text-center">Mín. Calc.</th>
                <th class="text-center">Estoque Atual</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produtos as $produto)
                <tr>
                    <td>
                        {{ $produto->nome }}
                        @if($produto->cor)
                            ({{ $produto->cor->nome }})
                        @endif
                    </td>
                    <td>{{ $produto->sku }}</td>
                    <td class="text-center">{{ number_format($produto->total_vendido, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($produto->qtd_por_mes, 2, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($produto->estoque_minimo_calculado, 2, ',', '.') }}</td>
                    <td class="text-center {{ $produto->abaixo_minimo ? 'text-red font-bold' : 'text-green' }}">
                        {{ number_format($produto->estoque_atual, 2, ',', '.') }}
                    </td>
                    <td class="text-center">
                        {{ $produto->abaixo_minimo ? 'ABAIXO' : 'OK' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>