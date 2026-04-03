<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Solicitação de Devolução #{{ $return->nr }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; background: #f4f4f4; padding: 5px; border-left: 4px solid #333; margin-bottom: 10px; }
        .grid { display: block; clear: both; }
        .col { float: left; width: 50%; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f9f9f9; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; border-top: 1px solid #ddd; padding-top: 5px; }
        .qr-code { float: right; }
    </style>
</head>
<body>
    @include('documentos_pdf.partials.cabecalho', [
        'docLabel'  => 'Romaneio de Devolução',
        'docNumero' => (string) $return->nr,
        'docData'   => $return->created_at->format('d/m/Y H:i'),
    ])

    <div class="section">
        <div class="section-title">DADOS DA ORIGEM (ORÇAMENTO)</div>
        <table class="table" style="border: none;">
            <tr>
                <td style="border: none; width: 50%;"><strong>Orçamento Origem:</strong> #{{ $return->orcamento_id }}</td>
                <td style="border: none;"><strong>Vendedor:</strong> {{ $return->vendedor->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Cliente:</strong> {{ $return->cliente->nome }}</td>
                <td style="border: none;"><strong>Documento:</strong> {{ $return->cliente->cnpj ?? $return->cliente->cpf }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">ITENS PARA DEVOLUÇÃO</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descrição do Produto</th>
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

    <div class="section">
        <div class="section-title">INFORMAÇÕES ADICIONAIS</div>
        <table class="table" style="border: none;">
            <tr>
                <td style="border: none;"><strong>Data Ocorrência:</strong> {{ $return->data_ocorrencia->format('d/m/Y') }}</td>
                <td style="border: none;"><strong>Troca de Produto:</strong> {{ $return->troca_produto ? 'SIM' : 'NÃO' }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Nota Fiscal:</strong> {{ $return->nota_fiscal ?? '-' }}</td>
                <td style="border: none;"><strong>Romaneio:</strong> {{ $return->romaneio_recebimento ?? '-' }}</td>
            </tr>
        </table>
        <div style="margin-top: 10px;">
            <strong>Observações:</strong><br>
            {{ $return->observacoes ?: 'Nenhuma observação informada.' }}
        </div>
    </div>

    <div class="section" style="margin-top: 50px;">
        <p style="text-align: center; color: #666; font-style: italic;">
            Este documento é informativo e serve para acompanhamento da mercadoria até a conferência técnica no estoque.
        </p>
    </div>

    <div class="footer">
        {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
