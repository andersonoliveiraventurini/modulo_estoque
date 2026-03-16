<?php

namespace App\Http\Controllers;

use App\Models\Romaneio;
use App\Models\PickingBatch;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RomaneioController extends Controller
{
    /**
     * Display a listing of the romaneios.
     */
    public function index(Request $request)
    {
        $query = Romaneio::query()->withCount('batches');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('data_entrega')) {
            $query->whereDate('data_entrega', $request->data_entrega);
        }

        $romaneios = $query->latest()->paginate(15);

        return view('paginas.romaneios.index', compact('romaneios'));
    }

    /**
     * Show the form for creating a new romaneio.
     */
    public function create()
    {
        return view('paginas.romaneios.create');
    }

    /**
     * Store a newly created romaneio in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao'    => 'required|string|max:255',
            'motorista'    => 'nullable|string|max:255',
            'veiculo'      => 'nullable|string|max:255',
            'data_entrega' => 'required|date',
            'observacoes'  => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status']  = 'aberto';

        $romaneio = Romaneio::create($validated);

        return redirect()->route('romaneios.show', $romaneio)
            ->with('success', 'Romaneio criado com sucesso!');
    }

    /**
     * Display the specified romaneio.
     */
    public function show(Romaneio $romaneio)
    {
        $romaneio->load([
            'batches.orcamento.cliente.enderecos', 
            'batches.orcamento.vendedor', 
            'batches.criadoPor',
            'batches.items.produto'
        ]);
        
        // Lotes concluídos que NÃO estão em nenhum romaneio e cujo transporte não seja retirada/balcão
        $availableBatches = PickingBatch::where('status', 'concluido')
            ->whereNull('romaneio_id')
            ->whereHas('orcamento', function ($q) {
                // Exclui se tiver QUALQUER transporte do tipo 4 (retirada) ou 5 (balcão)
                $q->whereDoesntHave('transportes', function ($sub) {
                    $sub->whereIn('tipos_transportes.id', [4, 5]);
                });
            })
            ->with(['orcamento.cliente.enderecos', 'orcamento.vendedor', 'orcamento.transportes'])
            ->get();

        return view('paginas.romaneios.show', compact('romaneio', 'availableBatches'));
    }

    /**
     * Add batches to the romaneio.
     */
    public function addBatches(Request $request, Romaneio $romaneio)
    {
        $request->validate([
            'batch_ids' => 'required|array',
            'batch_ids.*' => 'exists:picking_batches,id',
        ]);

        PickingBatch::whereIn('id', $request->batch_ids)
            ->update(['romaneio_id' => $romaneio->id]);

        return back()->with('success', 'Pedidos adicionados ao romaneio!');
    }

    /**
     * Remove batch from romaneio.
     */
    public function removeBatch(Romaneio $romaneio, PickingBatch $batch)
    {
        if ($batch->romaneio_id === $romaneio->id) {
            $batch->update(['romaneio_id' => null]);
            return back()->with('success', 'Pedido removido do romaneio.');
        }

        return back()->with('error', 'Pedido não pertence a este romaneio.');
    }

    /**
     * Update the status of the romaneio.
     */
    public function updateStatus(Request $request, Romaneio $romaneio)
    {
        $request->validate([
            'status' => 'required|in:aberto,em_transito,concluido,cancelado',
        ]);

        $romaneio->update(['status' => $request->status]);

        return back()->with('success', 'Status do romaneio atualizado!');
    }

    /**
     * Export Romaneio to PDF for Driver.
     */
    public function exportPdf(Romaneio $romaneio)
    {
        $romaneio->load(['batches.orcamento.cliente.enderecos', 'batches.orcamento.vendedor', 'batches.items.produto']);
        
        $pdf = Pdf::loadView('paginas.romaneios.pdf', compact('romaneio'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'romaneio_' . $romaneio->id . '_' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }
}
