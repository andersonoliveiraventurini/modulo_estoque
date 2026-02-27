<?php

namespace App\Livewire;

use App\Models\ConsultaPrecoGrupo;
use Livewire\Component;
use Livewire\WithPagination;

class ListaConsultaPreco extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
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
        // Expira automaticamente grupos vencidos
        ConsultaPrecoGrupo::where('status', 'Disponível')
            ->where('validade', '<', now())
            ->update(['status' => 'Expirado']);

        $grupos = ConsultaPrecoGrupo::with(['cliente', 'usuario', 'itens'])
            ->withCount('itens')
            ->when($this->search, function ($query) {
                $terms = preg_split('/\s+/', trim($this->search));
                foreach ($terms as $term) {
                    $normalizedTerm = str_replace(',', '.', $term);
                    $query->where(function ($q) use ($normalizedTerm) {
                        $q->whereHas('cliente', fn($qc) =>
                        $qc->where('nome_fantasia', 'like', "%{$normalizedTerm}%")
                            ->orWhere('nome', 'like', "%{$normalizedTerm}%")
                        )
                            ->orWhereHas('usuario', fn($qu) =>
                            $qu->where('name', 'like', "%{$normalizedTerm}%")
                            )
                            ->orWhereHas('itens', fn($qi) =>
                            $qi->where('descricao', 'like', "%{$normalizedTerm}%")
                                ->orWhere('part_number', 'like', "%{$normalizedTerm}%")
                            )
                            ->orWhere('status', 'like', "%{$normalizedTerm}%");
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.lista-consulta-preco', [
            'grupos' => $grupos,
        ]);
    }
}
