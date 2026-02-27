<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultaPrecoRequest extends FormRequest
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
            'cliente_id'               => 'required|exists:clientes,id',
            'observacao_geral'         => 'nullable|string|max:1000',
            'itens'                    => 'required|array|min:1',
            'itens.*.descricao'        => 'required|string|max:255',
            'itens.*.quantidade'       => 'required|integer|min:1',
            'itens.*.cor_id'           => 'nullable|exists:cores,id',
            'itens.*.part_number'      => 'nullable|string|max:100',
            'itens.*.observacao'       => 'nullable|string|max:500',
            'itens.*.fornecedor_ids'   => 'nullable|array',
            'itens.*.fornecedor_ids.*' => 'nullable|exists:fornecedores,id', // ← era required, agora nullable
        ];
    }
}
