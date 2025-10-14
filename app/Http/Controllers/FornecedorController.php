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
        // normaliza CNPJ
        $request->merge([
            'cnpj' => preg_replace('/\D/', '', $request->cnpj),
        ]);

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
                    'cep' => $request->endereco_cep ? preg_replace('/\D/', '', $request->endereco_cep) : null,
                    'logradouro'   => $request->endereco_logradouro,
                    'numero'       => $request->endereco_numero,
                    'complemento'  => $request->endereco_compl,
                    'bairro'       => $request->endereco_bairro,
                    'cidade'       => $request->endereco_cidade,
                    'estado'       => $request->endereco_estado,
                    'tipo'         => 'comercial',
                ]));
        }

        // contatos
        if ($request->filled('contatos')) {
            foreach ($request->contatos as $contato) {
                $fornecedor->contatos()->create(array_filter([
                    'nome'     => $contato['nome'] ?? null,
                    'telefone' => !empty($contato['telefone']) ? preg_replace('/\D/', '', $contato['telefone']) : null,
                    'email'    => $contato['email'] ?? null,
                ]));
            }
        }

        // Upload de Certidões Negativas (1 único arquivo)
        if ($request->hasFile('certidoes_negativas')) {
            $path = $request->file('certidoes_negativas')->store('documentos/certidoes', 'public');

            $fornecedor->documentos()->create([
                'tipo'           => 'certidao_negativa',
                'descricao'      => 'Certidão negativa',
                'caminho_arquivo' => $path,
                'user_id'        => auth()->id(),
            ]);
        }

        // Upload de Certificações de Qualidade (vários arquivos)
        if ($request->hasFile('certificacoes_qualidade')) {
            foreach ($request->file('certificacoes_qualidade') as $file) {
                $path = $file->store('documentos/certificacoes', 'public');

                $fornecedor->documentos()->create([
                    'tipo'           => 'certificacao_qualidade',
                    'descricao'      => 'Certificação de qualidade',
                    'caminho_arquivo' => $path,
                    'user_id'        => auth()->id(),
                ]);
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
        $fornecedor = Fornecedor::findOrFail($request->fornecedor_id);

        // Atualiza os dados do fornecedor (exceto CNPJ e campos tratados separadamente)
        $fornecedor->update($request->except([
            'cnpj',
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
                'cep'        => preg_replace('/\D/', '', $request->endereco_cep),
                'logradouro' => $request->endereco_logradouro,
                'numero'     => $request->endereco_numero,
                'complemento' => $request->endereco_compl,
                'bairro'     => $request->endereco_bairro,
                'cidade'     => $request->endereco_cidade,
                'estado'     => $request->endereco_estado,
                'tipo'       => 'comercial',
            ]);

            if ($request->filled('endereco_id')) {
                $fornecedor->endereco()->updateOrCreate(
                    ['id' => $request->endereco_id],
                    $enderecoData
                );
            } else {
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
                    'telefone' => !empty($contato['telefone']) ? preg_replace('/\D/', '', $contato['telefone']) : null,
                    'email'    => $contato['email'] ?? null,
                ]);

                if (!empty($contato['id'])) {
                    $fornecedor->contatos()->where('id', $contato['id'])->update($contatoData);
                } else {
                    $fornecedor->contatos()->create($contatoData);
                }
            }
        }

        /**
         * DOCUMENTOS
         */

        // Certidões Negativas (apenas um arquivo, substitui o anterior)
        if ($request->hasFile('certidoes_negativas')) {
            $path = $request->file('certidoes_negativas')->store('documentos', 'public');

            // remove o arquivo anterior se existir
            $fornecedor->documentos()->where('tipo', 'certidao_negativa')->delete();

            $fornecedor->documentos()->create([
                'tipo'           => 'certidao_negativa',
                'descricao'      => 'Certidão Negativa',
                'caminho_arquivo' => $path,
                'user_id'        => auth()->id(),
            ]);
        }

        // Certificações de Qualidade (vários arquivos)
        if ($request->hasFile('certificacoes_qualidade')) {
            foreach ($request->file('certificacoes_qualidade') as $file) {
                $path = $file->store('documentos', 'public');

                $fornecedor->documentos()->create([
                    'tipo'           => 'certificacao_qualidade',
                    'descricao'      => 'Certificação de Qualidade',
                    'caminho_arquivo' => $path,
                    'user_id'        => auth()->id(),
                ]);
            }
        }

        /**
         * DOCUMENTOS - Remoção
         */
        if ($request->filled('delete_documents')) {
            foreach ($request->delete_documents as $docId) {
                $doc = $fornecedor->documentos()->find($docId);
                if ($doc) {
                    // Remove arquivo físico também
                    //\Storage::disk('public')->delete($doc->caminho_arquivo);

                    $doc->delete(); // SoftDeletes
                }
            }
        }


        return $this->show($fornecedor->id)
            ->with('success', 'Fornecedor atualizado com sucesso!');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($fornecedor_id)
    {
        $fornecedor = Fornecedor::findOrFail($fornecedor_id);
        $fornecedor->delete();

        return redirect()
            ->route('fornecedores.index')
            ->with('success', 'Fornecedor deletado com sucesso!');
    }
}
