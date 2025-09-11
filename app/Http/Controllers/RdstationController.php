<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRdstationRequest;
use App\Http\Requests\UpdateRdstationRequest;
use App\Models\Cliente;
use App\Models\Contato;
use App\Models\Rdstation;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class RdstationController extends Controller
{

    public function listarEmpresas()
    {
        $token = config('app.rdstation.token');

        if (!$token) {
            Session::flash('success', 'Token do RD Station inválido.');
            return response()->json(['error' => 'Token do RD Station não configurado.'], 400);
        }

        $conexao = new Client();

        $response = $conexao->request('GET', 'https://crm.rdstation.com/api/v1/organizations?token=' . $token, [
            'headers' => [
                'accept' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return response()->json(['error' => 'Erro ao listar empresas.'], 400);
        }

        $data = json_decode($response->getBody(), true);

        $empresas = collect($data['organizations']);

        return view('rdstation.empresas.index', compact('empresas'));
    }

    public function listarNegociacoes()
    {
        $token = config('app.rdstation.token');

        if (!$token) {
            Session::flash('error', 'Token do RD Station não configurado.');
            return response()->json(['error' => 'Token do RD Station não configurado.'], 400);
        }

        $conexao = new Client();
        $response = $conexao->request('GET', 'https://crm.rdstation.com/api/v1/deals?token=' . $token, [
            'headers' => [
                'accept' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return response()->json(['error' => 'Erro ao listar negociações.'], 400);
        }

        $data = json_decode($response->getBody(), true);

        $negociacoes = collect($data['deals']);
        //dd($negociacoes['0']['organization']['id']);

        return view('rdstation.negociacoes.index', compact('negociacoes'));
    }

    public function criarEmpresa($cliente)
    {
        // https://developers.rdstation.com/reference/crm-v1-create-organization
        $token = config('app.rdstation.token');
        $user_id = config('app.rdstation.user_id');

        if (!$token) {
            Session::flash('success', 'Token do RD Station inválido.');
            return response()->json(['error' => 'Token do RD Station não configurado.'], 400);
        }

        if (!$user_id) {
            return response()->json(['error' => 'User ID do RD Station não configurado.'], 400);
        }

        $cliente = Cliente::findOrFail($cliente);

        $conexao = new Client();
        if ($cliente->rdstation_id !== null) {
            // atualizar empresa existente
            try {
                $response = $conexao->request('PUT', "https://crm.rdstation.com/api/v1/organizations/{$cliente->rdstation_id}?token={$token}", [
                    'body' => '{"organization":{"name":"' . $cliente->nome . '","organization_custom_fields":[{"custom_field_id":"67c8bcc4850d1d001fd0d994","value":"' . $cliente->bairro . '"},{"custom_field_id":"67c8bcc680ee960014328168","value":"' . $cliente->cidade . '"},{"custom_field_id":"67c8bcced016550014fdf769","value":"' . $cliente->estado . '"},{"custom_field_id":"67c8bcc7d016550014fdf760","value":"' . $cliente->cnpj . '"},{"custom_field_id":"67c8bcc92912da00141c9e46","value":"' . $cliente->email . '"},{"custom_field_id":"67c8bcde01ee1b0014100eba","value":"' . $cliente->telefone . '"},{"custom_field_id":"67c8bcf9d016550020fdf784","value":"' . $cliente->contato . '"}],"organization_segments":["NOME_DO_SEGMENTO1","NOME_DO_SEGMENTO2"],"resume":"resumo da empresa","url":"http://google.com/","user_id":"' . $user_id . '"}}',
                    'headers' => [
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ],
                ]);
                if ($response->getStatusCode() === 200) {
                    //$data = json_decode($response->getBody(), true);
                    // $response->getBody();

                    $data = json_decode($response->getBody(), true);
                    // Atualizar o cliente com o ID da empresa criada/atualizada
                    if ($cliente->rdstation_id == null) {
                        $cliente->rdstation_id = $data['id'];
                    }
                    $cliente->enviado_api = true;

                    $cliente->save();

                    return response()->json(['message' => 'Empresa atualizada com sucesso.']);
                } else {
                    return response()->json(['error' => 'Erro ao criar empresa.'], 400);
                }
                echo $response->getBody();
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                echo $e->getResponse()->getBody()->getContents(); // Mostra o JSON completo
            }
        } else {
            // criar nova empresa
            try {
                $response = $conexao->request('POST', 'https://crm.rdstation.com/api/v1/organizations?token=' . $token, [
                    'body' => '{"organization":{"name":"' . $cliente->nome . '.","organization_custom_fields":[{"custom_field_id":"67c8bcc4850d1d001fd0d994","value":"' . $cliente->bairro . '"},{"custom_field_id":"67c8bcc680ee960014328168","value":"' . $cliente->cidade . '"},{"custom_field_id":"67c8bcced016550014fdf769","value":"' . $cliente->estado . '"},{"custom_field_id":"67c8bcc7d016550014fdf760","value":"' . $cliente->cnpj . '"},{"custom_field_id":"67c8bcc92912da00141c9e46","value":"' . $cliente->email . '"},{"custom_field_id":"67c8bcde01ee1b0014100eba","value":"' . $cliente->telefone . '"},{"custom_field_id":"67c8bcf9d016550020fdf784","value":"' . $cliente->contato . '"}],"organization_segments":["NOME_DO_SEGMENTO1","NOME_DO_SEGMENTO2"],"resume":"resumo da empresa","url":"http://google.com/","user_id":"' . $user_id . '"}}',
                    'headers' => [
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ],
                ]);
                if ($response->getStatusCode() === 200) {
                    //$data = json_decode($response->getBody(), true);
                    // $response->getBody();

                    $data = json_decode($response->getBody(), true);
                    // Atualizar o cliente com o ID da empresa criada/atualizada
                    if ($cliente->rdstation_id == null) {
                        $cliente->rdstation_id = $data['id'];
                    }
                    $cliente->enviado_api = true;

                    $cliente->save();

                    return response()->json(['message' => 'Empresa criada com sucesso.']);
                } else {
                    return response()->json(['error' => 'Erro ao criar empresa.'], 400);
                }
                echo $response->getBody();
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                echo $e->getResponse()->getBody()->getContents(); // Mostra o JSON completo
            }
        }
    }

    public function criarContato($cliente)
    {
        // https://developers.rdstation.com/reference/crm-v1-create-contact
        $token = config('app.rdstation.token');

        if (!$token) {
            Session::flash('success', 'Token do RD Station inválido.');
            return response()->json(['error' => 'Token do RD Station não configurado.'], 400);
        }

        $cliente = Cliente::findOrFail($cliente);

        $conexao = new Client();

        $contatos = Contato::where('empresa_rdstation_id', $cliente->rdstation_id)->get();
        foreach ($contatos as $c) {
            try {
                if ($c->rdstation_contact_id == null) {
                    $response = $conexao->request('POST', 'https://crm.rdstation.com/api/v1/contacts?token=' . $token, [
                        'body' => '{"contact":{"birthday":{"day":11,"month":9,"year":1979},"name":"' . $c->nome . '","organization_id":"' . $cliente->rdstation_id . '"}}',
                        'headers' => [
                            'accept' => 'application/json',
                            'content-type' => 'application/json',
                        ],
                    ]);
                    if ($response->getStatusCode() === 200) {
                        // Atualizar o contato com o ID do contato criado
                        $data = json_decode($response->getBody(), true);
                        $c->rdstation_contact_id = $data['contact_id'];
                        $c->enviado_api = true;

                        $c->save();
                    } else {
                        return response()->json(['error' => 'Erro ao criar contato.'], 400);
                    }
                } else {
                    $response = $conexao->request('PUT', 'https://crm.rdstation.com/api/v1/contacts/contact_id?token=' . $token, [
                        'body' => '{"contact":{"birthday":{"day":11,"month":9,"year":1979},"name":"' . $c->nome . '","organization_id":"' . $cliente->rdstation_id . '"}}',
                        'headers' => [
                            'accept' => 'application/json',
                            'content-type' => 'application/json',
                        ],
                    ]);
                    if ($response->getStatusCode() === 200) {
                        // Atualizar o contato com o ID do contato criado
                        $c->enviado_api = true;

                        $c->save();
                    } else {
                        return response()->json(['error' => 'Erro ao alterar contato.'], 400);
                    }
                }
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                echo $e->getResponse()->getBody()->getContents(); // Mostra o JSON completo
            }
        }

        return response()->json(['message' => 'Contatos criados/atualizados com sucesso.']);
    }

    public function criarNegociacao() {}

    public function checarToken()
    {
        // Implementar lógica para verificar o token do RD Station
        // Exemplo: verificar se o token está configurado corretamente
        $token = config('app.rdstation.token');

        if (!$token) {
            Session::flash('success', 'Token do RD Station inválido.');
            return response()->json(['error' => 'Token do RD Station não configurado.'], 400);
        }

        $conexao = new Client();

        $response = $conexao->request('GET', 'https://crm.rdstation.com/api/v1/token/check?token=' . $token, [
            'headers' => [
                'accept' => 'application/json',
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            Session::flash('success', 'Token do RD Station está configurado.');
        } else {
            Session::flash('error', 'Token do RD Station inválido.');
        }
        return view('dashboard');

        //return response()->json(['message' => 'Token do RD Station está configurado.']);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRdstationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Rdstation $rdstation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rdstation $rdstation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRdstationRequest $request, Rdstation $rdstation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rdstation $rdstation)
    {
        //
    }
}
