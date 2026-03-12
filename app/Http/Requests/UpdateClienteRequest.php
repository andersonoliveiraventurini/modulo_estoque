<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clienteId = $this->route('cliente')?->id;

        return [
            // Pessoa Jurídica
            'cnpj'          => ['nullable', 'string', 'max:18', Rule::unique('clientes', 'cnpj')->ignore($clienteId)->whereNull('deleted_at')],
            'razao_social'  => ['nullable', 'string', 'max:255'],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'tratamento'    => ['nullable', 'string', 'max:100'],

            // Pessoa Física
            'cpf'            => ['nullable', 'string', 'max:14', Rule::unique('clientes', 'cpf')->ignore($clienteId)->whereNull('deleted_at')],
            'nome'           => ['nullable', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],

            // Documentos
            'certidoes_negativas' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'delete_documents'    => ['nullable', 'array'],
            'delete_documents.*'  => ['integer', 'exists:documentos,id'],

            // Vendedores
            'vendedor_id'             => ['nullable', 'exists:vendedores,id'],
            'vendedor_externo_id'     => ['nullable', 'exists:vendedores,id'],
            'vendedor_assistente_id'  => ['nullable', 'exists:vendedores,id'],

            // Contatos
            'contatos'           => ['nullable', 'array'],
            'contatos.*.nome'    => ['nullable', 'string', 'max:255'],
            'contatos.*.telefone' => ['nullable', 'string', 'max:20'],
            'contatos.*.email'   => ['nullable', 'email', 'max:255'],

            // Endereço Comercial
            'endereco_cep'        => ['nullable', 'string', 'max:9'],
            'endereco_logradouro' => ['nullable', 'string', 'max:255'],
            'endereco_numero'     => ['nullable', 'string', 'max:50'],
            'endereco_compl'      => ['nullable', 'string', 'max:100'],
            'endereco_bairro'     => ['nullable', 'string', 'max:100'],
            'endereco_cidade'     => ['nullable', 'string', 'max:100'],
            'endereco_estado'     => ['nullable', 'string', 'max:2'],

            // Endereço de Entrega
            'entrega_cep'        => ['nullable', 'string', 'max:9'],
            'entrega_logradouro' => ['nullable', 'string', 'max:255'],
            'entrega_numero'     => ['nullable', 'string', 'max:50'],
            'entrega_compl'      => ['nullable', 'string', 'max:100'],
            'entrega_bairro'     => ['nullable', 'string', 'max:100'],
            'entrega_cidade'     => ['nullable', 'string', 'max:100'],
            'entrega_estado'     => ['nullable', 'string', 'max:2'],
        ];
    }
}