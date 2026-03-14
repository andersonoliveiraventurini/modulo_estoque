<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovimentacaoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_entrada' => 'required|in:entrada,saida',
            'data_movimentacao' => 'nullable|date',
            'pedido_id' => 'nullable|exists:pedidos,id',
            'pedido_compra_id' => 'nullable|exists:pedido_compras,id',
            'nota_fiscal_fornecedor' => 'nullable|string',
            'arquivo_nota_fiscal' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'romaneiro' => 'nullable|string',
            'observacao' => 'nullable|string',
            'produtos' => 'required|array|min:1',
            'produtos.*.fornecedor_id' => 'nullable|exists:fornecedores,id',
            'produtos.*.produto_id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'produtos.*.valor' => 'nullable|numeric|min:0',
            'produtos.*.valor_total' => 'nullable|numeric|min:0',
            'produtos.*.armazem' => 'nullable|string',
            'produtos.*.corredor' => 'nullable|string',
            'produtos.*.posicao' => 'nullable|string',
            'produtos.*.observacao' => 'nullable|string|max:1000',
            'produtos.*.nome' => 'nullable|string',
            'produtos.*.cor' => 'nullable|string',
            'produtos.*.fornecedor_nome' => 'nullable|string',
        ];
    }
}
