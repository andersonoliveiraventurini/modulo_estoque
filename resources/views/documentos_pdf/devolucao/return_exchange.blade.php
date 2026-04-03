<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Romaneio de Entrega para Troca #{{ $return->nr }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; background: #dbeafe; padding: 5px; border-left: 4px solid #1e40af; margin-bottom: 10px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f9f9f9; }
        .signature-grid { margin-top: 50px; width: 100%; }
        .signature-line { border-top: 1px solid #333; width: 80%; margin: 20px auto 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="color: #1e40af;">ROMANEIO DE ENTREGA PARA TROCA</h1>
        <p>Número: <strong>{{ $return->nr }}</strong> | Data: {{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <div class="section-title">DADOS DO CLIENTE</div>
        <p><strong>Nome:</strong> {{ $return->cliente->nome }}</p>
        <p><strong>Documento:</strong> {{ $return->cliente->cnpj ?? $return->cliente->cpf }}</p>
        <p><strong>Ref. Devolução:</strong> {{ $return->nr }} | <strong>Orçamento Original:</strong> #{{ $return->orcamento_id }}</p>
    </div>

    <div class="section">
        <div class="section-title">ITENS ENVIADOS PARA SUBSTITUIÇÃO</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Cód. Produto</th>
                    <th>Descrição</th>
                    <th style="text-align: center;">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                @foreach($return->items as $item)
                <tr>
                    <td>{{ $item->produto->sku }}</td>
                    <td>{{ $item->produto->nome }}</td>
                    <td style="text-align: center;">{{ number_format($item->quantidade, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section" style="margin-top: 30px;">
        <p><strong>Observações da Logística:</strong></p>
        <div style="border: 1px solid #ddd; padding: 10px; min-height: 60px; border-radius: 5px;">
            {{ $return->observacoes_estoque ?: 'Material conferido e liberado para troca.' }}
        </div>
    </div>

    <table class="signature-grid">
        <tr>
            <td style="width: 50%; text-align: center;">
                <div class="signature-line"></div>
                <p>Assinatura do Motorista</p>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="signature-line"></div>
                <p>Recebido por (Nome Legível)</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
