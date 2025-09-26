<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrcamentoRequest;
use App\Http\Requests\UpdateOrcamentoRequest;
use App\Models\Cliente;
use App\Models\Cor;
use App\Models\Endereco;
use App\Models\Fornecedor;
use App\Models\Orcamento;
use App\Models\Produto;
use App\Models\OrcamentoItem;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class OrcamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('paginas.orcamentos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    public function clienteOrcamento($cliente_id)
    {        
        $cliente = Cliente::find($cliente_id);
        if (!$cliente) {
            return redirect()->route('clientes.index')->with('error', 'Cliente não encontrado.');
        }
        return view('paginas.orcamentos.cliente_index', compact('cliente'));
    }


    public function criarOrcamento($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.orcamentos.create', compact('produtos', 'cliente', 'fornecedores', 'cores'));

    }
    public function criarOrcamentoTeste($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.orcamentos.create_teste', compact('produtos', 'cliente', 'fornecedores', 'cores'));

    }

    public function criarOrcamentoRapido($cliente_id)
    {
        $cliente = Cliente::find($cliente_id);
        $produtos = Produto::all();
        $fornecedores = Fornecedor::orderBy('nome_fantasia')->get();
        $cores = Cor::orderBy('nome')->get();
        return view('paginas.orcamentos.create_rapido', compact('produtos', 'cliente', 'fornecedores', 'cores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrcamentoRequest $request)
    {
        // Criação do orçamento (sem endereço ainda)
        $orcamento = Orcamento::create([
            'cliente_id'   => $request->cliente_id,
            'vendedor_id'  => $request->vendedor_id,
            'obra'         => $request->nome_obra,
            'valor_total'  => $request->valor_total,
            'status'       => 'pendente',
            'observacoes'  => $request->observacoes,
            'validade'     => Carbon::now()->addDays(2), // sempre +2 dias
        ]);

        // Criação dos itens
        foreach ($request->itens as $item) {
            $orcamento->itens()->create([
                'produto_id'         => $item['id'],
                'quantidade'         => $item['quantidade'] ?? 0,
                'valor_unitario'     => $item['preco_unitario'] ?? 0,
                'desconto'           => ($item['preco_unitario'] ?? 0) - ($item['preco_unitario_com_desconto'] ?? 0),
                'valor_com_desconto' => $item['subtotal_com_desconto'] ?? $item['subtotal'],
                'user_id'            => $request->user()->id ?? null,
            ]);
        }

        // Se o request trouxe endereço de entrega → cria/atualiza
        if ($request->filled('endereco_cep')) {
            $endereco = Endereco::updateOrCreate(
                [
                    'tipo'       => 'entrega',
                    'cliente_id' => $request->cliente_id,
                ],
                array_filter([
                    'cep'        => $request->endereco_cep,
                    'logradouro' => $request->endereco_logradouro,
                    'numero'     => $request->endereco_numero,
                    'complemento'=> $request->endereco_compl,
                    'bairro'     => $request->endereco_bairro,
                    'cidade'     => $request->endereco_cidade,
                    'estado'     => $request->endereco_estado,
                    'tipo'       => 'entrega',
                ])
            );

            // vincula o endereço ao orçamento
            $orcamento->update(['endereco_id' => $endereco->id]);
        }

        // Gerar PDF e salvar no storage
        $pdf = Pdf::loadView('documentos_pdf.orcamento', compact('orcamento'))
                ->setPaper('a4');

        $path = "orcamentos/orcamento_{$orcamento->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        // Testa se o PDF foi realmente salvo
        if (Storage::disk('public')->exists($path)) {
            $orcamento->update(['pdf_path' => $path]);
        } else {
            // aqui você pode lançar exceção, logar erro ou apenas avisar
            return redirect()
                ->route('orcamentos.index')
                ->with('error', 'Orçamento criado, mas falha ao gerar o PDF.');
        }

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento criado com sucesso!');
    }


// ...

    public function duplicar($id)
    {
        $orcamentoOriginal = Orcamento::with('itens')->findOrFail($id);

        // Novo nome da obra com data e hora
        $dataHora = Carbon::now()->format('d/m/Y H:i');
        $novaObra = "{$dataHora} - {$orcamentoOriginal->obra}";

        // Criar novo orçamento (sem endereço)
        $novoOrcamento = Orcamento::create([
            'cliente_id'   => $orcamentoOriginal->cliente_id,
            'vendedor_id'  => $orcamentoOriginal->vendedor_id,
            'obra'         => $novaObra,
            'valor_total'  => $orcamentoOriginal->valor_total,
            'status'       => 'pendente',
            'observacoes'  => $orcamentoOriginal->observacoes,
            'validade'     => Carbon::now()->addDays(2),
        ]);

        // Copiar os itens
        foreach ($orcamentoOriginal->itens as $item) {
            $novoOrcamento->itens()->create([
                'produto_id'         => $item->produto_id,
                'quantidade'         => $item->quantidade,
                'valor_unitario'     => $item->valor_unitario,
                'desconto'           => $item->desconto,
                'valor_com_desconto' => $item->valor_com_desconto,
                'user_id'            => $item->user_id,
            ]);
        }

        // Gerar PDF e salvar no storage
        $pdf = Pdf::loadView('documentos_pdf.orcamento', ['orcamento' => $novoOrcamento])
                ->setPaper('a4');

        $path = "orcamentos/orcamento_{$novoOrcamento->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        if (Storage::disk('public')->exists($path)) {
            $novoOrcamento->update(['pdf_path' => $path]);
        }

        return redirect()
            ->route('orcamentos.index')
            ->with('success', 'Orçamento duplicado com sucesso!');
    }


    /**
     * Display the specified resource.
     */
    public function show(Orcamento $orcamento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orcamento $orcamento)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrcamentoRequest $request, Orcamento $orcamento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($orcamento_id)
    {
         $orcamento = Orcamento::findOrFail($orcamento_id);
        $orcamento->delete();

        return redirect()->route('orcamentos.index')
            ->with('success', 'Orçamento excluído com sucesso!');
    }
}
