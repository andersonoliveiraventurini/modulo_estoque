<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Contato;
use App\Models\Vendedor;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::paginate();
        return view('paginas.clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendedores = Vendedor::all();
        return view('paginas.clientes.create', compact('vendedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create_completo()
    {
        $vendedores = Vendedor::all();
        return view('paginas.clientes.create_completo', compact('vendedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClienteRequest $request)
    {
        DB::transaction(function () use ($request) {
            // salva apenas os valores preenchidos
            $dadosCliente = array_filter($request->only([
                'cpf', 'cnpj', 'nome', 'nome_fantasia', 'razao_social', 'tratamento',
                'data_nascimento', 'cnae', 'inscricao_estadual', 'inscricao_municipal',
                'data_abertura', 'regime_tributario', 'vendedor_id', 'vendedor_externo_id'
            ]));

            $cliente = Cliente::create($dadosCliente);

            // contatos
            if ($request->filled('contatos')) {
                foreach ($request->contatos as $contato) {
                    $cliente->contatos()->create(array_filter([
                        'nome'     => $contato['nome'] ?? null,
                        'telefone' => $contato['telefone'] ?? null,
                        'email'    => $contato['email'] ?? null,
                    ]));
                }
            }

            // endereço comercial
            if ($request->filled('endereco_cep')) {
                $cliente->enderecos()->create(array_filter([
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

            // endereço de entrega
            if ($request->filled('entrega_cep')) {
                $cliente->enderecos()->create(array_filter([
                    'cep'        => $request->entrega_cep,
                    'logradouro' => $request->entrega_logradouro,
                    'numero'     => $request->entrega_numero,
                    'complemento'=> $request->entrega_compl,
                    'bairro'     => $request->entrega_bairro,
                    'cidade'     => $request->entrega_cidade,
                    'estado'     => $request->entrega_estado,
                    'tipo'       => 'entrega',
                ]));
            }
        });

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $contatos = Contato::where('cliente_id', $cliente->id)->get();
        return view('paginas.clientes.show', compact('cliente', 'contatos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $contatos = Contato::where('cliente_id', $cliente->id)->get();
        $vendedores = Vendedor::all();
        return view('paginas.clientes.edit', compact('cliente', 'contatos', 'vendedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
    }
}
