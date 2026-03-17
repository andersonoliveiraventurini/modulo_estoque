<?php

namespace App\Http\Controllers\Estoque;

use App\Http\Controllers\Controller;
use App\Models\OrdemReposicao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReposicaoPdfController extends Controller
{
    /**
     * Gera o PDF da Ordem de Reposição.
     */
    public function __invoke(OrdemReposicao $ordem)
    {
        $ordem->load(['produto', 'solicitadoPor', 'executor', 'armazemOrigem', 'corredorOrigem', 'posicaoOrigem']);

        $pdf = Pdf::loadView('pdfs.reposicao-form', compact('ordem'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Ordem_Reposicao_{$ordem->id}.pdf");
    }
}
