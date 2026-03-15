<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiquetas de Separação</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .page-break {
            page-break-after: always;
        }
        .etiqueta {
            width: 100%;
            height: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 2px solid #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
            padding-bottom: 5px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
            text-transform: uppercase;
        }
        .content {
            font-size: 14px;
            line-height: 1.5;
        }
        .field-title {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-top: 10px;
            display: block;
        }
        .field-value {
            font-size: 16px;
            display: block;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .volume-badge {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-top: 15px;
            padding: 5px;
            border: 2px solid #000;
            border-radius: 5px;
        }
        .barcode {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>

    @foreach($etiquetas as $index => $etq)
    <div class="etiqueta">
        <div class="header">
            <h1>Pedido #{{ $etq['orcamento_id'] }}</h1>
        </div>
        
        <div class="content">
            <span class="field-title">Cliente</span>
            <span class="field-value"><strong>{{ mb_strimwidth($etq['cliente_nome'], 0, 35, '...') }}</strong></span>
            
            <span class="field-title">Roteiro / Rota</span>
            <span class="field-value">{{ mb_strimwidth($etq['roteiro'], 0, 40, '...') }}</span>
            
            <span class="field-title">Vendedor</span>
            <span class="field-value">{{ $etq['vendedor'] }}</span>

            <div class="volume-badge">
                VOLUME {{ $etq['current'] }} / {{ $etq['total'] }}
            </div>
        </div>

        <div class="footer">
            Gerado em: {{ $etq['data'] }} - Lote: {{ $etq['batch_id'] }}
        </div>
    </div>
    
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
    @endforeach

</body>
</html>
