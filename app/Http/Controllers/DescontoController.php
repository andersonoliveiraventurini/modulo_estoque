<?php

namespace App\Http\Controllers;

use App\Services\OrcamentoPdfService;

use App\Http\Requests\StoreDescontoRequest;
use App\Http\Requests\UpdateDescontoRequest;
use App\Models\Desconto;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DescontoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $descontos = Desconto::paginate();
        return view('paginas.descontos.index', compact('descontos'));
    }

    public function descontosAprovados()
    {
        return view('paginas.descontos.aprovados');
    }

    public function descontosClientes()
    {
        return view('paginas.descontos.clientes');
    }


    private function verificarEAtualizarOrcamento($orcamentoId)
    {
        if (!$orcamentoId) {
            return;
        }

        // Busca o orçamento
        $orcamento = Orcamento::find($orcamentoId);

        if (!$orcamento) {
            return;
        }

        // Verifica se ainda existem descontos pendentes
        $descontosPendentes = Desconto::where('orcamento_id', $orcamentoId)
            ->whereNull('aprovado_em')
            ->count();

        // Se não houver mais descontos pendentes, atualiza o orçamento
        if ($descontosPendentes > 0) {
           // Calcula o total de descontos aprovados
            $totalDescontosAprovados = Desconto::where('orcamento_id', $orcamentoId)
                ->whereNotNull('aprovado_em')
                ->sum('valor');

            // Calcula o novo valor total do orçamento
            $valorOriginal = $orcamento->valor_total_itens ?? 0;
            $valorFinal = $valorOriginal - $totalDescontosAprovados;

            // Atualiza o orçamento
            $orcamento->update([
                'desconto_total' => $totalDescontosAprovados,
                'valor_com_desconto' => $valorFinal,
            ]);  
        }
        else{
            // Calcula o total de descontos aprovados
            $totalDescontosAprovados = Desconto::where('orcamento_id', $orcamentoId)
                ->whereNotNull('aprovado_em')
                ->sum('valor');

            // Calcula o novo valor total do orçamento
            $valorOriginal = $orcamento->valor_total_itens ?? 0;
            $valorFinal = $valorOriginal - $totalDescontosAprovados;

            // Atualiza o orçamento
            $orcamento->update([
                'status' => 'Pendente',
                'desconto_total' => $totalDescontosAprovados,
                'valor_com_desconto' => $valorFinal,
            ]);

            $orcamento = Orcamento::find($orcamentoId);
            $pdfService = new OrcamentoPdfService();
            $pdfGeradoComSucesso = $pdfService->gerarOrcamentoPdf($orcamento);
        }

            /** Log da atualização (opcional)
             *\Log::info("Orçamento #{$orcamentoId} atualizado após aprovação de descontos", [
             *    'valor_original' => $valorOriginal,
             *    'total_descontos' => $totalDescontosAprovados,
             *    'valor_final' => $valorFinal,
             *    'usuario' => Auth::id(),
             *]);
             */
        
    }

    public function aprovar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $desconto = Desconto::findOrFail($id);

            // Verifica se o desconto já foi aprovado ou rejeitado
            if ($desconto->aprovado_em) {
                return back()->with('error', 'Este desconto já foi aprovado anteriormente.');
            }

            if ($desconto->rejeitado_em) {
                return back()->with('error', 'Este desconto já foi rejeitado anteriormente.');
            }

            // Atualiza os dados de aprovação
            $desconto->update([
                'aprovado_em' => now(),
                'aprovado_por' => Auth::id(),
                'justificativa_aprovacao' => $request->justificativa,
            ]);

            // Verifica e atualiza o orçamento se não houver mais descontos pendentes
            $this->verificarEAtualizarOrcamento($desconto->orcamento_id);

            DB::commit();

            return back()->with('success', 'Desconto aprovado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aprovar desconto: ' . $e->getMessage());
        }
    }

    /**
     * Rejeita um desconto e o remove do sistema
     */
    public function rejeitar(Request $request, $id)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $desconto = Desconto::findOrFail($id);

            // Verifica se o desconto já foi aprovado ou rejeitado
            if ($desconto->aprovado_em) {
                return back()->with('error', 'Este desconto já foi aprovado e não pode ser rejeitado.');
            }

            if ($desconto->rejeitado_em) {
                return back()->with('error', 'Este desconto já foi rejeitado anteriormente.');
            }

            // Atualiza os dados de rejeição antes de deletar
            $desconto->update([
                'rejeitado_em' => now(),
                'rejeitado_por' => Auth::id(),
                'justificativa_rejeicao' => $request->justificativa,
            ]);

            // remove o desconto dos itens do orçamento
            $itens_sem_desconto = OrcamentoItens::where('orcamento_id', $desconto->orcamento_id)->get();
            foreach ($itens_sem_desconto as $item) {
                $item->update([
                    'valor_unitario_com_desconto' => $item->valor_unitario,
                    'valor_com_desconto' => $item->valor_unitario * $item->quantidade,
                    'desconto' => 0,
                ]);
            }

            // Soft delete do desconto
            $desconto->delete();

            // Verifica e atualiza o orçamento se não houver mais descontos pendentes
            $this->verificarEAtualizarOrcamento($desconto->orcamento_id);

            DB::commit();

            return back()->with('success', 'Desconto rejeitado e removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao rejeitar desconto: ' . $e->getMessage());
        }
    }

    /**
     * Avalia um desconto (aprovar ou rejeitar) - rota única
     */
    public function avaliar(Request $request, $id)
    {
        $request->validate([
            'acao' => 'required|in:aprovar,rejeitar',
            'justificativa' => 'nullable|string|max:1000',
        ]);

        if ($request->acao === 'aprovar') {
            return $this->aprovar($request, $id);
        } else {
            return $this->rejeitar($request, $id);
        }
    }

    /**
     * Aprova todos os descontos de um orçamento
     */
    public function aprovarTodos(Request $request, $orcamentoId)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $descontos = Desconto::where('orcamento_id', $orcamentoId)
                ->whereNull('aprovado_em')
                ->whereNull('rejeitado_em')
                ->get();

            if ($descontos->isEmpty()) {
                return back()->with('warning', 'Não há descontos pendentes para aprovar.');
            }

            $count = 0;
            foreach ($descontos as $desconto) {
                $desconto->update([
                    'aprovado_em' => now(),
                    'aprovado_por' => Auth::id(),
                    'justificativa_aprovacao' => $request->justificativa ?? 'Aprovação em lote',
                ]);
                $count++;
            }

            $this->gerarpdfeAtualizarOrcamento($orcamentoId);
        
            DB::commit();

            return back()->with('success', "Total de {$count} desconto(s) aprovado(s) com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao aprovar descontos: ' . $e->getMessage());
        }
    }

    /**
     * Rejeita todos os descontos de um orçamento
     */
    public function rejeitarTodos(Request $request, $orcamentoId)
    {
        $request->validate([
            'justificativa' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $descontos = Desconto::where('orcamento_id', $orcamentoId)
                ->whereNull('aprovado_em')
                ->whereNull('rejeitado_em')
                ->get();

            if ($descontos->isEmpty()) {
                return back()->with('warning', 'Não há descontos pendentes para rejeitar.');
            }

            $count = 0;
            foreach ($descontos as $desconto) {
                $desconto->update([
                    'rejeitado_em' => now(),
                    'rejeitado_por' => Auth::id(),
                    'justificativa_rejeicao' => $request->justificativa ?? 'Rejeição em lote',
                ]);
                $desconto->delete();
                $count++;
            }

            // remove o desconto dos itens do orçamento
            $itens_sem_desconto = OrcamentoItens::where('orcamento_id', $desconto->orcamento_id)->get();
            foreach ($itens_sem_desconto as $item) {
                $item->update([
                    'valor_unitario_com_desconto' => $item->valor_unitario,
                    'valor_com_desconto' => $item->valor_unitario * $item->quantidade,
                    'desconto' => 0,
                ]);
            }

            // Verifica e atualiza o orçamento se não houver mais descontos pendentes
            $this->gerarpdfeAtualizarOrcamento($orcamentoId);

            DB::commit();

            return back()->with('success', "Total de {$count} desconto(s) rejeitado(s) com sucesso!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao rejeitar descontos: ' . $e->getMessage());
        }
    }

    private function gerarpdfeAtualizarOrcamento($orcamentoId)
    {
         $totalDescontosAprovados = Desconto::where('orcamento_id', $orcamentoId)
                ->whereNotNull('aprovado_em')
                ->sum('valor');

            $orcamento = Orcamento::find($orcamentoId);
            // Calcula o novo valor total do orçamento
            $valorOriginal = $orcamento->valor_total_itens ?? 0;
            $valorFinal = $valorOriginal - $totalDescontosAprovados;

            // Atualiza o orçamento
            $orcamento->update([
                'status' => 'Pendente',
                'desconto_total' => $totalDescontosAprovados,
                'valor_com_desconto' => $valorFinal,
            ]);

            $pdfService = new OrcamentoPdfService();
            $pdfGeradoComSucesso = $pdfService->gerarOrcamentoPdf($orcamento);
    }

    public function desconto_orcamento($orcamento_id)
    {
        return view('paginas.descontos.create', compact('orcamento_id'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDescontoRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Desconto $desconto)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Desconto $desconto)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDescontoRequest $request, Desconto $desconto)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Desconto $desconto)
    {
        //
    }
}
