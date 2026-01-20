<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrcamentoRequest extends FormRequest
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
            // Dados básicos
            'obra' => 'required|string|max:255', // CORRIGIDO: era 'nome_obra'
            'observacoes' => 'nullable|string',
            'prazo_entrega' => 'nullable|date',
            
            // Valores monetários
            'valor_total' => 'nullable|string',
            'frete' => 'nullable|string',
            'desconto' => 'nullable|numeric|min:0|max:100',
            'desconto_aprovado' => 'nullable|numeric|min:0|max:100',
            'desconto_especifico' => 'nullable|string',
            'guia_recolhimento' => 'nullable|string',
            'venda_triangular' => 'required|boolean',
            'cnpj_triangular' => 'required_if:venda_triangular,1|nullable|string|max:18',
            'tipos_transporte' => 'required|integer|exists:tipos_transportes,id',

            'condicao_pagamento' => 'required',
            'outros_meios_pagamento' => 'required_if:condicao_pagamento,20|nullable|string|max:255',
                        
            // Endereço
            'entrega_cep' => 'nullable|string|max:9',
            'entrega_logradouro' => 'nullable|string|max:255',
            'entrega_numero' => 'nullable|string|max:20',
            'entrega_compl' => 'nullable|string|max:100',
            'entrega_bairro' => 'nullable|string|max:100',
            'entrega_cidade' => 'nullable|string|max:100',
            'entrega_estado' => 'nullable|string|size:2',
            
            // Produtos existentes
            'produtos' => 'nullable|array',
            'produtos.*.produto_id' => 'required_with:produtos|exists:produtos,id',
            'produtos.*.quantidade' => 'required_with:produtos|numeric|min:0',
            'produtos.*.valor_unitario' => 'required_with:produtos|numeric|min:0',
            'produtos.*._remove' => 'nullable|boolean',
            
            // Novos itens
            'itens' => 'nullable|array',
            'itens.*.quantidade' => 'required_with:itens|numeric|min:0',
            
            // Vidros
            'vidros_removidos' => 'nullable|array',
            'vidros_removidos.*' => 'exists:orcamento_vidros,id',
            
            'vidros_existentes' => 'nullable|array',
            'vidros_existentes.*.id' => 'required_with:vidros_existentes|exists:orcamento_vidros,id',
            'vidros_existentes.*.quantidade' => 'nullable|numeric|min:0',
            'vidros_existentes.*.altura' => 'nullable|numeric|min:0',
            'vidros_existentes.*.largura' => 'nullable|numeric|min:0',
            'vidros_existentes.*.preco_m2' => 'nullable|numeric|min:0',
            
            'vidros' => 'nullable|array',
            'vidros.*.quantidade' => 'nullable|numeric|min:0',
            'vidros.*.altura' => 'nullable|numeric|min:0',
            'vidros.*.largura' => 'nullable|numeric|min:0',
            'vidros.*.preco_m2' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'obra.required' => 'O nome da obra é obrigatório.',
            'obra.max' => 'O nome da obra deve ter no máximo 255 caracteres.',
            'vendedor_id.exists' => 'Vendedor selecionado não existe.',
            'produtos.*.produto_id.exists' => 'Um ou mais produtos selecionados não existem.',
            'itens.*.id.exists' => 'Um ou mais produtos novos não existem.',
            'entrega_estado.size' => 'O estado deve ter 2 caracteres (UF).',
        ];
    }

    protected function prepareForValidation()
    {
        // Função helper para normalizar valores brasileiros
        $normalizarValor = function($valor) {
            if (empty($valor) || $valor === '0') {
                return null;
            }
            
            // Remove espaços
            $valor = trim($valor);
            
            // Se não tem vírgula nem ponto, é um valor inteiro
            if (!str_contains($valor, ',') && !str_contains($valor, '.')) {
                return $valor; // Retorna como está (inteiro)
            }
            
            // Se tem vírgula, é formato brasileiro (1.234,56)
            if (str_contains($valor, ',')) {
                // Remove pontos (separador de milhares) e troca vírgula por ponto
                return str_replace(',', '.', str_replace('.', '', $valor));
            }
            
            // Se tem apenas ponto, verifica se é milhares ou decimal
            // Ex: 1.234 (milhares) vs 1234.56 (decimal)
            $partes = explode('.', $valor);
            if (count($partes) == 2 && strlen($partes[1]) == 3 && !str_contains($valor, ' ')) {
                // Provavelmente é separador de milhares (1.234)
                return str_replace('.', '', $valor);
            }
            
            // Caso contrário, mantém como está (já está com ponto decimal)
            return $valor;
        };

        $this->merge([
            'desconto_especifico' => $normalizarValor($this->desconto_especifico),
            'guia_recolhimento' => $normalizarValor($this->guia_recolhimento),
            'frete' => $normalizarValor($this->frete),
        ]);
    }
}
