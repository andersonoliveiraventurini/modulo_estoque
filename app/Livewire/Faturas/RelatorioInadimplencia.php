<?php

namespace App\Livewire\Faturas;

use App\Models\Fatura;
use Livewire\Component;
use App\Services\FaturaService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;

#[Title('Inadimplência - Visão Geral')]
class RelatorioInadimplencia extends Component
{
    public function render()
    {
        // First ensure any pending but past due invoices are updated
        app(FaturaService::class)->verificarInadimplencia();

        $vencidos = Fatura::where('status', 'vencido')->get();
        
        $totalVencido = $vencidos->sum(fn($f) => $f->valor_total - $f->valor_pago);
        $totalAtrasados = $vencidos->count();

        // Group by cliente
        $porCliente = Fatura::with('cliente')
            ->select('cliente_id', DB::raw('SUM(valor_total - valor_pago) as devendo'), DB::raw('COUNT(id) as faturas_count'))
            ->where('status', 'vencido')
            ->groupBy('cliente_id')
            ->orderByDesc('devendo')
            ->get();

        return view('livewire.faturas.relatorio-inadimplencia', [
            'totalVencido' => $totalVencido,
            'totalAtrasados' => $totalAtrasados,
            'porCliente' => $porCliente,
            'faturas' => Fatura::with('cliente')->where('status', 'vencido')->latest('data_vencimento')->paginate(10)
        ]);
    }
}
