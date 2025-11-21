<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultaPrecoRequest;
use App\Http\Requests\UpdateConsultaPrecoRequest;
use App\Models\Cliente;
use App\Models\ConsultaPreco;
use App\Models\Cor;
use App\Models\Fornecedor;
use App\Models\Orcamento;

// classes para a geração de PDF e QR Code
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConsultaPrecoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $precos = ConsultaPreco::all();
        return view('paginas.produtos.consulta_precos.index', compact('precos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function criar_cotacao($cliente_id)
    {
        $cliente = Cliente::findOrFail($cliente_id);
        $fornecedores = Fornecedor::all();
        $cores = Cor::orderBy('nome')->get();
        $orcamentos = Orcamento::where('status', '<>', 'Aprovado')->where('status', '<>', 'Cancelado')->get();
        return view('paginas.produtos.consulta_precos.create', compact('fornecedores', 'cores', 'orcamentos', 'cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConsultaPrecoRequest $request)
    {
        $request->merge(['usuario_id' => auth()->id()]);
        $consultaPreco = ConsultaPreco::create($request->except('_token'));
        return redirect()->route('consulta_preco.show', $consultaPreco)->with('success', 'Consulta de Preço criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultaPreco $consulta)
    {
        return view('paginas.produtos.consulta_precos.show', compact('consulta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($consult_id)
    {
        $consulta = ConsultaPreco::findOrFail($consult_id);
        $fornecedores = Fornecedor::all();
        $cores = Cor::orderBy('nome')->get();
        $orcamentos = Orcamento::where('status', '<>', 'Aprovado')->where('status', '<>', 'Cancelado')->get();
        return view('paginas.produtos.consulta_precos.edit', compact('consulta', 'fornecedores', 'cores', 'orcamentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConsultaPrecoRequest $request, $consulta_id)
    {
        $consultaPreco = ConsultaPreco::findOrFail($consulta_id);
        $request->merge([
            'preco_compra' => str_replace(['.', ','], ['', '.'], $request->preco_compra),
            'preco_venda' => str_replace(['.', ','], ['', '.'], $request->preco_venda),
            'comprador_id' => auth()->id(),
        ]);
        $consultaPreco->update($request->except('_token', '_method'));

        $pdfGeradoComSucesso = $this->gerarCotacaoPdf($consultaPreco);
        
        if ($pdfGeradoComSucesso) {
            return redirect()
                ->route('consulta_preco.show', $consultaPreco->id)
                ->with('success', 'Consulta de Preço atualizada e PDF gerado com sucesso!');
        }

        return redirect()
            ->route('consulta_preco.show', $consultaPreco->id)
            ->with('error', 'Consulta de Preço atualizada com sucesso, mas ocorreu uma falha ao gerar o PDF. Por favor, contate o suporte.');
    }

    private function gerarCotacaoPdf(ConsultaPreco $cotacao): bool
    {
    
            // 1. CARREGAR RELACIONAMENTOS NECESSÁRIOS
            //$cotacao->load(['cliente', 'usuario', 'fornecedor', 'comprador']);

            // 2. GERAÇÃO DE TOKEN E LINK SEGURO
            $token = Str::uuid();
            $tokenExpiraEm = Carbon::now()->addDays(2);
            $linkSeguro = route('cotacoes.view', ['token' => $token]);

            // 3. GERAÇÃO DO QR CODE
            $qrCodeBase64 = base64_encode(
                QrCode::format('png')
                    ->size(130)
                    ->margin(1)
                    ->generate($linkSeguro)
            );

            // 4. PREPARAR DADOS PARA O PDF
            $dadosPdf = [
                'cotacao' => $cotacao,
                'qrCode' => $qrCodeBase64,
                'linkSeguro' => $linkSeguro,
                'versao' => $cotacao->versao ?? 1,
            ];

            // 5. GERAÇÃO DO PDF
            $pdf = Pdf::loadView('documentos_pdf.cotacao', $dadosPdf)
                ->setPaper('a4');

            // 6. NUMERAÇÃO DE PÁGINAS
            $canvas = $pdf->getDomPDF()->getCanvas();
            $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
                $text = "Página $pageNumber / $pageCount";
                $font = $fontMetrics->get_font("Helvetica", "normal");
                $canvas->text(270, 820, $text, $font, 10);
            });

            // 7. SALVAMENTO DO ARQUIVO
            $nomeArquivo = "cotacao_{$cotacao->id}_v{$cotacao->versao}.pdf";
            $path = "cotacoes/{$nomeArquivo}";

            // Garantir que o diretório existe
            Storage::disk('public')->makeDirectory('cotacoes');

            // Salvar o PDF
            Storage::disk('public')->put($path, $pdf->output());

            // 8. VERIFICAÇÃO E ATUALIZAÇÃO FINAL
            if (Storage::disk('public')->exists($path)) {
                $cotacao->update([
                    'token_acesso' => $token,
                    'token_expira_em' => $tokenExpiraEm,
                    'pdf_path' => $path,
                ]);

                Log::info("PDF gerado com sucesso para cotação #{$cotacao->id}", [
                    'path' => $path,
                    'versao' => $cotacao->versao
                ]);

                return true;
            } else {
                Log::error("Falha ao salvar o PDF no caminho: {$path}");
                return false;
            }
try{

        } catch (\Exception $e) {
            Log::error("Erro fatal ao gerar PDF para cotação #{$cotacao->id}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Método auxiliar para visualizar a cotação via token
     */
    public function visualizarCotacao($token)
    {
        try {
            $cotacao = ConsultaPreco::where('token_acesso', $token)
                ->where('token_expira_em', '>', Carbon::now())
                ->firstOrFail();

            if (!$cotacao->pdf_path || !Storage::disk('public')->exists($cotacao->pdf_path)) {
                // Regenerar PDF se não existir
                if (!$this->gerarCotacaoPdf($cotacao)) {
                    abort(500, 'Erro ao gerar PDF da cotação');
                }
            }

            $pdfPath = Storage::disk('public')->path($cotacao->pdf_path);

            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="cotacao_' . $cotacao->id . '.pdf"'
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao visualizar cotação", [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            abort(404, 'Cotação não encontrada ou token expirado');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($consulta_id)
    {
        $consultaPreco = ConsultaPreco::findOrFail($consulta_id);
        $consultaPreco->delete();
        return redirect()->route('consulta_preco.index')->with('success', 'Consulta de Preço excluída com sucesso.');
    }
}
