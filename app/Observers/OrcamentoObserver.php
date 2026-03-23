<?php

namespace App\Observers;

use App\Models\Orcamento;
use App\Events\OrcamentoAprovado;
use App\Events\OrcamentoCancelado;
use App\Events\OrcamentoFinalizado;
use Illuminate\Support\Facades\Storage;

class OrcamentoObserver
{
    public function updating(Orcamento $orcamento): void
    {
        // ✅ Sempre que status voltar para Pendente, deleta PDF
        if ($orcamento->isDirty('status') && $orcamento->status === 'Pendente') {
            if ($orcamento->pdf_path) {
                Storage::disk('public')->delete($orcamento->pdf_path);
                $orcamento->pdf_path = null;
            }
        }
    }

    public function updated(Orcamento $orcamento): void
    {
        \Log::info("OrcamentoObserver@updated chamado para Orçamento #{$orcamento->id}. Status: {$orcamento->status}. Mudou status: " . ($orcamento->wasChanged('status') ? 'Sim' : 'Não'));

        if ($orcamento->wasChanged('status')) {
            if ($orcamento->isAprovado()) {
                \Log::info("Disparando OrcamentoAprovado para Orçamento #{$orcamento->id}");
                OrcamentoAprovado::dispatch($orcamento);
            }

            if ($orcamento->isCancelado()) {
                \Log::info("Disparando OrcamentoCancelado para Orçamento #{$orcamento->id}");
                OrcamentoCancelado::dispatch($orcamento);
            }

            if ($orcamento->isFinalizado()) {
                \Log::info("Disparando OrcamentoFinalizado para Orçamento #{$orcamento->id}");
                OrcamentoFinalizado::dispatch($orcamento);
            }
        }
    }
}
