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
            'endereco_cep',
            'endereco_logradouro',
            'endereco_numero',
            'endereco_compl',
            'endereco_bairro',
            'endereco_cidade',
            'endereco_estado',
            'contatos',
            'certidoes_negativas',
            'certificacoes_qualidade',
            '_token'
        ]));


        // atualiza endereço comercial
        if ($request->filled('endereco_cep')) {
            $fornecedor->endereco()
                ->updateOrCreate(['tipo' => 'comercial'], array_filter([
                    'cep'        => $request->endereco_cep,
                    'logradouro' => $request->endereco_logradouro,
                    'numero'     => $request->endereco_numero,
                    'complemento' => $request->endereco_compl,
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
    public function edit($fornecedor_id)
    {
        $fornecedor = Fornecedor::find($fornecedor_id);

        $contatos = Contato::where('fornecedor_id', $fornecedor->id)->get();
        return view('paginas.fornecedores.edit', compact('contatos', 'fornecedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor)
    {
        $fornecedor = Fornecedor::find($request->fornecedor_id);

        // Atualiza os dados do fornecedor, exceto CNPJ
        $fornecedor->update($request->except([
            'cnpj', // não pode ser alterado
            'endereco_cep',
            'endereco_logradouro',
            'endereco_numero',
            'endereco_compl',
            'endereco_bairro',
            'endereco_cidade',
            'endereco_estado',
            'contatos',
            'certidoes_negativas',
            'certificacoes_qualidade',
            '_token',
            '_method'
        ]));

        /**
         * ENDEREÇO
         */
        if ($request->filled('endereco_cep')) {
            $enderecoData = array_filter([
                'cep'        => $request->endereco_cep,
                'logradouro' => $request->endereco_logradouro,
                'numero'     => $request->endereco_numero,
                'complemento' => $request->endereco_compl,
                'bairro'     => $request->endereco_bairro,
                'cidade'     => $request->endereco_cidade,
                'estado'     => $request->endereco_estado,
                'tipo'       => 'comercial',
            ]);

            // Se veio o ID, tenta atualizar o endereço existente
            if ($request->filled('endereco_id')) {
                $fornecedor->endereco()->updateOrCreate(
                    ['id' => $request->endereco_id],
                    $enderecoData
                );
            } else {
                // Caso contrário cria um novo
                $fornecedor->endereco()->create($enderecoData);
            }
        }

        /**
         * CONTATOS
         */
        if ($request->filled('contatos')) {
            foreach ($request->contatos as $contato) {
                $contatoData = array_filter([
                    'nome'     => $contato['nome'] ?? null,
                    'telefone' => $contato['telefone'] ?? null,
                    'email'    => $contato['email'] ?? null,
                ]);

                if (!empty($contato['id'])) {
                    // Atualiza contato existente
                    $fornecedor->contatos()
                        ->where('id', $contato['id'])
                        ->update($contatoData);
                } else {
                    // Cria novo contato
                    $fornecedor->contatos()->create($contatoData);
                }
            }
        }

        return $this->show($fornecedor->id)
            ->with('success', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fornecedor $fornecedor)
    {
        //
    }
}
