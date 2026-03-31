<?php

namespace App\Livewire;

use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class ListaProdutoOrcamento extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nome';
    public $sortDirection = 'asc';
    public $perPage = 25;
    public $showStock = true;
    public $showDiscount = true;
    public $showPrice = true;
    public $context = 'orcamento'; // 'orcamento' or component name
    public $purpose = ''; // custom identifier for listeners

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nome'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function buscar()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function render()
    {
       $produtos = Produto::query()
    ->with('fornecedor') // já carrega o fornecedor
    ->where('status', 'ativo')
    ->when($this->search, function ($query) {
        $terms = preg_split('/\s+/', trim($this->search));

        foreach ($terms as $term) {
            $normalizedTerm = str_replace(',', '.', $term);

            $query->where(function ($q) use ($normalizedTerm) {
                $q->where('nome', 'like', "%{$normalizedTerm}%")
                  ->orWhere('id', 'like', "%{$normalizedTerm}%")
                  ->orWhere('sku', 'like', "%{$normalizedTerm}%")
                  ->orWhere('preco_venda', 'like', "%{$normalizedTerm}%")
                  ->orWhere('codigo_barras', 'like', "%{$normalizedTerm}%")
                  ->orWhere('observacoes', 'like', "%{$normalizedTerm}%")
                  ->orWhere('descricao', 'like', "%{$normalizedTerm}%")
                  ->orWhere('ncm', 'like', "%{$normalizedTerm}%")
                  ->orWhereHas('fornecedor', function ($fq) use ($normalizedTerm) {
                      $fq->where('nome_fantasia', 'like', "%{$normalizedTerm}%")
                         ->orWhere('razao_social', 'like', "%{$normalizedTerm}%")
                         ->orWhere('tratamento', 'like', "%{$normalizedTerm}%");
                  });
            });
        }
    })
    ->orderBy($this->sortField, $this->sortDirection)
    ->paginate($this->perPage);

        return view('livewire.lista-produto-orcamento', [
            'produtos' => $produtos,
        ]);
    }

    public function selectProduct($id)
    {
        $produto = Produto::with(['fornecedor', 'cor'])->find($id);
        if ($produto) {
            $this->dispatch('produtoSelecionado', [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'sku' => $produto->sku,
                'preco' => $produto->preco_venda,
                'fornecedor' => $produto->fornecedor?->nome_fantasia ?? 'Sem fornecedor',
                'cor' => $produto->cor?->nome ?? 'Sem cor',
                'partNumber' => $produto->part_number ?? '',
                'liberarDesconto' => $produto->liberar_desconto,
                'estoqueAtual' => $produto->estoque_atual,
                'is_perishable' => $produto->is_perishable,
                'purpose' => $this->purpose
            ])->to($this->context === 'orcamento' ? null : $this->context);
        }
    }
}
