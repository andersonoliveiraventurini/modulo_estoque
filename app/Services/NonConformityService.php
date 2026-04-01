<?php

namespace App\Services;

use App\Models\NonConformity;
use Illuminate\Support\Facades\DB;

class NonConformityService
{
    /**
     * Gera o próximo número de referência para RNC
     * Formato: RNC-{ANO}-{SEQUENCIAL}
     */
    public function generateNextNr()
    {
        $year = date('Y');
        $lastRnc = NonConformity::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastRnc ? (int) substr($lastRnc->nr, -4) + 1 : 1;
        
        return 'RNC-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['nr'] = $this->generateNextNr();
            $data['usuario_id'] = auth()->id();
            
            $rnc = NonConformity::create($data);
            
            // Se marcado para baixar estoque
            if ($rnc->baixar_estoque && $rnc->produto_id && $rnc->quantidade > 0) {
                app(EstoqueService::class)->baixarRnc(
                    $rnc->produto,
                    (float) $rnc->quantidade,
                    "RNC #{$rnc->nr}: {$rnc->observacoes}",
                    $rnc->armazem_id
                );
            }
            
            // Gerar PDF automático
            app(QualityPdfService::class)->generateRncPdf($rnc);
            
            return $rnc;
        });
    }

    public function update(NonConformity $rnc, array $data)
    {
        return $rnc->update($data);
    }
}
