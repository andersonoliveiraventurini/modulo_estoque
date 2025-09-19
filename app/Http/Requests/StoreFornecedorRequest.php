<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFornecedorRequest extends FormRequest
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
            // Pessoa Jurídica
            'cnpj' => ['required', 'string', 'max:18'],

            // Certidão (um único arquivo)
            'certidoes_negativas' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048'
            ],

            // Certificações (vários arquivos)
            'certificacoes_qualidade'   => ['nullable', 'array'],
            'certificacoes_qualidade.*' => [
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048'
            ],
        ];
    }
}
