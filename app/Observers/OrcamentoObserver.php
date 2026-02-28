<?php

namespace App\Observers;

use App\Models\Orcamento;
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
}
