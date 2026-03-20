<?php

namespace App\Services;

use App\Models\Pagamento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PagamentoPdfService
{
    public function gerar(Pagamento $pagamento): bool
    {
        $tag = "PagamentoPdfService [pagamento#{$pagamento->id}]";
 
        Log::info("{$tag} → iniciando geração do PDF");
 
        try {
            // ── 1. Carrega relacionamentos ────────────────────────────────
            $pagamento->loadMissing([
                'formas.condicaoPagamento',
                'condicaoPagamento',
                'routeBillingAttachments',
                'orcamento.cliente',
                'orcamento.vendedor',
                'orcamento.itens.produto',
                'orcamento.condicaoPagamento',
                'pedido.cliente',
                'pedido.vendedor',
                'pedido.itens.produto',
                'pedido.condicaoPagamento',
                'user',
                'usuarioEstorno',
            ]);
 
            Log::info("{$tag} → relacionamentos carregados", [
                'orcamento_id' => $pagamento->orcamento_id,
                'pedido_id'    => $pagamento->pedido_id,
                'formas'       => $pagamento->formas->count(),
            ]);
 
            // ── 2. Resolve o registro vinculado ───────────────────────────
            [$registro, $tipoRegistro] = $this->resolverRegistro($pagamento);
 
            Log::info("{$tag} → registro resolvido", [
                'tipo'    => $tipoRegistro,
                'id'      => $registro->id,
                'cliente' => $registro->cliente->nome ?? 'N/A',
                'itens'   => $registro->itens->count(),
            ]);
 
            // ── 3. Renderiza a view ───────────────────────────────────────
            Log::info("{$tag} → renderizando view [documentos_pdf.pagamento]");
 
            $pdf = Pdf::loadView('documentos_pdf.pagamento', [
                'pagamento'    => $pagamento,
                'registro'     => $registro,
                'tipoRegistro' => $tipoRegistro,
            ])->setPaper('a4');
 
            Log::info("{$tag} → view renderizada");
 
            // ── 4. Numeração de páginas ───────────────────────────────────
            $this->adicionarNumeracaoPaginas($pdf);
 
            // ── 5. Output em memória ──────────────────────────────────────
            $output       = $pdf->output();
            $tamanhoBytes = strlen($output);
 
            Log::info("{$tag} → PDF gerado em memória", [
                'tamanho_bytes' => $tamanhoBytes,
                'tamanho_kb'    => round($tamanhoBytes / 1024, 2),
            ]);
 
            if ($tamanhoBytes < 100) {
                Log::error("{$tag} → PDF suspeito: tamanho muito pequeno ({$tamanhoBytes} bytes)");
                return false;
            }
 
            // ── 6. Persiste no disco ──────────────────────────────────────
            $path        = "pagamentos/comprovante_{$pagamento->id}.pdf";
            $discoRaiz   = Storage::disk('public')->path('');
 
            Log::info("{$tag} → salvando arquivo", [
                'path'       => $path,
                'disco_raiz' => $discoRaiz,
                'caminho_abs'=> $discoRaiz . $path,
            ]);
 
            $salvo = Storage::disk('public')->put($path, $output);
 
            Log::info("{$tag} → resultado do Storage::put", ['retorno' => $salvo]);
 
            $existe = Storage::disk('public')->exists($path);
            Log::info("{$tag} → arquivo existe no disco?", ['existe' => $existe]);
 
            if (! $existe) {
                Log::error("{$tag} → arquivo não encontrado após put — verifique permissões em storage/app/public");
                return false;
            }
 
            Log::info("{$tag} → tamanho real no disco", [
                'bytes' => Storage::disk('public')->size($path),
            ]);
 
            // ── 7. Atualiza o banco ───────────────────────────────────────
            Log::info("{$tag} → gravando pdf_path no banco");
 
            $linhasAfetadas = \Illuminate\Support\Facades\DB::table('pagamentos')
                ->where('id', $pagamento->id)
                ->update(['pdf_path' => $path]);
 
            Log::info("{$tag} → DB::update executado", ['linhas_afetadas' => $linhasAfetadas]);
 
            // Recarrega para confirmar
            $pagamento->refresh();
 
            Log::info("{$tag} → pdf_path após refresh", ['pdf_path' => $pagamento->pdf_path]);
 
            if ($pagamento->pdf_path !== $path) {
                Log::error("{$tag} → DIVERGÊNCIA: banco não reflete o valor salvo", [
                    'esperado' => $path,
                    'atual'    => $pagamento->pdf_path,
                ]);
                return false;
            }
 
            Log::info("{$tag} → PDF gerado e salvo com SUCESSO ✓");
            return true;
 
        } catch (\Throwable $e) {
            Log::error("{$tag} → EXCEÇÃO", [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => collect(explode("\n", $e->getTraceAsString()))
                    ->take(20)
                    ->implode("\n"),
            ]);
            return false;
        }
    }
 
    /**
     * @return array{0: \App\Models\Orcamento|\App\Models\Pedido, 1: string}
     */
    private function resolverRegistro(Pagamento $pagamento): array
    {
        if ($pagamento->orcamento) {
            return [$pagamento->orcamento, 'orcamento'];
        }
 
        if ($pagamento->pedido) {
            return [$pagamento->pedido, 'pedido'];
        }
 
        throw new \RuntimeException(
            "Pagamento #{$pagamento->id} não possui orçamento nem pedido vinculado."
        );
    }
 
    private function adicionarNumeracaoPaginas(mixed $pdf): void
    {
        $canvas = $pdf->getDomPDF()->getCanvas();
 
        $canvas->page_script(function (int $pageNumber, int $pageCount, $canvas, $fontMetrics) {
            $font = $fontMetrics->get_font('Helvetica', 'normal');
            $canvas->text(270, 820, "Página {$pageNumber} / {$pageCount}", $font, 9);
        });
    }
}