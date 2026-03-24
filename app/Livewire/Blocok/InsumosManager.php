<?php

namespace App\Livewire\Blocok;

use App\Models\BlocokInsumos;
use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class InsumosManager extends Component
{
    use WithPagination;

    public $produto_id;
    public $quantidade;
    public $unidade_medida = 'UN';

    public $search_produto;

    protected $listeners = ['produtoSelecionado' => 'handleProdutoSelecionado'];

    protected $rules = [
        'produto_id' => 'required|exists:produtos,id',
        'quantidade' => 'required|integer|min:1',
        'unidade_medida' => 'required|string|max:10',
    ];

    public function add()
    {
        $this->validate();

        BlocokInsumos::create([
            'produto_id' => $this->produto_id,
            'quantidade' => $this->quantidade,
            'unidade_medida' => $this->unidade_medida,
        ]);

        $this->reset(['produto_id', 'quantidade', 'search_produto']);
        session()->flash('success', 'Insumo registrado com sucesso.');
    }

    public function remove($id)
    {
        BlocokInsumos::find($id)?->delete();
        session()->flash('success', 'Registro removido.');
    }

    public function handleProdutoSelecionado($data)
    {
        $this->produto_id = $data['id'];
        $this->search_produto = $data['sku'] . ' - ' . $data['nome'];
    }

    public function render()
    {
        return view('livewire.blocok.insumos-manager', [
            'insumos' => BlocokInsumos::with('produto')->latest()->paginate(10),
        ]);
    }
}
