<?php

namespace App\Livewire;

use App\Models\Orcamento;
use App\Models\Pagamento;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class OrcamentoPagamentoResidual extends Component
{
    public Orcamento $orcamento;
    public string $valor = '';
    public string $observacoes = '';
    public bool $mostrarFormulario = false;

    public function mount(Orcamento $orcamento): void
    {
        if (!$orcamento->isEncomenda() || $orcamento->pagamentos()->ativos()->doesntExist()) {
            $this->mostrarFormulario = false;
            return;
        }
        $this->mostrarFormulario = true;
    }

    public function salvarResidual(): void
    {
        try {
            $this->validate([
                'valor'       => 'required|numeric|min:0.01',
                'observacoes' => 'required|string|min:3|max:500',
            ], [
                'valor.required' => 'O valor é obrigatório.',
                'valor.min' => 'O valor deve ser maior que zero.',
                'observacoes.required' => 'O motivo da cobrança é obrigatório.',
                'observacoes.min' => 'O motivo deve ter pelo menos 3 caracteres.',
            ]);

            \Log::info("Iniciando criação de pagamento residual para Orçamento #{$this->orcamento->id}", [
                'valor' => $this->valor,
                'user_id' => auth()->id()
            ]);

            $pagamento = Pagamento::create([
                'orcamento_id'          => $this->orcamento->id,
                'condicao_pagamento_id' => $this->orcamento->condicao_id ?? 1, // Fallback caso condicao_id seja null
                'data_pagamento'        => now(),
                'tipo_documento'        => 'nota_fiscal',
                'user_id'               => auth()->id(),
                'valor_final'           => (float) $this->valor,
                'valor_pago'            => 0, // Inicia como pendente
                'observacoes'           => $this->observacoes ?: null,
                'estornado'             => false,
                'tipo'                  => Pagamento::TIPO_RESIDUAL,
            ]);

            if ($pagamento) {
                \Log::info("Pagamento residual #{$pagamento->id} created with success.");
                
                session()->flash('residual_sucesso', 'Cobrança residual de R$ ' . number_format($this->valor, 2, ',', '.') . ' registrada com sucesso.');
                
                // Recarrega o orçamento para garantir que os relacionamentos reflitam a mudança
                $this->orcamento->refresh();
                
                $this->reset(['valor', 'observacoes']);
                
                // Notifica outros componentes
                $this->dispatch('residualSalvo')->to(OrcamentoShow::class);
                $this->dispatch('refresh');

                // Notificação SweetAlert2
                $this->dispatch('notify-swal', [
                    'icon' => 'success',
                    'title' => 'Sucesso!',
                    'text' => 'Cobrança residual registrada com sucesso.',
                ]);
            } else {
                throw new \Exception("Falha ao criar registro no banco de dados.");
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw para o Livewire mostrar os erros nos campos, mas também avisar via pop-up
            $this->dispatch('notify-swal', [
                'icon' => 'warning',
                'title' => 'Validação',
                'text' => 'Verifique os campos obrigatórios e tente novamente.',
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error("Erro ao salvar cobrança residual: " . $e->getMessage(), [
                'orcamento_id' => $this->orcamento->id,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('residual_erro', 'Erro ao salvar: ' . $e->getMessage());

            // Notificação SweetAlert2 de erro
            $this->dispatch('notify-swal', [
                'icon' => 'error',
                'title' => 'Erro!',
                'text' => 'Não foi possível registrar a cobrança: ' . $e->getMessage(),
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.orcamento-pagamento-residual');
    }
}
