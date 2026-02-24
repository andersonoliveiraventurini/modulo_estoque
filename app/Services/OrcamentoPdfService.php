<?php

namespace App\Services;

use App\Models\Orcamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrcamentoPdfService
{
    public function gerarOrcamentoPdf(Orcamento $orcamento): bool
    {
        try {
            // 1. TOKEN E LINK SEGURO
            $token = Str::uuid();
            $tokenExpiraEm = Carbon::now()->addDays(2);
            $linkSeguro = route('orcamentos.view', ['token' => $token]);

            // 2. QR CODE
            $qrCodeBase64 = base64_encode(
                QrCode::format('png')
                    ->size(130)
                    ->margin(1)
                    ->generate($linkSeguro)
            );

            // 3. PDF
            $pdf = Pdf::loadView('documentos_pdf.orcamento', [
                'orcamento'  => $orcamento,
                'qrCode'     => $qrCodeBase64,
                'linkSeguro' => $linkSeguro,
                'versao'     => $orcamento->versao ?? 1,
            ])->setPaper('a4');

            // 4. NUMERAÇÃO DE PÁGINAS
            $canvas = $pdf->getDomPDF()->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $text = "Página $pageNumber / $pageCount";
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $canvas->text(270, 820, $text, $font, 10);
            });

            // 5. SALVAR PDF -  Nome com versão garante que o browser sempre baixa o arquivo novo
            $path = "orcamentos/orcamento_{$orcamento->id}_v{$orcamento->versao}.pdf";
            Storage::disk('public')->put($path, $pdf->output());

            // 6. ATUALIZA ORÇAMENTO
            if (Storage::disk('public')->exists($path)) {
                $orcamento->update([
                    'token_acesso'     => $token,
                    'token_expira_em'  => $tokenExpiraEm,
                    'pdf_path'         => $path,
                ]);
                return true;
            }

            Log::error("Falha ao salvar PDF: {$path}");
            return false;

        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF do orçamento #{$orcamento->id}: {$e->getMessage()}");
            return false;
        }
    }
}
