<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Vendas e Estoque Sugerido</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        th { background-color: #f5f5f5; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .header { margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .info { margin-bottom: 10px; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Vendas e Estoque Sugerido</h1>
        <p>Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($fim)->format('d/m/Y') }} ({{ number_format($numMeses, 1, ',', '.') }} meses)
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>SKU</th>
                <th class="text-center">Total Vendido</th>
                <th class="text-center">Média Mensal</th>
                <th class="text-center">Estoque Sugerido</th>
                <th class="text-center">Estoque Atual</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produtos as $p)
                <tr>
                    <td>{{ $p->nome }}</td>
                    <td>{{ $p->sku }}</td>
                    <td class="text-center">{{ number_format($p->total_vendido, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($p->qtd_por_mes, 2, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($p->estoque_minimo_sugerido, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($p->estoque_atual, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Página 1 de 1
    </div>
</body>
</html>
