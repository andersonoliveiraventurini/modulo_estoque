<?php

namespace App\Livewire;

use App\Models\Ncm;
use Livewire\Component;
use Livewire\WithPagination;

class ListaNcm extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'codigo';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'codigo'],
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
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function render()
    {
        $ncms = Ncm::query()
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $query->where(function ($q) use ($term) {
                        $q->where('codigo', 'like', "%{$term}%")
                          ->orWhere('descricao', 'like', "%{$term}%")
                          ->orWhere('numero', 'like', "%{$term}%")
                          ->orWhere('ato_legal', 'like', "%{$term}%");
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-ncm', [
            'ncms' => $ncms,
        ]);
    }
}