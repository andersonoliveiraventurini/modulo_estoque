<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrcamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Dados básicos
            'obra'                        => 'required|string|max:255',
            'observacoes'                 => 'nullable|string',
            'prazo_entrega'               => 'nullable|string|max:100', // CORRIGIDO: era 'nullable|date', quebrava com "15 dias úteis"

            // Valores monetários
            'valor_total'                 => 'nullable|string',
            'frete'                       => 'nullable|string',
            'desconto'                    => 'nullable|numeric|min:0|max:100',
            'desconto_aprovado'           => 'nullable|numeric|min:0|max:100',
            'desconto_especifico'         => 'nullable|string',
            'guia_recolhimento'           => 'nullable|string',

            // Documento e pagamento
            'tipo_documento'              => 'nullable|string',
            'homologacao'                 => 'required|boolean',
            'venda_triangular'            => 'required|boolean',
            'cnpj_triangular'             => 'required_if:venda_triangular,1|nullable|string|max:18',
            'tipos_transporte'            => 'required|integer|exists:tipos_transportes,id',
            'condicao_id'                 => 'required|exists:condicoes_pagamento,id',
            'outros_meios_pagamento'      => 'required_if:condicao_id,20|nullable|string|max:255',

            // Endereço
            'entrega_cep'                 => 'nullable|string|max:9',
            'entrega_logradouro'          => 'nullable|string|max:255',
            'entrega_numero'              => 'nullable|string|max:20',
            'entrega_compl'               => 'nullable|string|max:100',
            'entrega_bairro'              => 'nullable|string|max:100',
            'entrega_cidade'              => 'nullable|string|max:100',
            'entrega_estado'              => 'nullable|string|max:2',

            // Produtos existentes
            'produtos'                          => 'nullable|array',
            'produtos.*.produto_id'             => 'required_with:produtos|exists:produtos,id',
            'produtos.*.quantidade'             => 'required_with:produtos|numeric|min:0',
            'produtos.*.valor_unitario'         => 'required_with:produtos|numeric|min:0',
            'produtos.*._remove'                => 'nullable|boolean',

            // Novos itens (produtos adicionados via JS)
            'itens'                             => 'nullable|array',
            'itens.*.id'                        => 'nullable',
            'itens.*.quantidade'                => 'required_with:itens|numeric|min:0',
            'itens.*.preco_unitario'            => 'nullable|numeric|min:0',

            // Vidros removidos
            'vidros_removidos'                  => 'nullable|array',
            'vidros_removidos.*'                => 'exists:orcamento_vidros,id',

            // Vidros existentes (edição)
            'vidros_existentes'                 => 'nullable|array',
            'vidros_existentes.*.id'            => 'required_with:vidros_existentes|exists:orcamento_vidros,id',
            'vidros_existentes.*.quantidade'    => 'nullable|numeric|min:0',
            'vidros_existentes.*.altura'        => 'nullable|numeric|min:0',
            'vidros_existentes.*.largura'       => 'nullable|numeric|min:0',
            'vidros_existentes.*.preco_m2'      => 'nullable|string',

            // Novos vidros
            'vidros'                            => 'nullable|array',
            'vidros.*.quantidade'               => 'nullable|numeric|min:0',
            'vidros.*.altura'                   => 'nullable|numeric|min:0',
            'vidros.*.largura'                  => 'nullable|numeric|min:0',
            'vidros.*.preco_m2'                 => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'obra.required'                         => 'O nome da obra é obrigatório.',
            'obra.max'                              => 'O nome da obra deve ter no máximo 255 caracteres.',
            'condicao_pagamento.required'           => 'Por favor, selecione uma condição de pagamento.',
            'tipos_transporte.required'             => 'Por favor, selecione o tipo de venda/transporte.',
            'tipos_transporte.exists'               => 'O tipo de transporte selecionado é inválido.',
            'venda_triangular.required'             => 'O campo venda triangular é obrigatório.',
            'homologacao.required'                  => 'O campo homologação é obrigatório.',
            'cnpj_triangular.required_if'           => 'O CNPJ é obrigatório para venda triangular.',
            'outros_meios_pagamento.required_if'    => 'Descreva o meio de pagamento especial.',
            'produtos.*.produto_id.exists'          => 'Um ou mais produtos selecionados não existem.',
            'entrega_estado.max'                    => 'O estado deve ter 2 caracteres (UF).',
            'vidros_removidos.*.exists'             => 'Um ou mais vidros a remover não foram encontrados.',
            'vidros_existentes.*.id.exists'         => 'Um ou mais vidros existentes não foram encontrados.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizarValor = function ($valor) {
            if (is_null($valor) || $valor === '' || $valor === '0') {
                return null;
            }

            $valor = trim((string) $valor);

            if (!str_contains($valor, ',') && !str_contains($valor, '.')) {
                return $valor;
            }

            if (str_contains($valor, ',')) {
                return str_replace(',', '.', str_replace('.', '', $valor));
            }

            $partes = explode('.', $valor);
            if (count($partes) === 2 && strlen($partes[1]) === 3 && !str_contains($valor, ' ')) {
                return str_replace('.', '', $valor);
            }

            return $valor;
        };

        $this->merge([
            'desconto_especifico' => $normalizarValor($this->desconto_especifico),
            'guia_recolhimento'   => $normalizarValor($this->guia_recolhimento),
            'frete'               => $normalizarValor($this->frete),
            'valor_total'         => $normalizarValor($this->valor_total),
        ]);
    }
}