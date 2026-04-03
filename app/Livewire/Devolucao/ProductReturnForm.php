<?php

namespace App\Livewire\Devolucao;

use App\Models\Orcamento;
use App\Models\ProductReturn;
use App\Services\ProductReturnService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ProductReturnForm extends Component
{
    public $search_orcamento;
    public $orcamento;
    public $items_selecionados = [];
    public $quantidades = [];
    public $data_ocorrencia;
    public $nota_fiscal;
    public $romaneio_recebimento;
    public $observacoes;
    public $troca_produto = false;

    protected $rules = [
        'data_ocorrencia' => 'required|date',
        'nota_fiscal' => 'nullable|string|max:100',
        'romaneio_recebimento' => 'nullable|string|max:100',
        'observacoes' => 'nullable|string|max:2000',
        'troca_produto' => 'boolean',
    ];

    public function mount()
    {
        $this->data_ocorrencia = date('Y-m-d');
    }

    public function buscarOrcamento()
    {
        $this->validate([
            'search_orcamento' => 'required'
        ]);

        $this->orcamento = Orcamento::with(['itens.produto', 'cliente'])
            ->where('id', $this->search_orcamento)
            ->whereIn('status', ['Pago', 'Finalizado', 'Concluido'])
            ->first();

        if (!$this->orcamento) {
            $this->addError('search_orcamento', 'Orçamento não encontrado ou ainda não pago.');
            return;
        }

        $this->reset(['items_selecionados', 'quantidades']);
    }

    public function updatedItemsSelecionados()
    {
        /** @var \App\Models\Orcamento|null $orcamento */
        $orcamento = $this->orcamento;
        if (!($orcamento instanceof \App\Models\Orcamento) || !$orcamento->itens) return;
        
        foreach ($this->items_selecionados as $itemId) {
            if (!isset($this->quantidades[$itemId])) {
                $item = $orcamento->itens->find($itemId);
                if ($item) {
                    $this->quantidades[$itemId] = $item->quantidade;
                }
            }
        }
    }

    public function save(ProductReturnService $service)
    {
        $this->authorize('create', ProductReturn::class);
        $this->validate();

        if (empty($this->items_selecionados)) {
            $this->addError('items', 'Selecione pelo menos um item para devolução.');
            return;
        }

        $itemsToReturn = [];
        foreach ($this->items_selecionados as $itemId) {
            $qty = $this->quantidades[$itemId] ?? 0;
            if ($qty <= 0) {
                $this->addError("quantidades.$itemId", 'A quantidade deve ser maior que zero.');
                return;
            }
            $item = $this->orcamento?->itens?->find($itemId);
            if (!$item) continue;

            if ($qty > $item->quantidade) {
                $this->addError("quantidades.$itemId", "A quantidade não pode ser maior que a vendida ({$item->quantidade}).");
                return;
            }
            $itemsToReturn[$itemId] = $qty;
        }

        /** @var \App\Models\Orcamento|null $orcamento */
        $orcamento = $this->orcamento;
        if (!($orcamento instanceof \App\Models\Orcamento)) return;

        try {
            $data = [
                'orcamento_id' => $orcamento->id,
                'data_ocorrencia' => $this->data_ocorrencia,
                'nota_fiscal' => $this->nota_fiscal,
                'romaneio_recebimento' => $this->romaneio_recebimento,
                'observacoes' => $this->observacoes,
                'troca_produto' => $this->troca_produto,
                'items' => $itemsToReturn
            ];

            $return = $service->initiate($data);

            session()->flash('success', "Solicitação de Devolução #{$return->nr} iniciada com sucesso!");
            return redirect()->route('devolucao.dashboard');
            
        } catch (\Exception $e) {
            Log::error("Erro ao iniciar devolução: " . $e->getMessage());
            $this->addError('general', 'Erro ao salvar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.devolucao.product-return-form');
    }
}
