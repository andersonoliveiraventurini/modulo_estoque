<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprovante de Devolução Finalizada #{{ $return->nr }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; background: #eee; padding: 5px; border-left: 4px solid #10b981; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        .table th { background-color: #f9f9f9; }
        .total-box { margin-top: 15px; padding: 10px; border: 2px solid #10b981; background: #f0fdf4; text-align: right; font-size: 14px; font-weight: bold; }
        .auth-box { margin-top: 20px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPROVANTE DE DEVOLUÇÃO FINALIZADA</h1>
        <p>Número: <strong>{{ $return->nr }}</strong> | Data Finalização: {{ $return->finalizado_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="section">
        <div class="section-title">DADOS DO CLIENTE E ORIGEM</div>
        <p><strong>Cliente:</strong> {{ $return->cliente->nome }} ({{ $return->cliente->cnpj ?? $return->cliente->cpf }})</p>
        <p><strong>Orçamento Relacionado:</strong> #{{ $return->orcamento_id }} | <strong>Vendedor:</strong> {{ $return->vendedor->name ?? 'N/A' }}</p>
    </div>

    <div class="section">
        <div class="section-title">ITENS DEVOLVIDOS</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Cód. Produto</th>
                    <th>Descrição</th>
                    <th style="text-align: center;">Qtd</th>
                    <th style="text-align: right;">Vlr. Unit.</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($return->items as $item)
                <tr>
                    <td>{{ $item->produto->sku }}</td>
                    <td>{{ $item->produto->nome }}</td>
                    <td style="text-align: center;">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td style="text-align: right;">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="total-box">
        VALOR TOTAL DO CRÉDITO GERADO: R$ {{ number_format($return->valor_total_credito, 2, ',', '.') }}
    </div>

    <div class="section">
        <div class="section-title">HISTÓRICO DE APROVAÇÕES</div>
        @foreach($return->authorizations as $auth)
            <div class="auth-box">
                <strong>{{ strtoupper($auth->role) }}:</strong> {{ $auth->status === 'aprovado' ? 'AUTORIZADO' : 'NEGADO' }}<br>
                <strong>Responsável:</strong> {{ $auth->user->name }} em {{ $auth->created_at->format('d/m/Y H:i') }}<br>
                @if($auth->observacoes)
                    <strong>Obs:</strong> {{ $auth->observacoes }}
                @endif
            </div>
        @endforeach
    </div>

    <div class="section">
        <p style="font-size: 10px; color: #666;">
            * O crédito acima foi adicionado ao saldo do cliente e poderá ser utilizado como abatimento em compras futuras (Checkout Balcão ou Rota).
        </p>
    </div>

    <div class="footer">
         {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
