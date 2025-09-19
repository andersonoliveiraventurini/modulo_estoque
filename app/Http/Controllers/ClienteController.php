<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\AnaliseCredito;
use App\Models\Bloqueio;
use App\Models\Cliente;
use App\Models\Contato;
use App\Models\Vendedor;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Block;

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
        $cliente_id = DB::transaction(function () use ($request) {

            // salva apenas os valores preenchidos
            $dadosCliente = array_filter($request->only([
                'cpf',
                'cnpj',
                'nome',
                'nome_fantasia',
                'razao_social',
                'tratamento',
                'data_nascimento',
                'cnae',
                'inscricao_estadual',
                'inscricao_municipal',
                'data_abertura',
                'regime_tributario',
                'vendedor_id',
                'vendedor_externo_id',
                'certidoes_negativas',
                'suframa',
                'classificacao',
                'canal_origem',
                'desconto',
                'negociar_titulos',
                'inativar_apos'
            ]));

            // Ajusta o campo cpf_responsavel -> cpf
            if ($request->filled('cpf_responsavel')) {
                $dadosCliente['cpf'] = $request->input('cpf_responsavel');
            }

            // Upload de arquivo (certidões negativas)
            if ($request->hasFile('certidoes_negativas')) {
                $path = $request->file('certidoes_negativas')->store('certidoes', 'public');
                $dadosCliente['certidoes_negativas'] = $path;
            }

            // Normalização dos campos numéricos
            if (!empty($dadosCliente['cnpj'])) {
                $dadosCliente['cnpj'] = preg_replace('/\D/', '', $dadosCliente['cnpj']);
            }

            // Normalização dos campos numéricos
            if (!empty($dadosCliente['suframa'])) {
                $dadosCliente['suframa'] = preg_replace('/\D/', '', $dadosCliente['suframa']);
            }

            // cria cliente
            $cliente = Cliente::create($dadosCliente);

            // bloqueio inicial
            if ($request->bloqueado == 1) {
                Bloqueio::create([
                    'cliente_id' => $cliente->id,
                    'motivo'     => 'Bloqueio automático no cadastro',
                    'user_id'    => auth()->id()
                ]);
            }

            // análise de crédito inicial
            if (isset($request->limite_boleto) || isset($request->limite_carteira)) {
                AnaliseCredito::create([
                    'cliente_id'     => $cliente->id,
                    'limite_boleto'  => $request->limite_boleto ?? 0,
                    'limite_carteira' => $request->limite_carteira ?? 0,
                    'observacoes'    => 'Análise inicial no cadastro',
                    'user_id'        => auth()->id()
                ]);
            }

            // contatos
            if ($request->filled('contatos')) {
                foreach ($request->contatos as $contato) {
                    $cliente->contatos()->create(array_filter([
                        'nome'     => $contato['nome'] ?? null,
                        'telefone' => !empty($contato['telefone']) ? preg_replace('/\D/', '', $contato['telefone']) : null,
                        'email'    => $contato['email'] ?? null,
                    ]));
                }
            }

            // endereço comercial
            if ($request->filled('endereco_cep')) {
                $cliente->enderecos()->create(array_filter([
                    'cep'         => preg_replace('/\D/', '', $request->endereco_cep),
                    'logradouro'  => $request->endereco_logradouro,
                    'numero'      => $request->endereco_numero,
                    'complemento' => $request->endereco_compl,
                    'bairro'      => $request->endereco_bairro,
                    'cidade'      => $request->endereco_cidade,
                    'estado'      => $request->endereco_estado,
                    'tipo'        => 'comercial',
                ]));
            }

            // endereço de entrega
            if ($request->filled('entrega_cep')) {
                $cliente->enderecos()->create(array_filter([
                    'cep'         => preg_replace('/\D/', '', $request->entrega_cep),
                    'logradouro'  => $request->entrega_logradouro,
                    'numero'      => $request->entrega_numero,
                    'complemento' => $request->entrega_compl,
                    'bairro'      => $request->entrega_bairro,
                    'cidade'      => $request->entrega_cidade,
                    'estado'      => $request->entrega_estado,
                    'tipo'        => 'entrega',
                ]));
            }

            // retorna o id no final
            return $cliente->id;
        });

        if ($request->has('pre_cadastro')) {
            return redirect()->route('orcamentos.criar', ['cliente_id' => $cliente_id])
                ->with('success', 'Pré-cadastro realizado com sucesso! Em breve entraremos em contato.');
        } else {
            return redirect()->route('clientes.index')
                ->with('success', 'Cliente cadastrado com sucesso!');
        }
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
        $enderecos = $cliente->enderecos;
        return view('paginas.clientes.edit', compact('cliente', 'contatos', 'vendedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClienteRequest $request, Cliente $cliente)
    {
        DB::transaction(function () use ($request, $cliente) {
            $dadosCliente = array_filter($request->only([
                'cpf',
                'cnpj',
                'nome',
                'nome_fantasia',
                'razao_social',
                'tratamento',
                'data_nascimento',
                'cnae',
                'inscricao_estadual',
                'inscricao_municipal',
                'data_abertura',
                'regime_tributario',
                'vendedor_id',
                'vendedor_externo_id',
                'certidoes_negativas',
                'suframa',
                'classificacao',
                'canal_origem',
                'desconto',
                'negociar_titulos',
                'inativar_apos'
            ]));

            if ($request->filled('cpf_responsavel')) {
                $dadosCliente['cpf'] = $request->input('cpf_responsavel');
            }

            if ($request->hasFile('certidoes_negativas')) {
                $path = $request->file('certidoes_negativas')->store('certidoes', 'public');
                $dadosCliente['certidoes_negativas'] = $path;
            }

            $cliente->update($dadosCliente);

            // atualiza bloqueio
            if ($request->bloqueado == 1 && !$cliente->bloqueado) {
                Bloqueio::create([
                    'cliente_id' => $cliente->id,
                    'motivo' => 'Bloqueio manual na edição',
                    'user_id' => auth()->id()
                ]);
            }

            // atualiza análise de crédito
            if (isset($request->limite_boleto) || isset($request->limite_carteira)) {
                $cliente->analisesCredito()->create([
                    'limite_boleto' => $request->limite_boleto ?? 0,
                    'limite_carteira' => $request->limite_carteira ?? 0,
                    'observacoes' => 'Atualização de limites na edição',
                    'user_id' => auth()->id()
                ]);
            }

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


            // atualiza endereço comercial
            if ($request->filled('endereco_cep')) {
                $cliente->enderecos()
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

            // atualiza endereço de entrega
            if ($request->filled('entrega_cep')) {
                $cliente->enderecos()
                    ->updateOrCreate(['tipo' => 'entrega'], array_filter([
                        'cep'        => $request->entrega_cep,
                        'logradouro' => $request->entrega_logradouro,
                        'numero'     => $request->entrega_numero,
                        'complemento' => $request->entrega_compl,
                        'bairro'     => $request->entrega_bairro,
                        'cidade'     => $request->entrega_cidade,
                        'estado'     => $request->entrega_estado,
                        'tipo'       => 'entrega',
                    ]));
            }
        });

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente atualizado com sucesso!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($cliente_id)
    {
        $cliente = Cliente::findOrFail($cliente_id);
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }
}
