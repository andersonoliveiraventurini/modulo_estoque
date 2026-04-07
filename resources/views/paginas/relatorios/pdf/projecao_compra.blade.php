<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Projeção de Compra</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #4f46e5; }
        .info { margin-bottom: 15px; background: #f9fafb; padding: 10px; border-radius: 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; font-size: 8px; text-align: center; color: #999; }
        .critical { color: #dc2626; font-weight: bold; }
        .suggested { background-color: #eef2ff; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeção de Compra - Inteligência de Estoque</h1>
        <p>Gerado em: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="info">
        <strong>Data do Pedido:</strong> {{ \Carbon\Carbon::parse($dataPedido)->format('d/m/Y') }}<br>
        <strong>Previsão Recebimento:</strong> {{ \Carbon\Carbon::parse($previsaoRecebimento)->format('d/m/Y') }}<br>
        <strong>Meses de Compra:</strong> {{ $mesesCompra }} meses<br>
        <strong>Configurações:</strong> 
        Abater Estoque: {{ $abaterEstoque ? 'Sim' : 'Não' }} | 
        Abater Consumo Previsto: {{ $abaterConsumo ? 'Sim' : 'Não' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto / SKU</th>
                <th class="text-center">Consumo Mensal</th>
                <th class="text-center">Estoque Atual</th>
                <th class="text-center">Consumo Previsto</th>
                <th class="text-center suggested">Sugestão Compra</th>
                <th class="text-right">Preço Custo</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeral = 0; @endphp
            @foreach ($produtos->where('quantidade_sugerida', '>', 0) as $p)
                @php $subtotal = $p->quantidade_sugerida * $p->preco_custo; $totalGeral += $subtotal; @endphp
                <tr>
                    <td>
                        {{ $p->nome }}<br>
                        <small style="color:#666">SKU: {{ $p->sku }}</small>
                        @if($p->abaixo_minimo) <span class="critical">[CRÍTICO]</span> @endif
                    </td>
                    <td class="text-center">{{ number_format($p->consumo_mensal, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($p->estoque_atual, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($p->detalhe_consumo_previsao, 2, ',', '.') }}</td>
                    <td class="text-center suggested">{{ number_format($p->quantidade_sugerida, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($p->preco_custo, 2, ',', '.') }}</td>
                    <td class="text-right">R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right font-bold">VALOR TOTAL ESTIMADO:</td>
                <td class="text-right font-bold" style="font-size: 12px; color: #4f46e5;">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Este documento é uma projeção baseada em histórico de vendas e pode sofrer variações conforme demanda de mercado.
    </div>
</body>
</html>
