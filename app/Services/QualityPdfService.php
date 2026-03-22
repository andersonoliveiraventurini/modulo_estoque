<?php

namespace App\Services;

use App\Models\NonConformity;
use App\Models\ProductReturn;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class QualityPdfService
{
    public function generateRncPdf(NonConformity $rnc)
    {
        $pdf = Pdf::loadView('documentos_pdf.quality.rnc', compact('rnc'));
        $fileName = "quality/rnc_{$rnc->nr}.pdf";
        Storage::disk('public')->put($fileName, $pdf->output());
        return $fileName;
    }

    public function generateReturnPdf(ProductReturn $return, string $type = 'solicited')
    {
        // $type: solicited, authorized, exchange
        $view = "documentos_pdf.quality.return_{$type}";
        $return->load(['items.produto', 'cliente', 'vendedor', 'orcamento', 'authorizations.user']);
        
        $pdf = Pdf::loadView($view, compact('return'));
        $fileName = "quality/return_{$type}_{$return->nr}.pdf";
        
        Storage::disk('public')->put($fileName, $pdf->output());
        return $fileName;
    }
}
