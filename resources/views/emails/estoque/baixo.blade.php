<x-mail::message>
# Alerta de Estoque Crítico

O produto abaixo atingiu ou ultrapassou o nível de estoque mínimo definido no sistema.

**Produto:** {{ $produto->nome }}
**SKU:** {{ $produto->sku }}
**Estoque Atual:** {{ $produto->estoque_atual }} {{ $produto->unidade_medida }}
**Estoque Mínimo:** {{ $produto->estoque_minimo }} {{ $produto->unidade_medida }}

Uma requisição de compra automática pode ter sido gerada caso não houvesse uma pendente. Por favor, verifique o painel administrativo para tomar as providências necessárias.

<x-mail::button :url="route('produtos.show', $produto->id)">
Ver Produto no Sistema
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
