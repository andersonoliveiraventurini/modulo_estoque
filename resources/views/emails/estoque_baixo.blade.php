@component('mail::message')
# Alerta de Estoque Baixo

O produto **{{ $produto->nome }}** (SKU: {{ $produto->sku }}) atingiu ou está abaixo do estoque mínimo.

**Estoque Atual:** {{ $produto->estoque_atual }} {{ $produto->unidade_medida }}
**Estoque Mínimo:** {{ $produto->estoque_minimo }} {{ $produto->unidade_medida }}

@component('mail::button', ['url' => route('produtos.show', $produto->id)])
Ver Produto
@endcomponent

@component('mail::button', ['url' => route('pedido_compras.create')])
Gerar Pedido de Compra
@endcomponent

Atenciosamente,<br>
{{ config('app.name') }}
@endcomponent
