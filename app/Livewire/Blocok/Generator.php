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
            
            // Usar navigate: true para uma transição suave no Livewire 3
            return $this->redirect(route('blocok.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Ocorreu um erro ao gerar o arquivo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.blocok.generator');
    }
}
