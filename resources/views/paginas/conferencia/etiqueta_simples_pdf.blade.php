<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta Simples</title>
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
            margin-bottom: 5px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }
        .content {
            font-size: 12px;
            line-height: 1.4;
        }
        .field {
            margin-bottom: 4px;
        }
        .label {
            font-weight: bold;
            text-transform: uppercase;
        }
        .value {
            font-size: 14px;
        }
        .volumes {
            margin-top: 10px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            border: 2px solid #000;
            padding: 5px;
        }
    </style>
</head>
<body>
    @foreach($etiquetas as $etq)
    <div class="etiqueta">
        <div class="header">
            <h1>Pedido #{{ $etq['orcamento_id'] }}</h1>
        </div>
        
        <div class="content">
            <div class="field"><span class="label">Cliente:</span> <span class="value">{{ $etq['cliente_nome'] }}</span></div>
            <div class="field"><span class="label">Endereço:</span> <span class="value">{{ $etq['endereco'] }}</span></div>
            <div class="field"><span class="label">Obra:</span> <span class="value">{{ $etq['obra'] }}</span></div>
            <div class="field"><span class="label">Vendedor:</span> <span class="value">{{ $etq['vendedor'] }}</span></div>
            <div class="field"><span class="label">Data:</span> <span class="value">{{ $etq['data'] }}</span></div>
            <div class="field"><span class="label">Dados da Entrega:</span> <span class="value">{{ $etq['dados_entrega'] }}</span></div>
            
            <div class="volumes">
                VOLUME {{ $etq['current'] }} / {{ $etq['total'] }}
            </div>
        </div>
    </div>
    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
    @endforeach
</body>
</html>
