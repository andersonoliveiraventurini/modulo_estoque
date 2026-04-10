<?php

namespace App\Livewire\Estorno;

use App\Models\Estorno;
use App\Models\Pagamento;
use App\Services\EstornoService;
use Exception;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class EstornoForm extends Component
{
    public \App\Livewire\Forms\EstornoForm $form;

    public Pagamento $pagamento;

    public function mount($pagamentoId)
    {
        $this->authorize('create', Estorno::class);

        $this->pagamento = Pagamento::findOrFail($pagamentoId);
        
        $this->form->pagamento_id = $this->pagamento->id;
        $this->form->valor = $this->pagamento->valor_pago; // Default para o valor pago, mas o usuário pode editar se for parcial
    }

    public function save(EstornoService $estornoService)
    {
        $this->authorize('create', Estorno::class);
        $this->validate();

        try {
            $estornoService->solicitar(auth()->user(), $this->pagamento, $this->form->all());

            session()->flash('success', 'Solicitação de estorno enviada com sucesso!');
            return $this->redirectRoute('estornos.index', navigate: true);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->addError('geral', 'Erro ao solicitar estorno: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.estorno.estorno-form')->layout('components.layouts.app', ['title' => 'Solicitar Estorno']);
    }
}
