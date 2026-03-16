<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Romaneio #{{ $romaneio->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .romaneio-info { margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px; }
        .romaneio-info table { width: 100%; }
        .romaneio-info td { padding: 5px; }
        
        .batch-card { border: 1px solid #ddd; margin-bottom: 20px; page-break-inside: avoid; border-radius: 5px; overflow: hidden; }
        .batch-header { background: #f0f0f0; padding: 10px; font-weight: bold; border-bottom: 1px solid #ddd; }
        .batch-body { padding: 10px; }
        
        .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        .item-table th { background: #f8f8f8; text-align: left; padding: 5px; border-bottom: 1px solid #eee; }
        .item-table td { padding: 5px; border-bottom: 1px solid #eee; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; }
        .page-break { page-break-after: always; }
        
        .label { color: #666; font-size: 10px; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-weight: bold; }
        
        .address-box { margin-top: 10px; padding: 10px; background: #fffbe6; border: 1px solid #ffe58f; border-radius: 3px; }
        .volumes-box { margin-top: 10px; display: inline-block; background: #e6f7ff; border: 1px solid #91d5ff; padding: 5px 15px; border-radius: 3px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ROMANEIO DE ENTREGA #{{ $romaneio->id }}</h1>
        <p>{{ $romaneio->descricao }}</p>
    </div>

    <div class="romaneio-info">
        <table>
            <tr>
                <td><div class="label">Motorista</div><div class="value">{{ $romaneio->motorista ?: '—' }}</div></td>
                <td><div class="label">Veículo</div><div class="value">{{ $romaneio->veiculo ?: '—' }}</div></td>
                <td><div class="label">Data Entrega</div><div class="value">{{ $romaneio->data_entrega?->format('d/m/Y') }}</div></td>
                <td><div class="label">Total Pedidos</div><div class="value">{{ $romaneio->batches->count() }}</div></td>
            </tr>
        </table>
    </div>

    @foreach($romaneio->batches as $index => $batch)
        <div class="batch-card">
            <div class="batch-header">
                ORDEM {{ $index + 1 }} - Lote #{{ $batch->id }} (Orç. #{{ $batch->orcamento_id }})
            </div>
            <div class="batch-body">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 60%;">
                            <div class="label">Cliente</div>
                            <div class="value" style="font-size: 14px;">{{ $batch->orcamento?->cliente?->nome }}</div>
                        </td>
                        <td style="width: 40%; text-align: right;">
                            <div class="label">Vendedor</div>
                            <div class="value">{{ $batch->orcamento?->vendedor?->name }}</div>
                        </td>
                    </tr>
                </table>

                <div class="address-box">
                    <div class="label">Endereço de Entrega</div>
                    @php
                        $endereco = $batch->orcamento?->cliente?->enderecos?->where('tipo', 'entrega')->first() 
                                 ?? $batch->orcamento?->cliente?->enderecos?->where('tipo', 'comercial')->first();
                    @endphp
                    <div class="value" style="font-size: 13px;">
                        {{ $endereco ? "{$endereco->logradouro}, {$endereco->numero} - {$endereco->bairro}" : 'ENDEREÇO NÃO LOCALIZADO' }}<br>
                        {{ $endereco ? "{$endereco->cidade}/{$endereco->uf} - CEP: {$endereco->cep}" : '' }}
                    </div>
                    @if($endereco && $endereco->ponto_referencia)
                        <div style="margin-top: 5px; color: #d46b08; font-size: 11px;">
                            <strong>Ref:</strong> {{ $endereco->ponto_referencia }}
                        </div>
                    @endif
                </div>

                <div style="margin-top: 15px;">
                    <div class="label">Volumes do Pedido</div>
                    <div class="volumes-box">
                        {{ $batch->qtd_caixas }} CAIXAS | 
                        {{ $batch->qtd_sacos }} SACOS | 
                        {{ $batch->qtd_sacolas }} SACOLAS
                        @if($batch->outros_embalagem)
                            | {{ $batch->outros_embalagem }}
                        @endif
                    </div>
                </div>

                <table class="item-table">
                    <thead>
                        <tr>
                            <th>PRODUTO</th>
                            <th style="text-align: center;">QTD</th>
                            <th style="text-align: right;">ASSINATURA CLIENTE: ____________________________________</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batch->items as $item)
                            <tr>
                                <td>{{ $item->produto?->nome }} (SKU: {{ $item->produto?->sku }})</td>
                                <td style="text-align: center;">{{ $item->quantidade }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(($index + 1) % 2 == 0 && !$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i:s') }} - Módulo Logística
    </div>
</body>
</html>
