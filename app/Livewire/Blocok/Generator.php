<?php

namespace App\Livewire\Blocok;

use App\Services\BlocokService;
use Carbon\Carbon;
use Livewire\Component;

class Generator extends Component
{
    public $data_inicio;
    public $data_fim;

    public function mount()
    {
        $this->data_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->data_fim = now()->endOfMonth()->format('Y-m-d');
    }

    public function gerar(BlocokService $service)
    {
        $this->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        try {
            $bloco = $service->gerarRegistrosParaPeriodo(
                Carbon::parse($this->data_inicio),
                Carbon::parse($this->data_fim)
            );

            $service->exportarTxt($bloco);

            session()->flash('success', 'Arquivo Bloco K gerado com sucesso!');
            return redirect()->route('blocok.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao gerar Bloco K: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.blocok.generator');
    }
}
