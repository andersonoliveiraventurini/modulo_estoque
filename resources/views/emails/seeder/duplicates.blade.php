<!DOCTYPE html>
<html>
<head>
    <title>Registros Ignorados</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-family: sans-serif; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .error { color: #d9534f; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Relatório de Carga de Produtos - Itens Ignorados</h2>
    <p>Os seguintes registros não puderam ser salvos devido a restrições de unicidade:</p>

    <table>
        <thead>
            <tr>
                <th>Linha</th>
                <th>SKU</th>
                <th>Referência</th>
                <th>Nome</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($skippedRecords as $record)
                <tr>
                    <td>{{ $record['line'] }}</td>
                    <td>{{ $record['sku'] }}</td>
                    <td>{{ $record['referencia'] }}</td>
                    <td>{{ $record['nome'] }}</td>
                    <td class="error">{{ $record['reason'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><small>Este é um e-mail automático gerado pelo sistema durante a execução do seeder.</small></p>
</body>
</html>
