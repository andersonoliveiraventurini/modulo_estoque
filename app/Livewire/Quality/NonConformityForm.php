<?php

namespace App\Livewire\Quality;

use App\Models\Fornecedor;
use App\Models\NonConformity;
use App\Models\Produto;
use App\Services\NonConformityService;
use Livewire\Component;
use Flux\Flux;

class NonConformityForm extends Component
{
    public $rncId;
    public $isEdit = false;

    // Form fields
    public $produto_id;
    public $produto_nome;
    public $fornecedor_id;
    public $fornecedor_nome;
    public $data_ocorrencia;
    public $nota_fiscal;
    public $romaneio_recebimento;
    public $acoes_tomadas;
    public $observacoes;

    // Search fields
    public $searchProduto = '';
    public $searchFornecedor = '';
    public $showProdutoSearch = false;
    public $showFornecedorSearch = false;

    protected $rules = [
        'data_ocorrencia' => 'required|date',
        'produto_nome' => 'required|string',
        'fornecedor_nome' => 'required|string',
        'nota_fiscal' => 'nullable|string',
        'romaneio_recebimento' => 'nullable|string',
        'acoes_tomadas' => 'nullable|string',
        'observacoes' => 'nullable|string',
    ];

    public function mount($rnc = null)
    {
        $this->data_ocorrencia = date('Y-m-d');
        
        if ($rnc) {
            $this->isEdit = true;
            $this->rncId = $rnc;
            $model = NonConformity::findOrFail($rnc);
            $this->fill($model->toArray());
            $this->data_ocorrencia = $model->data_ocorrencia->format('Y-m-d');
        }
    }

    public function selectProduto($id, $nome)
    {
        $this->produto_id = $id;
        $this->produto_nome = $nome;
        $this->showProdutoSearch = false;
        $this->searchProduto = '';
    }

    public function selectFornecedor($id, $nome)
    {
        $this->fornecedor_id = $id;
        $this->fornecedor_nome = $nome;
        $this->showFornecedorSearch = false;
        $this->searchFornecedor = '';
    }

    public function save(NonConformityService $service)
    {
        $this->validate();

        $data = [
            'produto_id' => $this->produto_id,
            'produto_nome' => $this->produto_nome,
            'fornecedor_id' => $this->fornecedor_id,
            'fornecedor_nome' => $this->fornecedor_nome,
            'data_ocorrencia' => $this->data_ocorrencia,
            'nota_fiscal' => $this->nota_fiscal,
            'romaneio_recebimento' => $this->romaneio_recebimento,
            'acoes_tomadas' => $this->acoes_tomadas,
            'observacoes' => $this->observacoes,
        ];

        if ($this->isEdit) {
            $rnc = NonConformity::findOrFail($this->rncId);
            $service->update($rnc, $data);
            session()->flash('success', 'RNC atualizada com sucesso!');
        } else {
            $rnc = $service->store($data);
            session()->flash('success', "RNC #{$rnc->nr} criada com sucesso!");
        }

        return redirect()->route('quality.dashboard');
    }

    public function render()
    {
        $produtos = [];
        if ($this->showProdutoSearch && strlen($this->searchProduto) >= 2) {
            $produtos = Produto::where('nome', 'like', "%{$this->searchProduto}%")
                ->orWhere('sku', 'like', "%{$this->searchProduto}%")
                ->limit(5)
                ->get();
        }

        $fornecedores = [];
        if ($this->showFornecedorSearch && strlen($this->searchFornecedor) >= 2) {
            $fornecedores = Fornecedor::where('nome_fantasia', 'like', "%{$this->searchFornecedor}%")
                ->orWhere('razao_social', 'like', "%{$this->searchFornecedor}%")
                ->orWhere('cnpj', 'like', "%{$this->searchFornecedor}%")
                ->limit(5)
                ->get();
        }

        return view('livewire.quality.non-conformity-form', [
            'produtos' => $produtos,
            'fornecedores' => $fornecedores,
        ])->layout('components.layouts.app');
    }
}
