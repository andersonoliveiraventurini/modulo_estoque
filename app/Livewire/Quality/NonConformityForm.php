<?php

namespace App\Livewire\Quality;

use App\Models\NonConformity;
use App\Models\Produto;
use App\Models\Fornecedor;
use App\Services\NonConformityService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class NonConformityForm extends Component
{
    public $rncId;
    public $isEdit = false;

    // Campos do formulário
    public $produto_id;
    public $produto_nome;
    public $fornecedor_id;
    public $fornecedor_nome;
    public $data_ocorrencia;
    public $nota_fiscal;
    public $romaneio_recebimento;
    public $acoes_tomadas;
    public $observacoes;

    protected $rules = [
        'produto_nome' => 'required|string|max:255',
        'fornecedor_nome' => 'required|string|max:255',
        'data_ocorrencia' => 'required|date',
        'nota_fiscal' => 'nullable|string|max:100',
        'romaneio_recebimento' => 'nullable|string|max:100',
        'acoes_tomadas' => 'nullable|string|max:2000',
        'observacoes' => 'nullable|string|max:2000',
    ];

    public function mount($rnc = null)
    {
        if ($rnc) {
            $this->rncId = $rnc instanceof NonConformity ? $rnc->id : $rnc;
            $this->isEdit = true;
            $this->loadRnc();
        } else {
            $this->data_ocorrencia = date('Y-m-d');
        }
    }

    public function loadRnc()
    {
        $rnc = NonConformity::findOrFail($this->rncId);
        $this->produto_id = $rnc->produto_id;
        $this->produto_nome = $rnc->produto_nome;
        $this->fornecedor_id = $rnc->fornecedor_id;
        $this->fornecedor_nome = $rnc->fornecedor_nome;
        $this->data_ocorrencia = $rnc->data_ocorrencia->format('Y-m-d');
        $this->nota_fiscal = $rnc->nota_fiscal;
        $this->romaneio_recebimento = $rnc->romaneio_recebimento;
        $this->acoes_tomadas = $rnc->acoes_tomadas;
        $this->observacoes = $rnc->observacoes;
    }

    public function save(NonConformityService $service)
    {
        $data = $this->validate();
        $data['produto_id'] = $this->produto_id;
        $data['fornecedor_id'] = $this->fornecedor_id;

        try {
            if ($this->isEdit) {
                $rnc = NonConformity::findOrFail($this->rncId);
                $service->update($rnc, $data);
                session()->flash('success', 'RNC atualizada com sucesso!');
            } else {
                $rnc = $service->store($data);
                session()->flash('success', "RNC #{$rnc->nr} criada com sucesso!");
            }

            return redirect()->route('quality.dashboard');
        } catch (\Exception $e) {
            Log::error("Erro ao salvar RNC: " . $e->getMessage());
            $this->addError('general', 'Ocorreu um erro ao salvar a RNC. Verifique os dados e tente novamente.');
        }
    }

    public function render()
    {
        return view('livewire.quality.non-conformity-form');
    }
}
