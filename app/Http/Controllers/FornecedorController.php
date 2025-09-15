<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFornecedorRequest;
use App\Http\Requests\UpdateFornecedorRequest;
use App\Models\Contato;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fornecedores = Fornecedor::paginate();
        return view('paginas.fornecedores.index', compact('fornecedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('paginas.fornecedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFornecedorRequest $request)
    {

        $fornecedor = Fornecedor::create($request->except([
            'endereco_cep', 'endereco_logradouro', 'endereco_numero', 'endereco_compl',
            'endereco_bairro', 'endereco_cidade', 'endereco_estado',
            'contatos', 'certidoes_negativas', 'certificacoes_qualidade', '_token'
        ]));


        // atualiza endereço comercial
        if ($request->filled('endereco_cep')) {
            $fornecedor->enderecos()
                ->updateOrCreate(['tipo' => 'comercial'], array_filter([
                    'cep'        => $request->endereco_cep,
                    'logradouro' => $request->endereco_logradouro,
                    'numero'     => $request->endereco_numero,
                    'complemento'=> $request->endereco_compl,
                    'bairro'     => $request->endereco_bairro,
                    'cidade'     => $request->endereco_cidade,
                    'estado'     => $request->endereco_estado,
                    'tipo'       => 'comercial',
                ]));
        }

            // contatos
        if ($request->filled('contatos')) {
            foreach ($request->contatos as $contato) {
                $fornecedor->contatos()->create(array_filter([
                    'nome'     => $contato['nome'] ?? null,
                    'telefone' => $contato['telefone'] ?? null,
                    'email'    => $contato['email'] ?? null,
                ]));
            }
        }


        return redirect()->route('fornecedores.show', $fornecedor);
    }

    public function tabelaPrecos($fornecedor_id)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor_id);
        return view('paginas.fornecedores.tabela_preco.mostrar', compact('fornecedor'));
    }

    /**
     * Display the specified resource.
     */
    public function show($fornecedor_id)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor_id);
        $contatos = Contato::where('fornecedor_id', $fornecedor->id)->get();
        return view('paginas.fornecedores.show', compact('fornecedor', 'contatos'));
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fornecedor $fornecedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fornecedor $fornecedor)
    {
        //
    }
}
