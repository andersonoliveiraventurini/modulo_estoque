<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RNC #{{ $rnc->nr }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-bold: true; margin-bottom: 5px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; background: #eee; padding: 5px; margin-bottom: 10px; border-radius: 4px; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .col { display: table-cell; border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        .label { font-weight: bold; color: #777; font-size: 10px; text-transform: uppercase; }
        .value { font-size: 12px; margin-top: 3px; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #aaa; }
        .signature { margin-top: 60px; text-align: center; border-top: 1px solid #333; width: 250px; margin-left: auto; margin-right: auto; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">RELATÓRIO DE NÃO CONFORMIDADE (RNC)</div>
        <div class="value">NR: <strong>{{ $rnc->nr }}</strong> | Data: {{ $rnc->data_ocorrencia->format('d/m/Y') }}</div>
    </div>

    <div class="section">
        <div class="section-title">DADOS DO PRODUTO E FORNECEDOR</div>
        <div class="grid">
            <div class="col" style="width: 50%;">
                <div class="label">Produto</div>
                <div class="value">{{ $rnc->produto_nome }}</div>
                @if($rnc->produto)
                    <div class="label" style="margin-top: 5px;">SKU</div>
                    <div class="value">{{ $rnc->produto->sku }}</div>
                @endif
            </div>
            <div class="col">
                <div class="label">Fornecedor</div>
                <div class="value">{{ $rnc->fornecedor_nome }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DOCUMENTAÇÃO DE ORIGEM</div>
        <div class="grid">
            <div class="col">
                <div class="label">Nota Fiscal</div>
                <div class="value">{{ $rnc->nota_fiscal ?: 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="label">Romaneio de Recebimento</div>
                <div class="value">{{ $rnc->romaneio_recebimento ?: 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DESCRIÇÃO DA NÃO CONFORMIDADE / OBSERVAÇÕES</div>
        <div style="border: 1px solid #ddd; padding: 10px; min-height: 80px;">
            {{ $rnc->observacoes ?: 'Nenhuma observação informada.' }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">AÇÕES TOMADAS</div>
        <div style="border: 1px solid #ddd; padding: 10px; min-height: 80px;">
            {{ $rnc->acoes_tomadas ?: 'Nenhuma ação registrada ainda.' }}
        </div>
    </div>

    <div style="margin-top: 80px;">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; text-align: center;">
                <div class="signature">Responsável pela Emissão<br><small>{{ $rnc->usuario->name }}</small></div>
            </div>
        </div>
    </div>

    <div class="footer">
        Gerado em {{ now()->format('d/m/Y H:i') }} | WT System ERP
    </div>
</body>
</html>
