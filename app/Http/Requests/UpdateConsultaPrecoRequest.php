<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultaPrecoRequest extends FormRequest
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
            'descricao'                          => 'required|string|max:255',
            'quantidade'                         => 'required|integer|min:1',
            'cor_id'                             => 'nullable|exists:cores,id',
            'part_number'                        => 'nullable|string|max:100',
            'observacao'                         => 'nullable|string|max:500',
            'fornecedores'                       => 'nullable|array',
            'fornecedores.*.fornecedor_id'       => 'required|exists:fornecedores,id',
            'fornecedores.*.preco_compra'        => 'nullable|string',
            'fornecedores.*.preco_venda'         => 'nullable|string',
            'fornecedores.*.prazo_entrega'       => 'nullable|string|max:100',
            'fornecedor_selecionado_id'          => 'nullable|exists:fornecedores,id',
        ];

    }
}
