<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdutoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('produtos')->where(function ($query) {
                    return $query->where('fornecedor_id', $this->fornecedor_id)
                                 ->where('cor_id', $this->cor_id);
                }),
            ],
            'codigo_barras' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255|unique:produtos,sku',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'part_number' => 'nullable|string|max:255|unique:produtos,part_number',
            'cor_id' => 'nullable|exists:cores,id',
            'unidade_medida' => 'required|string|max:255',
            'peso' => 'nullable|numeric',
            'estoque_minimo' => 'nullable|numeric',
            'flag_encomenda' => 'nullable|boolean',
            'icms' => 'nullable|numeric',
            'pis' => 'nullable|numeric',
            'cofins' => 'nullable|numeric',
            'mva' => 'nullable|numeric',
            'preco_custo' => 'nullable|numeric',
            'preco_base' => 'nullable|numeric',
            'custo_frete_fornecedor' => 'nullable|numeric',
            'custo_operacional' => 'nullable|numeric',
            'margem_lucro' => 'nullable|numeric',
            'preco_venda' => 'nullable|numeric',
            'liberar_desconto' => 'nullable|boolean',
            'valor_desconto' => 'nullable|numeric',
            'porcentagem_desconto' => 'nullable|numeric',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }
}
