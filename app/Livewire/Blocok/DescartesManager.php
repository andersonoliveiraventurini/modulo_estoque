<?php

namespace App\Livewire\Blocok;

use App\Models\BlocokDescartes;
use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class DescartesManager extends Component
{
    use WithPagination;

    public $produto_id;
    public $produto_descartado_id;
    public $quantidade_descarte;
    public $unidade_medida_descarte = 'UN';

    public $search_produto;
    public $search_descartado;

    protected $listeners = ['produtoSelecionado' => 'handleProdutoSelecionado'];

    protected $rules = [
        'produto_id' => 'required|exists:produtos,id',
        'produto_descartado_id' => 'required|exists:produtos,id',
        'quantidade_descarte' => 'required|numeric|min:0.01',
        'unidade_medida_descarte' => 'required|string|max:10',
    ];

    public function add()
    {
        $this->validate();

        BlocokDescartes::create([
            'produto_id' => $this->produto_id,
            'produto_descartado_id' => $this->produto_descartado_id,
            'quantidade_descarte' => $this->quantidade_descarte,
            'unidade_medida_descarte' => $this->unidade_medida_descarte,
        ]);

        $this->reset(['produto_id', 'produto_descartado_id', 'quantidade_descarte', 'search_produto', 'search_descartado']);
        session()->flash('success', 'Descarte registrado com sucesso.');
    }

    public function remove($id)
    {
        BlocokDescartes::find($id)?->delete();
        session()->flash('success', 'Registro removido.');
    }

    public function handleProdutoSelecionado($data)
    {
        if (($data['purpose'] ?? '') === 'resultante') {
            $this->produto_id = $data['id'];
            $this->search_produto = $data['sku'] . ' - ' . $data['nome'];
        } elseif (($data['purpose'] ?? '') === 'descartado') {
            $this->produto_descartado_id = $data['id'];
            $this->search_descartado = $data['sku'] . ' - ' . $data['nome'];
        }
    }

    public function render()
    {
        return view('livewire.blocok.descartes-manager', [
            'descartes' => BlocokDescartes::with(['produto', 'produtoDescartado'])->latest()->paginate(10),
        ]);
    }
}
