<?php

use App\Models\Orcamento;
use App\Models\RouteBillingAttachment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class RouteBillingAttach extends Component
{
    use WithFileUploads;

    public Orcamento $orcamento;
    public $files = [];
    public $notes = '';
    public $file_type = 'payment_proof';

    protected $rules = [
        'files.*' => 'required|file|max:4096', // 4MB
        'notes' => 'nullable|string|max:500',
        'file_type' => 'required|string',
    ];

    public function mount(Orcamento $orcamento)
    {
        $this->orcamento = $orcamento;
    }

    public function save()
    {
        if (empty($this->files)) {
            $this->addError('files', 'Selecione ao menos um arquivo.');
            return;
        }

        $this->validate();

        foreach ($this->files as $file) {
            // Salva o arquivo no disco public
            $path = $file->store('route_billing/' . $this->orcamento->id, 'public');

            RouteBillingAttachment::create([
                'orcamento_id' => $this->orcamento->id,
                'user_id' => Auth::id(),
                'file_path' => $path,
                'file_type' => $this->file_type,
                'notes' => $this->notes,
            ]);
        }

        $this->reset(['files', 'notes']);
        
        $this->dispatch('notify', 'Comprovante(s) anexado(s) com sucesso!');
        $this->dispatch('refresh'); // Para o componente pai (OrcamentoShow)
    }

    public function render()
    {
        return view('livewire.route-billing-attach');
    }
}
