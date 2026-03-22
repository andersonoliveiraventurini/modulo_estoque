<?php

namespace App\Livewire\Quality;

use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use App\Models\ProductReturn;
use App\Models\User;
use App\Services\ProductReturnService;
use Livewire\Component;

class ProductReturnForm extends Component
{
    public $returnId;
    public $isEdit = false;

    // Search fields
    public $searchOrcamento = '';
    public $showOrcamentoSearch = false;
    
    // Header fields
    public $orcamento_id;
    public $orcamento_nr;
    public $cliente_nome;
    public $vendedor_nome;
    
    // Form fields
    public $data_ocorrencia;
    public $nota_fiscal;
    public $romaneio_recebimento;
    public $observacoes;
    public $troca_produto = false;

    // Items selection
    public $orcamento_items = [];
    public $items_to_return = []; // [item_id => quantity]

    protected $rules = [
        'orcamento_id' => 'required|exists:orcamentos,id',
        'data_ocorrencia' => 'required|date',
        'nota_fiscal' => 'nullable|string',
        'romaneio_recebimento' => 'nullable|string',
        'observacoes' => 'nullable|string',
        'troca_produto' => 'boolean',
        'items_to_return' => 'required|array|min:1',
    ];

    public function mount($return = null)
    {
        $this->data_ocorrencia = date('Y-m-d');
        
        if ($return) {
            $this->isEdit = true;
            $this->returnId = $return;
            $model = ProductReturn::with('items')->findOrFail($return);
            $this->fill($model->toArray());
            $this->orcamento_id = $model->orcamento_id;
            $this->orcamento_nr = $model->orcamento->id; // Usando ID como NR se não houver NR específico
            $this->loadOrcamento($this->orcamento_id);
            
            foreach ($model->items as $item) {
                $this->items_to_return[$item->orcamento_item_id] = $item->quantidade;
            }
        }
    }

    public function selectOrcamento($id)
    {
        $this->loadOrcamento($id);
        $this->showOrcamentoSearch = false;
        $this->searchOrcamento = '';
    }

    protected function loadOrcamento($id)
    {
        $orcamento = Orcamento::with(['cliente', 'vendedor', 'itens.produto'])->findOrFail($id);
        $this->orcamento_id = $orcamento->id;
        $this->orcamento_nr = $orcamento->id;
        $this->cliente_nome = $orcamento->cliente->nome;
        $this->vendedor_nome = $orcamento->vendedor->name ?? 'N/A';
        $this->orcamento_items = $orcamento->itens;
        
        // Inicializa itens com quantidade 0
        foreach ($this->orcamento_items as $item) {
            if (!isset($this->items_to_return[$item->id])) {
                $this->items_to_return[$item->id] = 0;
            }
        }
    }

    public function save(ProductReturnService $service)
    {
        // Filtra apenas itens com quantidade > 0
        $filteredItems = array_filter($this->items_to_return, fn($q) => $q > 0);
        $this->items_to_return = $filteredItems;

        $this->validate();

        $data = [
            'orcamento_id' => $this->orcamento_id,
            'items' => $this->items_to_return,
            'data_ocorrencia' => $this->data_ocorrencia,
            'nota_fiscal' => $this->nota_fiscal,
            'romaneio_recebimento' => $this->romaneio_recebimento,
            'observacoes' => $this->observacoes,
            'troca_produto' => $this->troca_produto,
        ];

        try {
            if ($this->isEdit) {
                // Para edição, talvez seja mais complexo. Por enquanto, só criação.
                session()->flash('error', 'Edição de devolução não implementada nesta versão.');
            } else {
                $model = $service->initiate($data);
                session()->flash('success', "Devolução #{$model->nr} solicitada com sucesso! Aguardando aprovação do Supervisor.");
            }

            return redirect()->route('quality.dashboard');
        } catch (\Exception $e) {
            $this->addError('items', $e->getMessage());
        }
    }

    public function render()
    {
        $orcamentos = [];
        if ($this->showOrcamentoSearch && strlen($this->searchOrcamento) >= 1) {
            // Busca orçamentos pagos. Idealmente teria um scope 'pagos'. 
            // Vou usar uma lógica simples de busca por ID ou Cliente.
            $orcamentos = Orcamento::where('id', 'like', "%{$this->searchOrcamento}%")
                ->orWhereHas('cliente', fn($q) => $q->where('nome', 'like', "%{$this->searchOrcamento}%"))
                ->limit(10)
                ->get()
                ->filter(fn($o) => $o->pagamentoFinalizado()); // Apenas pagos
        }

        return view('livewire.quality.product-return-form', [
            'orcamentos' => $orcamentos,
        ])->layout('components.layouts.app');
    }
}
