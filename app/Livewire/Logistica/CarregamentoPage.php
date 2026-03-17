<?php
 
namespace App\Livewire\Logistica;

use App\Models\Orcamento;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Carregamento de Rota')]
class CarregamentoPage extends Component
{
    use WithPagination;

    public $loadingDay = '';
    public $search = '';

    protected $queryString = [
        'loadingDay' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updating($field)
    {
        if (in_array($field, ['loadingDay', 'search'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $this->authorize('viewLoading', Orcamento::class);

        $query = Orcamento::query();
        
        $query->with(['cliente', 'vendedor', 'transportes', 'routeBillingApprovals']);
        
        // Filtro por tipo de transporte (Rota/Similar)
        $query->whereHas('transportes', function ($q) {
            $q->whereIn('tipo_transporte_id', [1, 2, 3, 6, 7]);
        });
        
        // Apenas com dia definido
        $query->whereNotNull('loading_day');
        
        // Apenas com aprovação aprovada (mais recente)
        $query->whereHas('routeBillingApprovals', function ($q) {
            $q->where('status', 'approved')
              ->whereIn('id', function($sub) {
                  $sub->selectRaw('MAX(id)')
                      ->from('route_billing_approvals')
                      ->groupBy('orcamento_id');
              });
        });

        if ($this->loadingDay) {
            $query->where('loading_day', '=', $this->loadingDay);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('id', 'like', "%{$this->search}%")
                  ->orWhereHas('cliente', function($qc) {
                      $qc->where('nome', 'like', "%{$this->search}%")
                         ->orWhere('nome_fantasia', 'like', "%{$this->search}%");
                  });
            });
        }

        $orcamentos = $query->orderBy('loading_day')->latest()->paginate(20);

        return view('livewire.logistica.carregamento-page', [
            'orcamentos' => $orcamentos
        ]);
    }
}
