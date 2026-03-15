<x-mail::message>
# Requisição de Compra Aprovada

A Requisição de Compra **#{{ $requisicao->id }}** foi totalmente aprovada e já está disponível para a geração do Pedido de Compra formal.

**Solicitante:** {{ $requisicao->solicitante->name }}
**Valor Estimado:** R$ {{ number_format($requisicao->valor_estimado, 2, ',', '.') }}
**Data da Aprovação:** {{ $requisicao->aprovado_em->format('d/m/Y H:i') }}

<x-mail::button :url="route('requisicao_compras.show', $requisicao->id)">
Ver Requisição no Sistema
</x-mail::button>

O próximo passo é acessar o sistema e clicar em **"Gerar Pedido de Compra"** para iniciar o processo de aquisição com o fornecedor.

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
