<?php

namespace App\Livewire\Quality;

use App\Models\NonConformity;
use App\Models\ProductReturn;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class QualityDashboard extends Component
{
    use WithPagination;

    public $confirmingReturnId = null;
    public $confirmingAction = '';

    public $search = '';
    public $status = '';
    public $date_start;
    public $date_end;

    public function mount()
    {
        $this->date_start = now()->startOfMonth()->format('Y-m-d');
        $this->date_end = now()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'status', 'date_start', 'date_end'])) {
            $this->resetPage();
        }
    }

    public function downloadRnc($id)
    {
        $rnc = NonConformity::findOrFail($id);
        $path = "quality/rnc_{$rnc->nr}.pdf";
        
        if (!Storage::disk('public')->exists($path)) {
            app(\App\Services\QualityPdfService::class)->generateRncPdf($rnc);
        }
        
        return response()->download(storage_path("app/public/{$path}"));
    }

    public function downloadReturn($id, $type = 'solicited')
    {
        $return = ProductReturn::findOrFail($id);
        $path = "quality/return_{$type}_{$return->nr}.pdf";
        
        if (!Storage::disk('public')->exists($path)) {
            app(\App\Services\QualityPdfService::class)->generateReturnPdf($return, $type);
        }
        
        return response()->download(storage_path("app/public/{$path}"));
    }

    public function render()
    {
        $rncs = NonConformity::query()
            ->when($this->search, function($q) {
                $q->where('nr', 'like', "%{$this->search}%")
                  ->orWhere('produto_nome', 'like', "%{$this->search}%")
                  ->orWhere('fornecedor_nome', 'like', "%{$this->search}%");
            })
            ->when($this->date_start, fn($q) => $q->where('data_ocorrencia', '>=', $this->date_start))
            ->when($this->date_end, fn($q) => $q->where('data_ocorrencia', '<=', $this->date_end))
            ->latest()
            ->paginate(10, ['*'], 'rncPage');

        $returns = ProductReturn::query()
            ->with(['cliente', 'orcamento', 'vendedor'])
            ->when($this->search, function($q) {
                $q->where('nr', 'like', "%{$this->search}%")
                  ->orWhereHas('cliente', fn($sq) => $sq->where('nome', 'like', "%{$this->search}%"))
                  ->orWhereHas('orcamento', fn($sq) => $sq->where('id', 'like', "%{$this->search}%"));
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->date_start, fn($q) => $q->where('data_ocorrencia', '>=', $this->date_start))
            ->when($this->date_end, fn($q) => $q->where('data_ocorrencia', '<=', $this->date_end))
            ->latest()
            ->paginate(10, ['*'], 'returnPage');

        return view('livewire.quality.quality-dashboard', [
            'rncs' => $rncs,
            'returns' => $returns,
            'stats' => [
                'total_rnc' => NonConformity::count(),
                'total_returns' => ProductReturn::count(),
                'pending_supervisor' => ProductReturn::where('status', 'pendente_supervisor')->count(),
                'pending_estoque' => ProductReturn::where('status', 'pendente_estoque')->count(),
            ]
        ])->layout('components.layouts.app');
    }
}
