<?php

namespace App\Livewire\Estorno;

use App\Models\Estorno;
use App\Services\EstornoService;
use Exception;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class EstornoApproval extends Component
{
    public $observacao = '';
    public $estornoEmAtendimento = null;
    public $acaoSelecionada = null; // 'approve' ou 'reject'
    public bool $showModal = false;

    public function selecionarEstorno($id, $acao)
    {
        $this->estornoEmAtendimento = Estorno::findOrFail($id);
        $this->acaoSelecionada = $acao;
        $this->observacao = '';
        $this->showModal = true;
    }

    public function processarAcao(EstornoService $estornoService)
    {
        $this->validate([
            'observacao' => $this->acaoSelecionada === 'reject' ? 'required|string|min:5' : 'nullable|string',
        ]);

        $estorno = Estorno::findOrFail($this->estornoEmAtendimento->id);
        
        $this->authorize('approve', $estorno);

        try {
            if ($this->acaoSelecionada === 'approve') {
                $estornoService->aprovar(auth()->user(), $estorno, $this->observacao);
                session()->flash('success', 'Estorno aprovado com sucesso!');
            } else {
                $estornoService->rejeitar(auth()->user(), $estorno, $this->observacao);
                session()->flash('success', 'Estorno rejeitado com sucesso.');
            }

            $this->dispatch('estorno-decidido');
            $this->cancelar();

        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->addError('geral', 'Erro ao processar ação: ' . $e->getMessage());
        }
    }

    public function cancelar()
    {
        $this->estornoEmAtendimento = null;
        $this->acaoSelecionada = null;
        $this->observacao = '';
        $this->showModal = false;
    }

    public function render()
    {
        // Verifica se o usuário tem permissão para visualizar o index e aprovar estornos
        $this->authorize('viewAny', Estorno::class);

        $pendentes = Estorno::with(['pagamento', 'solicitante'])
            ->where('status', Estorno::STATUS_PENDENTE)
            // Regra forte: O aprovador não pode despachar estornos criados por ele mesmo (exceto admin)
            ->when(!auth()->user()->hasRole('admin'), function ($query) {
                $query->where('solicitante_id', '!=', auth()->id());
            })
            ->get();

        return view('livewire.estorno.estorno-approval', compact('pendentes'))
            ->layout('components.layouts.app', ['title' => 'Aprovação de Estornos']);
    }
}
