<?php

namespace App\Http\Controllers;

use App\Models\PickingBatch;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class EtiquetaController extends Controller
{
    /**
     * Gera um PDF com etiquetas correspondentes aos volumes separados.
     */
    public function gerarEtiquetas(PickingBatch $batch)
    {
        // Garante que o batch tem os dados carregados (orçamento e cliente)
        $batch->loadMissing(['orcamento.cliente', 'orcamento.endereco']);

        if ($batch->status !== 'concluido' && $batch->status !== 'em_separacao') {
             return back()->with('error', 'Etiquetas só podem ser geradas para lotes concluídos ou já em separação.');
        }

        // Calcula quantos volumes totais foram embalados
        // Se as quantidades via formulario forem zero, e o lote for velho, garantimos ao menos 1 volume como fallback
        $totalVolumes = ($batch->qtd_caixas ?? 0) + ($batch->qtd_sacos ?? 0) + ($batch->qtd_sacolas ?? 0);
        
        // Trata os 'outros volumes' se preenchido. (Aqui assumimos cada entrada textual não-numérica como 1 extra bundle caso vazio os numericos)
        if ($totalVolumes === 0) {
            $totalVolumes = $batch->outros_embalagem ? 1 : 1; 
        }

        $etiquetas = [];
        for ($i = 1; $i <= $totalVolumes; $i++) {
            $etiquetas[] = [
                'current' => $i,
                'total' => $totalVolumes,
                'batch_id' => $batch->id,
                'orcamento_id' => $batch->orcamento_id,
                'cliente_nome' => $batch->orcamento->cliente->nome,
                'roteiro' => $batch->orcamento->endereco->roteiro ?? 'Não especificado',
                'vendedor' => $batch->orcamento->vendedor->name ?? 'N/A',
                'data' => now()->format('d/m/Y H:i')
            ];
        }

        Log::info("Gerando PDF de {$totalVolumes} etiquetas para Lote {$batch->id}");

        $pdf = Pdf::loadView('paginas.separacao.etiqueta_pdf', compact('etiquetas', 'batch'))
            ->setPaper('a6', 'landscape'); // A6 é comum para etiquetas (aprox 10x15cm)

        return $pdf->stream("etiquetas-lote-{$batch->id}.pdf");
    }
}
