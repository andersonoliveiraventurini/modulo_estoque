<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        .header { text-align: center; margin-bottom: 20px; }
        .totais { margin-top: 20px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Orçamento #{{ $orcamento->id }}</h2>
        <p>Data: {{ $orcamento->created_at->format('d/m/Y') }}</p>
        <p>Validade: {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}</p>
    </div>

    <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? '---' }}</p>
    <p><strong>Telefone:</strong> {{ $orcamento->cliente->telefone ?? '---' }}</p>
    <p><strong>Vendedor:</strong> {{ $orcamento->vendedor->name ?? '---' }}</p>
    <p><strong>Obra:</strong> {{ $orcamento->obra ?? '---' }}</p>

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
            @foreach($orcamento->itens as $item)
                <tr>
                    <td>{{ $item->quantidade }}</td>
                    <td>{{ $item->produto->nome ?? '---' }}</td>
                    <td>{{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->desconto, 2, ',', '.') }}</td>
                    <td>{{ number_format($item->valor_com_desconto, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totais">
        <p><strong>Total:</strong> R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</p>
    </div>
</body>
</html>
