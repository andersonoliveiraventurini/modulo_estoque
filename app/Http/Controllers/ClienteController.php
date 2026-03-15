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

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::paginate();
        return view('paginas.clientes.index', compact('clientes'));
    }

    public function create()
    {
        $vendedores          = Vendedor::internos()->with('user')->get();
        $vendedores_externos = Vendedor::externos()->with('user')->get();
        $vendedores_assistentes = Vendedor::assistentes()->with('user')->get();

        return view('paginas.clientes.create', compact(
            'vendedores',
            'vendedores_externos',
            'vendedores_assistentes'
        ));
    }

    public function create_completo()
    {
        $vendedores          = Vendedor::internos()->with('user')->get();
        $vendedores_externos = Vendedor::externos()->with('user')->get();
        $vendedores_assistentes = Vendedor::assistentes()->with('user')->get();

        return view('paginas.clientes.create_completo', compact(
            'vendedores',
            'vendedores_externos',
            'vendedores_assistentes'
        ));
    }

    public function store(StoreClienteRequest $request)
    {
        // 1. Verificar duplicidade manual para permitir limpar o form e mostrar link
        $cnpj = $request->filled('cnpj') ? preg_replace('/\D/', '', $request->cnpj) : null;
        $cpf  = $request->filled('cpf') ? preg_replace('/\D/', '', $request->cpf) : null;
        $cpf_resp = $request->filled('cpf_responsavel') ? preg_replace('/\D/', '', $request->cpf_responsavel) : null;
        $cpf_final = $cpf ?? $cpf_resp;

        $clienteExistente = null;
        if ($cnpj) {
            $clienteExistente = Cliente::where('cnpj', $cnpj)->first();
        } elseif ($cpf_final) {
            $clienteExistente = Cliente::where('cpf', $cpf_final)->first();
        }

        if ($clienteExistente) {
            return redirect()->back()
                ->with('duplicate_client_id', $clienteExistente->id)
                ->with('error', 'O valor indicado para o campo cnpj/cpf já se encontra registrado.');
        }

        $cliente_id = DB::transaction(function () use ($request) {

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
                'vendedor_assistente_id',
                'suframa',
                'classificacao',
                'canal_origem',
                'desconto',
                'negociar_titulos',
                'inativar_apos',
            ]));

            if ($request->filled('cpf_responsavel')) {
                $dadosCliente['cpf'] = $request->input('cpf_responsavel');
            }

            if (!empty($dadosCliente['cnpj'])) {
                $dadosCliente['cnpj'] = preg_replace('/\D/', '', $dadosCliente['cnpj']);
            }

            if (!empty($dadosCliente['suframa'])) {
                $dadosCliente['suframa'] = preg_replace('/\D/', '', $dadosCliente['suframa']);
            }

            $cliente = Cliente::create($dadosCliente);

            if ($request->bloqueado == 1) {
                Bloqueio::create([
                    'cliente_id' => $cliente->id,
                    'motivo'     => 'Bloqueio automático no cadastro',
                    'user_id'    => auth()->id(),
                ]);
            }

            if ($request->filled('limite_boleto') || $request->filled('limite_carteira')) {
                AnaliseCredito::create([
                    'cliente_id'      => $cliente->id,
                    'limite_boleto'   => $request->limite_boleto ?? 0,
                    'limite_carteira' => $request->limite_carteira ?? 0,
                    'observacoes'     => 'Análise inicial no cadastro',
                    'user_id'         => auth()->id(),
                ]);
            }

            if ($request->filled('contatos')) {
                foreach ($request->contatos as $contato) {
                    $cliente->contatos()->create(array_filter([
                        'nome'     => $contato['nome'] ?? null,
                        'telefone' => !empty($contato['telefone']) ? preg_replace('/\D/', '', $contato['telefone']) : null,
                        'email'    => $contato['email'] ?? null,
                    ]));
                }
            }

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

            if ($request->hasFile('certidoes_negativas')) {
                $path = $request->file('certidoes_negativas')->store('documentos', 'public');
                $cliente->documentos()->create([
                    'tipo'            => 'certidao_negativa',
                    'descricao'       => 'Certidão Negativa',
                    'caminho_arquivo' => $path,
                    'user_id'         => auth()->id(),
                    'cliente_id'      => $cliente->id,
                ]);
            }

            return $cliente->id;
        });

        if ($request->has('pre_cadastro')) {
            return redirect()->route('orcamentos.criar', ['cliente_id' => $cliente_id])
                ->with('success', 'Pré-cadastro realizado com sucesso!');
        }

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load([
            'vendedor.user',
            'vendedorExterno.user',
            'vendedorAssistente.user',
            'enderecos',
            'contatos',
            'certidoesNegativas',
        ]);

        $contatos = $cliente->contatos;

        return view('paginas.clientes.show', compact('cliente', 'contatos'));
    }

    public function edit(Cliente $cliente)
    {
        $contatos               = Contato::where('cliente_id', $cliente->id)->get();
        $vendedores             = Vendedor::internos()->with('user')->get();
        $vendedores_externos    = Vendedor::externos()->with('user')->get();
        $vendedores_assistentes = Vendedor::assistentes()->with('user')->get();
        $enderecos              = $cliente->enderecos;

        return view('paginas.clientes.edit', compact(
            'cliente',
            'contatos',
            'vendedores',
            'vendedores_externos',
            'vendedores_assistentes',
            'enderecos'
        ));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente)
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
                'vendedor_assistente_id',
                'suframa',
                'classificacao',
                'canal_origem',
                'desconto',
                'negociar_titulos',
                'inativar_apos',
            ]));

            if ($request->filled('cpf_responsavel')) {
                $dadosCliente['cpf'] = $request->input('cpf_responsavel');
            }

            $cliente->update($dadosCliente);

            if ($request->filled('delete_documents')) {
                foreach ($request->delete_documents as $docId) {
                    $doc = $cliente->documentos()->find($docId);
                    if ($doc) {
                        $doc->delete();
                    }
                }
            }

            if ($request->hasFile('certidoes_negativas')) {
                $path = $request->file('certidoes_negativas')->store('documentos', 'public');
                $cliente->documentos()->where('tipo', 'certidao_negativa')->delete();
                $cliente->documentos()->create([
                    'tipo'            => 'certidao_negativa',
                    'descricao'       => 'Certidão Negativa',
                    'caminho_arquivo' => $path,
                    'user_id'         => auth()->id(),
                    'cliente_id'      => $cliente->id,
                ]);
            }

            if ($request->bloqueado == 1 && !$cliente->bloqueado) {
                Bloqueio::create([
                    'cliente_id' => $cliente->id,
                    'motivo'     => 'Bloqueio manual na edição',
                    'user_id'    => auth()->id(),
                ]);
            }

            if ($request->filled('limite_boleto') || $request->filled('limite_carteira')) {
                $cliente->analisesCredito()->create([
                    'limite_boleto'   => $request->limite_boleto ?? 0,
                    'limite_carteira' => $request->limite_carteira ?? 0,
                    'observacoes'     => 'Atualização de limites na edição',
                    'user_id'         => auth()->id(),
                ]);
            }

            if ($request->filled('contatos')) {
                foreach ($request->contatos as $contato) {
                    $cliente->contatos()->create(array_filter([
                        'nome'     => $contato['nome'] ?? null,
                        'telefone' => $contato['telefone'] ?? null,
                        'email'    => $contato['email'] ?? null,
                    ]));
                }
            }

            if ($request->filled('endereco_cep')) {
                $cliente->enderecos()->updateOrCreate(
                    ['tipo' => 'comercial'],
                    array_filter([
                        'cep'         => $request->endereco_cep,
                        'logradouro'  => $request->endereco_logradouro,
                        'numero'      => $request->endereco_numero,
                        'complemento' => $request->endereco_compl,
                        'bairro'      => $request->endereco_bairro,
                        'cidade'      => $request->endereco_cidade,
                        'estado'      => $request->endereco_estado,
                        'tipo'        => 'comercial',
                    ])
                );
            }

            if ($request->filled('entrega_cep')) {
                $cliente->enderecos()->updateOrCreate(
                    ['tipo' => 'entrega'],
                    array_filter([
                        'cep'         => $request->entrega_cep,
                        'logradouro'  => $request->entrega_logradouro,
                        'numero'      => $request->entrega_numero,
                        'complemento' => $request->entrega_compl,
                        'bairro'      => $request->entrega_bairro,
                        'cidade'      => $request->entrega_cidade,
                        'estado'      => $request->entrega_estado,
                        'tipo'        => 'entrega',
                    ])
                );
            }
        });

        return redirect()->route('clientes.show', $cliente)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($cliente_id)
    {
        $cliente = Cliente::findOrFail($cliente_id);
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }
}