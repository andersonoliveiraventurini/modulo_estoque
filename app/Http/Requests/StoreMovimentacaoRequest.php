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
            'is_reposicao' => 'nullable|boolean',
            'is_devolucao' => 'nullable|boolean',
            'produtos' => 'required|array|min:1',
            'produtos.*.fornecedor_id' => 'nullable|exists:fornecedores,id',
            'produtos.*.produto_id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'produtos.*.quantidade_vendida' => 'nullable|numeric|min:0',
            'produtos.*.wt_code' => 'nullable|string|max:100',
            'produtos.*.cor' => 'nullable|string|max:100',
            'produtos.*.codigo_fornecedor' => 'nullable|string|max:100',
            'produtos.*.is_encomenda' => 'nullable|boolean',
            'produtos.*.numero_pedido' => 'nullable|required_if:produtos.*.is_encomenda,true|string|max:100',
            'produtos.*.vendedor_id' => 'nullable|exists:vendedores,id',
            'produtos.*.valor' => 'nullable|numeric|min:0',
            'produtos.*.valor_total' => 'nullable|numeric|min:0',
            'produtos.*.alocacoes' => 'required|array|min:1',
            'produtos.*.alocacoes.*.posicao_id' => 'required|exists:posicoes,id',
            'produtos.*.alocacoes.*.quantidade' => 'required|numeric|min:0.001',
            'produtos.*.observacao' => 'nullable|string|max:1000',
            'produtos.*.nome' => 'nullable|string',
            'produtos.*.cor' => 'nullable|string',
            'produtos.*.fornecedor_nome' => 'nullable|string',
            'produtos.*.data_vencimento' => [
                'nullable',
                'date',
                'after:today',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $produtoId = $this->input("produtos.{$index}.produto_id");
                    $produto = \App\Models\Produto::find($produtoId);
                    if ($produto && $produto->is_perishable && empty($value)) {
                        $fail("A data de vencimento é obrigatória para o produto perecível: {$produto->nome}.");
                    }
                }
            ],
        ];
    }
}
