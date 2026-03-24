<?php
namespace App\Http\Controllers;

use App\Models\PedidoCompra;
use App\Models\PedidoCompraFollowup;
use Illuminate\Http\Request;

class PedidoCompraFollowupController extends Controller
{
    public function store(Request $request, PedidoCompra $pedidoCompra)
    {
        $request->validate([
            'descricao' => 'required|string|max:1000',
            'tipo' => 'required|in:cobranca,atualizacao_prazo,observacao',
            'previsao_nova' => 'nullable|date|after:today',
        ]);

        $followup = $pedidoCompra->followUps()->create([
            'user_id' => auth()->id(),
            'descricao' => $request->descricao,
            'tipo' => $request->tipo,
            'previsao_nova' => $request->previsao_nova,
        ]);

        // Se for atualização de prazo, atualiza o pedido também
        if ($request->tipo === 'atualizacao_prazo' && $request->previsao_nova) {
            $pedidoCompra->update(['previsao_entrega' => $request->previsao_nova]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'followup' => $followup->load('user'),
            ]);
        }

        return back()->with('success', 'Follow up registrado!');
    }

    public function destroy(PedidoCompraFollowup $followup)
    {
        $this->authorize('delete', $followup);
        $followup->delete();
        return back()->with('success', 'Follow up removido.');
    }
}
