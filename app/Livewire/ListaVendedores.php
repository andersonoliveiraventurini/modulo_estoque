<?php

namespace App\Livewire;

use App\Models\Vendedor;
use Livewire\Component;
use Livewire\WithPagination;

class ListaVendedores extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'nome';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Controle do modal
    public $confirmandoDelete = false;
    public $idParaDeletar = null;
    public $nomeParaDeletar = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'nome'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function confirmarDelete($id, $nome)
    {
        $this->idParaDeletar    = $id;
        $this->nomeParaDeletar  = $nome;
        $this->confirmandoDelete = true;
    }

    public function cancelarDelete()
    {
        $this->idParaDeletar    = null;
        $this->nomeParaDeletar  = '';
        $this->confirmandoDelete = false;
    }

    public function deletar()
    {
        $vendedor = Vendedor::findOrFail($this->idParaDeletar);
        $vendedor->delete();

        $this->cancelarDelete();
        $this->resetPage();

        session()->flash('success', 'Vendedor excluído com sucesso!');
    }

    public function render()
    {
        $vendedores = Vendedor::query()
            ->with('user')
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->where('desconto', 'like', "%{$normalizedTerm}%")
                            ->orWhereHas('user', function ($uq) use ($normalizedTerm) {
                                $uq->where('name', 'like', "%{$normalizedTerm}%");
                            });
                    });
                }
            })
            ->when($this->sortField === 'nome', function ($query) {
                $query->join('users', 'vendedores.user_id', '=', 'users.id')
                    ->orderBy('users.name', $this->sortDirection)
                    ->select('vendedores.*');
            }, function ($query) {
                $query->orderBy($this->sortField, $this->sortDirection);
            })
            ->paginate($this->perPage);

        return view('livewire.lista-vendedores', [
            'vendedores' => $vendedores,
        ]);
    }
}