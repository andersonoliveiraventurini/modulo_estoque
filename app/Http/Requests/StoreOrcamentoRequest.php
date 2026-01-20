<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrcamentoRequest extends FormRequest
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
            'cliente_id' => 'required|exists:clientes,id',
            'nome_obra' => 'required|string|max:255',
            'valor_total' => 'nullable|string',
            'observacoes' => 'nullable|string',
            'desconto' => 'nullable|numeric|min:0|max:100',
            'desconto_aprovado' => 'nullable|numeric|min:0|max:100',
            'desconto_especifico' => 'nullable|string',
            'guia_recolhimento' => 'nullable|numeric',
            'itens' => 'nullable|array',
            'itens.*.id' => 'required_with:itens|exists:produtos,id',
            'itens.*.quantidade' => 'required_with:itens|numeric|min:0',
            'itens.*.preco_unitario' => 'required_with:itens|numeric|min:0',
            'vidros' => 'nullable|array',
            'endereco_cep' => 'nullable|string|max:9',
            'endereco_logradouro' => 'nullable|string|max:255',
            'endereco_numero' => 'nullable|string|max:20',
            'tipo_documento' => 'required',
            'tipos_transporte' => 'required|integer|exists:tipos_transportes,id',
            'condicao_pagamento' => 'required|exists:condicoes_pagamento,id',
            'venda_triangular' => 'required|boolean',
            'cnpj_triangular' => 'required_if:venda_triangular,1|nullable|string|max:18',

            'condicao_pagamento' => 'required',
            'outros_meios_pagamento' => 'required_if:condicao_pagamento,20|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'O cliente é obrigatório.',
            'nome_obra.required' => 'O nome da obra é obrigatório.',
            'cliente_id.exists' => 'Cliente selecionado não existe.',
            'condicao_pagamento.required' => 'Por favor, selecione uma condição de pagamento.',
            'condicao_pagamento.exists' => 'A condição de pagamento selecionada é inválida.',
        ];
    }

    protected function prepareForValidation()
    {
        // Função helper para normalizar valores brasileiros
        $normalizarValor = function ($valor) {
            if (empty($valor) || $valor === '0') {
                return null;
            }

            $valor = trim($valor);

            // Se não tem vírgula nem ponto, é um valor inteiro
            if (!str_contains($valor, ',') && !str_contains($valor, '.')) {
                return $valor;
            }

            // Se tem vírgula, é formato brasileiro (1.234,56)
            if (str_contains($valor, ',')) {
                return str_replace(',', '.', str_replace('.', '', $valor));
            }

            // Se tem apenas ponto, verifica se é milhares ou decimal
            $partes = explode('.', $valor);
            if (count($partes) == 2 && strlen($partes[1]) == 3) {
                return str_replace('.', '', $valor);
            }

            return $valor;
        };

        $this->merge([
            'desconto_especifico' => $normalizarValor($this->desconto_especifico),
            'guia_recolhimento' => $normalizarValor($this->guia_recolhimento),
            'valor_total' => $normalizarValor($this->valor_total),
        ]);
    }
}
