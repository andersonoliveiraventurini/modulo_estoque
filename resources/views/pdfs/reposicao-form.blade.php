<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Formulário de Reposição #{{ $ordem->id }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #4f46e5; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; color: #666; font-weight: bold; }
        
        .section { margin-bottom: 25px; }
        .section-title { font-weight: bold; background: #f3f4f6; padding: 5px 10px; border-left: 4px solid #4f46e5; margin-bottom: 10px; text-transform: uppercase; font-size: 10px; color: #374151; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th, table td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        table th { background: #f9fafb; font-weight: bold; color: #374151; width: 30%; }
        
        .footer { margin-top: 50px; text-align: center; }
        .signature-box { margin-top: 60px; border-top: 1px solid #000; width: 300px; margin-left: auto; margin-right: auto; padding-top: 10px; }
        .signature-label { font-size: 10px; text-transform: uppercase; font-weight: bold; }
        
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .badge-pendente { background: #fef3c7; color: #92400e; }
        
        .info-grid { display: table; width: 100%; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Formulário de Reposição</h1>
        <p>Ordem #{{ str_pad($ordem->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="section">
        <div class="section-title">Dados da Solicitação</div>
        <div class="info-grid">
            <div class="info-col">
                <table>
                    <tr><th>Data/Hora solicitação</th><td>{{ $ordem->created_at->format('d/m/Y H:i') }}</td></tr>
                    <tr><th>Solicitado por</th><td>{{ $ordem->solicitadoPor->name ?? 'N/A' }}</td></tr>
                    <tr><th>Status Atual</th><td><span class="badge">{{ ucfirst($ordem->status) }}</span></td></tr>
                </table>
            </div>
            <div class="info-col" style="padding-left: 20px;">
                <table>
                    <tr><th>Executor Designado</th><td>{{ $ordem->executor->name ?? 'A definir' }}</td></tr>
                    <tr><th>Data Impressão</th><td>{{ now()->format('d/m/Y H:i') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Produto e Quantidade</div>
        <table>
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="width: 15%;">SKU</th>
                    <th style="width: 65%;">Descrição do Produto</th>
                    <th style="width: 20%; text-align: center;">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $ordem->produto->sku }}</td>
                    <td><strong>{{ $ordem->produto->nome }}</strong></td>
                    <td style="text-align: center; font-size: 16px; font-weight: bold;">{{ number_format($ordem->quantidade_solicitada, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Endereço de Origem (Retirada)</div>
        <p style="font-size: 14px; margin-left: 10px;">
            @if($ordem->armazemOrigem)
                <strong>Armazém:</strong> {{ $ordem->armazemOrigem->nome }} <br>
                @if($ordem->corredorOrigem) <strong>Corredor:</strong> {{ $ordem->corredorOrigem->nome }} @endif
                @if($ordem->posicaoOrigem) | <strong>Posição:</strong> {{ $ordem->posicaoOrigem->nome }} @endif
            @else
                <i style="color: #999;">Endereço de origem não especificado na ordem. Consulte o sistema.</i>
            @endif
        </p>
    </div>

    <div class="section">
        <div class="section-title">Destino Final</div>
        <p style="font-size: 14px; margin-left: 10px;">
            <strong>HUB Central (Armazém ID: 1)</strong>
        </p>
    </div>

    <div class="footer">
        <div class="signature-box">
            <div class="signature-label">Assinatura do Repositor / Executor</div>
            <div style="margin-top: 5px; font-weight: bold;">{{ $ordem->executor->name ?? '________________________________' }}</div>
        </div>
        <p style="margin-top: 40px; font-size: 9px; color: #999;">
            Documento gerado pelo Sistema de Gestão ERP - Módulo Estoque/HUB
        </p>
    </div>

</body>
</html>
