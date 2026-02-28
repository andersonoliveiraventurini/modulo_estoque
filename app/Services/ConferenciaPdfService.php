<?php

namespace App\Services;

use App\Models\Conferencia;
use App\Models\Orcamento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConferenciaPdfService
{
    /**
     * Gera o relatório PDF de todas as conferências de um orçamento.
     *
     * Salva em: storage/app/public/conferencias/conferencia_orcamento_{id}.pdf
     * Retorna o path relativo (disco public) ou null em caso de falha.
     */
    public function gerarRelatorioPdf(Orcamento $orcamento): ?string
    {
        try {
            // 1. Carrega TODAS as conferências do orçamento com relacionamentos necessários
            $conferencias = Conferencia::with([
                'conferente',
                'itens.produto',
                'itens.conferidoPor',
                'itens.fotos',
                'itens.consultaPreco.fornecedorSelecionado.fornecedor', // ✅
            ])
                ->where('orcamento_id', $orcamento->id)
                ->orderBy('created_at')
                ->get();

            if ($conferencias->isEmpty()) {
                Log::warning("Nenhuma conferência encontrada para orçamento #{$orcamento->id}");
                return null;
            }

            // 2. Converte cada foto para base64 para embed no PDF
            //    (DomPDF não acessa URLs públicas em produção; embed é mais seguro)
            foreach ($conferencias as $conf) {
                foreach ($conf->itens as $item) {
                    foreach ($item->fotos as $foto) {
                        $foto->base64 = $this->fotoParaBase64($foto->path, $foto->disk);
                    }
                }
            }

            // 3. Gera o PDF
            $pdf = Pdf::loadView('documentos_pdf.conferencia-relatorio', [
                'orcamento'    => $orcamento,
                'conferencias' => $conferencias,
                'geradoEm'     => now()->format('d/m/Y H:i'),
            ])->setPaper('a4');

            // 4. Numeração de páginas
            $canvas = $pdf->getDomPDF()->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $text = "Página $pageNumber / $pageCount";
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $canvas->text(270, 820, $text, $font, 10);
            });

            // 5. Salva no disco
            $path = "conferencias/conferencia_orcamento_{$orcamento->id}.pdf";
            Storage::disk('public')->put($path, $pdf->output());

            if (!Storage::disk('public')->exists($path)) {
                Log::error("Falha ao salvar PDF de conferência: {$path}");
                return null;
            }

            return $path;

        } catch (\Exception $e) {
            Log::error("Erro ao gerar PDF de conferência do orçamento #{$orcamento->id}: {$e->getMessage()}");
            return null;
        }
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Lê a foto do disco e retorna string base64 com data URI pronta para <img src="">.
     * Retorna null se o arquivo não existir.
     */
    private function fotoParaBase64(string $path, string $disk = 'public'): ?string
    {
        try {
            if (!Storage::disk($disk)->exists($path)) {
                return null;
            }

            $conteudo  = Storage::disk($disk)->get($path);
            $mime      = Storage::disk($disk)->mimeType($path) ?? 'image/jpeg';

            return 'data:' . $mime . ';base64,' . base64_encode($conteudo);

        } catch (\Exception $e) {
            Log::warning("Não foi possível ler foto para PDF: {$path} — {$e->getMessage()}");
            return null;
        }
    }
}
