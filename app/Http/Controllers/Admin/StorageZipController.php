<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class StorageZipController extends Controller
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        // Somente usuários admin podem ter acesso a isso
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $paths = collect($request->input('paths', []))
            ->filter(fn($p) => ! str_contains($p, '..'))
            ->filter(fn($p) => str_ends_with(strtolower($p), '.pdf'))
            ->filter(fn($p) => Storage::disk('public')->exists($p))
            ->values()
            ->toArray();

        abort_if(empty($paths), 400, 'Nenhum arquivo válido selecionado.');

        Log::info('Gerenciador de Arquivos: Baixou ZIP', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'count' => count($paths),
            'files' => $paths
        ]);

        $tmpDir  = storage_path('app/tmp');
        $zipName = 'backup-pdfs-' . now()->format('Y-m-d-His') . '.zip';
        $zipPath = $tmpDir . '/' . $zipName;

        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($paths as $path) {
            $absolutePath = Storage::disk('public')->path($path);
            // Preserva subpasta no ZIP (ex: orcamentos/orcamento_1.pdf)
            $zip->addFile($absolutePath, $path);
        }

        $zip->close();

        return response()
            ->download($zipPath, $zipName)
            ->deleteFileAfterSend(true);
    }
}
