<!DOCTYPE html>
<html>
<head>
    <title>Inconsistência no Recebimento</title>
</head>
<body style="font-family: sans-serif;">
    <h2 style="color: #e53e3e;">Alerta de Inconsistência no Recebimento</h2>
    <p>O sistema registrou uma entrada de mercadoria com quantidade superior à solicitada no Pedido de Compra.</p>
    
    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <tr style="background-color: #f7fafc;">
            <td><strong>Pedido de Compra:</strong></td>
            <td>#{{ $inconsistencia->pedido_compra_id }}</td>
        </tr>
        <tr>
            <td><strong>Produto:</strong></td>
            <td>{{ $inconsistencia->produto->nome }} ({{ $inconsistencia->produto->sku }})</td>
        </tr>
        <tr style="background-color: #fff5f5;">
            <td><strong>Qtd. Esperada:</strong></td>
            <td>{{ $inconsistencia->quantidade_esperada }}</td>
        </tr>
        <tr style="background-color: #fff5f5;">
            <td><strong>Qtd. Recebida:</strong></td>
            <td>{{ $inconsistencia->quantidade_recebida }}</td>
        </tr>
        <tr>
            <td><strong>Conferente:</strong></td>
            <td>{{ $inconsistencia->usuario->name }}</td>
        </tr>
        <tr>
            <td><strong>Data/Hora:</strong></td>
            <td>{{ $inconsistencia->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
    </table>

    <p style="margin-top: 20px;">
        <a href="{{ route('movimentacao.show', $inconsistencia->movimentacao_id) }}" style="background-color: #4c51bf; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            Ver Movimentação
        </a>
    </p>

    <p style="font-size: 12px; color: #718096; margin-top: 30px;">
        Este é um e-mail automático gerado pelo sistema de gestão de estoque.
    </p>
</body>
</html>
