<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Romaneio de Entrega por Roteiro</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: sans-serif; font-size: 11px; color: #1f2937; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #111827; }
        .header p { margin: 5px 0 0; color: #6b7280; font-size: 10px; }
        
        .route-section { margin-bottom: 40px; }
        .route-header { background: #374151; color: white; padding: 10px 15px; font-size: 14px; font-weight: bold; margin-bottom: 15px; border-radius: 4px; }
        
        .client-card { border: 1px solid #d1d5db; border-radius: 6px; padding: 15px; margin-bottom: 20px; page-break-inside: avoid; }
        .client-header { border-bottom: 1px solid #f3f4f6; padding-bottom: 8px; margin-bottom: 10px; display: table; width: 100%; }
        .client-name { font-size: 13px; font-weight: bold; color: #111827; display: table-cell; }
        .batch-id { text-align: right; color: #9ca3af; font-size: 9px; font-family: monospace; display: table-cell; }
        
        .info-grid { display: table; width: 100%; margin-bottom: 10px; }
        .info-col { display: table-cell; vertical-align: top; }
        
        .address-box { background: #f9fafb; padding: 8px 12px; border-radius: 4px; border-left: 3px solid #6366f1; }
        .address-box strong { display: block; font-size: 9px; text-transform: uppercase; color: #6b7280; margin-bottom: 2px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { text-align: left; padding: 6px; font-size: 9px; color: #4b5563; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb; background: #f9fafb; }
        .items-table td { padding: 6px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
        
        .footer-info { margin-top: 10px; border-top: 1px dashed #e5e7eb; padding-top: 8px; }
        .volumes-badge { display: inline-block; background: #ecfdf5; color: #065f46; font-weight: bold; padding: 4px 10px; border-radius: 4px; font-size: 11px; }
        .vendedor-info { float: right; font-size: 9px; color: #9ca3af; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #9ca3af; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Romaneio de Entrega - Logística</h1>
        <p>Documento gerado em: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        // Agrupamos novamente no Blade se necessário ou usamos o que o controller mandou. 
        // Como o PDF pode ser filtrado, vamos garantir o agrupamento aqui também para segurança visual.
        $grouped = $batches->groupBy(function($b) {
            $cliente = $b->orcamento?->cliente;
            $endereco = $cliente?->enderecos->where('tipo', 'entrega')->first() 
                     ?? $cliente?->enderecos->where('tipo', 'comercial')->first();
            return $endereco?->roteiro ?: 'SEM ROTEIRO DEFINIDO';
        });
    @endphp

    @foreach ($grouped as $roteiroNome => $lotes)
        <div class="route-section">
            <div class="route-header uppercase">ROTEIRO: {{ $roteiroNome }}</div>

            @foreach ($lotes as $batch)
                @php
                    $orcamento = $batch->orcamento;
                    $cliente   = $orcamento?->cliente;
                    $endereco  = $cliente?->enderecos->where('tipo', 'entrega')->first() 
                              ?? $cliente?->enderecos->where('tipo', 'comercial')->first();
                    $volumes   = collect([
                        $batch->qtd_caixas   ? $batch->qtd_caixas . ' CAIXAS' : null,
                        $batch->qtd_sacos    ? $batch->qtd_sacos  . ' SACOS'  : null,
                        $batch->qtd_sacolas  ? $batch->qtd_sacolas . ' SACOLAS' : null,
                        $batch->outros_embalagem ? strtoupper($batch->outros_embalagem) : null,
                    ])->filter()->implode(' · ');
                @endphp

                <div class="client-card">
                    <div class="client-header">
                        <div class="client-name">{{ $cliente?->nome ?? 'CONSUMIDOR FINAL' }}</div>
                        <div class="batch-id">LOTE #{{ $batch->id }} | ORÇ. #{{ $orcamento?->id }}</div>
                    </div>

                    <div class="info-grid">
                        <div class="info-col">
                            <div class="address-box">
                                <strong>Endereço de Entrega</strong>
                                @if ($endereco)
                                    {{ $endereco->logradouro }}, {{ $endereco->numero }} — {{ $endereco->bairro }}<br>
                                    {{ $endereco->cidade }}/{{ $endereco->estado }} — CEP: {{ $endereco->cep }}
                                @else
                                    <span style="color: #ef4444;">ENDEREÇO NÃO CADASTRADO</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th width="15%">REF/SKU</th>
                                <th>DESCRIÇÃO DO PRODUTO</th>
                                <th width="10%" style="text-align: center;">QTD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($batch->items as $item)
                                <tr>
                                    <td>{{ $item->produto?->sku ?: '—' }}</td>
                                    <td>{{ $item->produto?->nome ?: 'PRODUTO NÃO IDENTIFICADO' }}</td>
                                    <td style="text-align: center; font-weight: bold;">{{ $item->quantidade_separada }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="footer-info">
                        <div class="volumes-badge">
                            VOLUMES: {{ $volumes ?: 'NENHUM REGISTRO DE VOLUMES' }}
                        </div>
                        <div class="vendedor-info">
                            Vendedor: {{ $orcamento?->vendedor?->name ?? '—' }} | 
                            Separador: {{ $batch->criadoPor?->name ?? '—' }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- Quebra de página entre roteiros --}}
        @if (!$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

    <div class="footer">
        ERP Modulo Estoque - Documento de Apoio à Logística - Página {PAGINA}
    </div>
</body>
</html>
