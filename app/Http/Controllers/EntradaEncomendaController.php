<?php

namespace App\Http\Controllers;

use App\Models\ConsultaPrecoGrupo;
use App\Models\EntradaEncomenda;
use App\Models\EntradaEncomendaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntradaEncomendaController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX — lista todas as entradas registradas
    // ──────────────────────────────────────────────────────────
    public function index()
    {
        $entradas = EntradaEncomenda::with([
            'grupo.cliente',
            'recebedor',
            'destinatario',
            'itens.consultaPreco',
        ])
            ->latest()
            ->paginate(20);

        return view('paginas.produtos.entrada_encomendas.index', compact('entradas'));
    }

    public function kanban()
    {
        return view('paginas.produtos.entrada_encomendas.kanban');
    }

    // ──────────────────────────────────────────────────────────
    // CREATE — formulário de entrada a partir de um grupo aprovado/pago
    // ──────────────────────────────────────────────────────────
    public function create(Request $request, \App\Services\CnpjService $cnpjService)
    {
        $grupoId = $request->get('grupo_id');

        $fornecedoresStatus = [];
        $grupo = $grupoId
            ? ConsultaPrecoGrupo::with([
                'cliente',
                'itens.cor',
                'itens.fornecedorSelecionado.fornecedor',
                'itens.fornecedorSelecionado.comprador',
                'usuario',
                'entradas.itens',
            ])->findOrFail($grupoId)
            : null;

        if ($grupo) {
            foreach ($grupo->itens as $item) {
                if ($item->fornecedorSelecionado && $item->fornecedorSelecionado->fornecedor) {
                    $f = $item->fornecedorSelecionado->fornecedor;
                    if ($f->cnpj && !isset($fornecedoresStatus[$f->cnpj])) {
                        $body = $cnpjService->consultarCnpj($f->cnpj);
                        $fornecedoresStatus[$f->cnpj] = [
                            'ativo' => $cnpjService->estaAtivo($body),
                            'tem_ie' => $cnpjService->temIeAtiva($body),
                            'situacao' => $body['descricao_situacao_cadastral'] ?? 'N/A'
                        ];
                    }
                }
            }
        }

        $gruposDisponiveis = ConsultaPrecoGrupo::with(['cliente', 'itens'])
            ->where('status', 'Aprovado')
            ->latest()
            ->get();

        $usuarios     = \App\Models\User::orderBy('name')->get();
        $categorias   = \App\Models\Categoria::orderBy('nome')->get();      // ← NOVO
        $subCategorias = \App\Models\SubCategoria::orderBy('nome')->get();  // ← NOVO

        return view('paginas.produtos.entrada_encomendas.create', compact(
            'grupo',
            'gruposDisponiveis',
            'usuarios',
            'categorias',
            'subCategorias',
            'fornecedoresStatus'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // STORE — salva a entrada
    // ──────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'grupo_id'         => 'required|exists:consulta_preco_grupos,id',
            'data_recebimento' => 'required|date',
            'recebido_por'     => 'nullable|exists:users,id',
            'observacao'       => 'nullable|string|max:1000',
            'itens'            => 'required|array|min:1',
            'itens.*.consulta_preco_id'     => 'required|exists:consulta_precos,id',
            'itens.*.quantidade_solicitada' => 'required|integer|min:0',
            'itens.*.quantidade_recebida'   => 'required|integer|min:0',
            'itens.*.observacao'            => 'nullable|string|max:500',
            'itens.*.descricao'             => 'nullable|string|max:500',
            // Campos de produto — todos opcionais
            'itens.*.ncm'               => 'nullable|string|max:20',
            'itens.*.codigo_barras'     => 'nullable|string|max:50',
            'itens.*.sku'               => 'nullable|string|max:50',
            'itens.*.unidade_medida'    => 'nullable|string|max:20',
            'itens.*.peso'              => 'nullable|numeric|min:0',
            'itens.*.categoria_id'      => 'nullable|exists:categorias,id',
            'itens.*.sub_categoria_id'  => 'nullable|exists:sub_categorias,id',
            'itens.*.data_vencimento'   => [
                'nullable',
                'date',
                'after:today',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $cpId = $this->input("itens.{$index}.consulta_preco_id");
                    $cp = \App\Models\ConsultaPreco::with('produto')->find($cpId);
                    if ($cp && $cp->produto && $cp->produto->is_perishable && empty($value)) {
                        $fail("A data de vencimento é obrigatória para o produto perecível: {$cp->produto->nome}.");
                    }
                }
            ],
        ]);

        DB::beginTransaction();

        try {
            $grupo = ConsultaPrecoGrupo::with('cliente')->findOrFail($request->grupo_id);

            // Soma já recebido em entradas anteriores
            $jaRecebidoMap = EntradaEncomendaItem::whereHas(
                'entrada',
                fn($q) =>
                $q->where('grupo_id', $grupo->id)
            )->get()->groupBy('consulta_preco_id')->map(
                fn($itens) =>
                $itens->sum('quantidade_recebida')
            );

            $todosCompletos = true;
            foreach ($request->itens as $itemData) {
                $cpId        = $itemData['consulta_preco_id'];
                $jaRecebido  = (float) ($jaRecebidoMap[$cpId] ?? 0);
                $novaQtd     = (float) $itemData['quantidade_recebida'];
                $totalPedido = (float) $itemData['quantidade_solicitada'];

                if (($jaRecebido + $novaQtd) < $totalPedido) {
                    $todosCompletos = false;
                    break;
                }
            }

            $entrada = EntradaEncomenda::create([
                'grupo_id'         => $grupo->id,
                'recebido_por'     => $request->filled('recebido_por') ? $request->recebido_por : auth()->id(),
                'cliente_id'       => $grupo->cliente_id,
                'data_recebimento' => $request->data_recebimento,
                'status'           => $todosCompletos ? 'Recebido completo' : 'Recebido parcialmente',
                'observacao'       => $request->observacao ?? null,
            ]);

            foreach ($request->itens as $itemData) {
                $cpId        = $itemData['consulta_preco_id'];
                $jaRecebido  = (float) ($jaRecebidoMap[$cpId] ?? 0);
                $novaQtd     = (float) $itemData['quantidade_recebida'];
                $totalPedido = (float) $itemData['quantidade_solicitada'];

                EntradaEncomendaItem::create([
                    'entrada_id'            => $entrada->id,
                    'consulta_preco_id'     => $cpId,
                    'quantidade_solicitada' => $totalPedido,
                    'quantidade_recebida'   => $novaQtd,
                    'recebido_completo'     => ($jaRecebido + $novaQtd) >= $totalPedido,
                    'observacao'            => $itemData['observacao'] ?? null,
                    'descricao'             => $itemData['descricao'] ?? null,
                    // Campos de produto opcionais
                    'ncm'               => $itemData['ncm'] ?? null,
                    'codigo_barras'     => $itemData['codigo_barras'] ?? null,
                    'sku'               => $itemData['sku'] ?? null,
                    'unidade_medida'    => $itemData['unidade_medida'] ?? null,
                    'peso'              => $itemData['peso'] ?? null,
                    'categoria_id'      => $itemData['categoria_id'] ?? null,
                    'sub_categoria_id'  => $itemData['sub_categoria_id'] ?? null,
                    'data_vencimento'   => $itemData['data_vencimento'] ?? null,
                ]);
            }

            // Log de auditoria para o recebimento físico
            \App\Models\AcaoCriar::create([
                'descricao' => "Recebimento físico de encomenda registrado: Entrada #{$entrada->id} (Cotação #{$grupo->id})",
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('entrada_encomendas.show', $entrada->id)
                ->with('success', 'Entrada registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao registrar entrada de encomenda: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao registrar entrada: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // COMPLEMENTAR — exibe form para nova entrada complementar
    // Calcula o pendente consolidado de TODAS as entradas anteriores
    // ──────────────────────────────────────────────────────────
    public function complementar(EntradaEncomenda $entradaEncomenda)
    {
        $entradaEncomenda->load([
            'grupo.cliente',
            'grupo.itens.cor',
            'grupo.itens.fornecedorSelecionado.fornecedor',
            'grupo.itens.fornecedorSelecionado.comprador',  // ← comprador que precificou
        ]);

        $grupo = $entradaEncomenda->grupo;

        // Soma tudo já recebido em TODAS as entradas do grupo
        $recebidoMap = EntradaEncomendaItem::whereHas(
            'entrada',
            fn($q) =>
            $q->where('grupo_id', $grupo->id)
        )->get()->groupBy('consulta_preco_id')->map(
            fn($itens) =>
            $itens->sum('quantidade_recebida')
        );

        $itensPendentes = $grupo->itens->map(function ($item) use ($recebidoMap) {
            $solicitado = (float) $item->quantidade;
            $recebido   = (float) ($recebidoMap[$item->id] ?? 0);
            return [
                'item'       => $item,
                'solicitado' => $solicitado,
                'recebido'   => $recebido,
                'pendente'   => max(0, $solicitado - $recebido),
            ];
        });

        $usuarios = \App\Models\User::orderBy('name')->get();

        return view('paginas.produtos.entrada_encomendas.complementar', compact(
            'entradaEncomenda',
            'itensPendentes',
            'usuarios',
            'fornecedoresStatus'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // SHOW — detalhe de uma entrada
    // ──────────────────────────────────────────────────────────
    public function show(EntradaEncomenda $entradaEncomenda)
    {
        $entradaEncomenda->load([
            'grupo.cliente',
            'recebedor',
            'destinatario',
            'itens.consultaPreco.cor',
            'itens.consultaPreco.fornecedorSelecionado.fornecedor',
        ]);

        return view('paginas.produtos.entrada_encomendas.show', compact('entradaEncomenda'));
    }

    // ──────────────────────────────────────────────────────────
    // EDIT — editar/complementar uma entrada parcial
    // ──────────────────────────────────────────────────────────
    public function edit(EntradaEncomenda $entradaEncomenda, \App\Services\CnpjService $cnpjService)
    {
        $entradaEncomenda->load([
            'grupo.cliente',
            'recebedor',
            'destinatario',
            'itens.consultaPreco.cor',
            'itens.consultaPreco.fornecedorSelecionado.fornecedor',
            'itens.categoria',
            'itens.subCategoria',
        ]);

        $fornecedoresStatus = [];
        if ($entradaEncomenda->grupo) {
            foreach ($entradaEncomenda->grupo->itens as $item) {
                if ($item->fornecedorSelecionado && $item->fornecedorSelecionado->fornecedor) {
                    $f = $item->fornecedorSelecionado->fornecedor;
                    if ($f->cnpj && !isset($fornecedoresStatus[$f->cnpj])) {
                        $body = $cnpjService->consultarCnpj($f->cnpj);
                        $fornecedoresStatus[$f->cnpj] = [
                            'ativo' => $cnpjService->estaAtivo($body),
                            'tem_ie' => $cnpjService->temIeAtiva($body),
                            'situacao' => $body['descricao_situacao_cadastral'] ?? 'N/A'
                        ];
                    }
                }
            }
        }

        $usuarios      = \App\Models\User::orderBy('name')->get();
        $categorias    = \App\Models\Categoria::orderBy('nome')->get();
        $subCategorias = \App\Models\SubCategoria::orderBy('nome')->get();

        return view('paginas.produtos.entrada_encomendas.edit', compact(
            'entradaEncomenda',
            'usuarios',
            'categorias',
            'subCategorias',
            'fornecedoresStatus'
        ));
    }

    public function update(Request $request, EntradaEncomenda $entradaEncomenda)
    {
        $request->validate([
            'data_recebimento' => 'required|date',
            'recebido_por'     => 'nullable|exists:users,id',
            'observacao'       => 'nullable|string|max:1000',
            'itens'            => 'required|array|min:1',
            'itens.*.id'                    => 'required|exists:entrada_encomenda_itens,id',
            'itens.*.quantidade_recebida'   => 'required|integer|min:0',
            'itens.*.observacao'            => 'nullable|string|max:500',
            'itens.*.descricao'             => 'nullable|string|max:500',
            // Campos de produto — opcionais
            'itens.*.ncm'              => 'nullable|string|max:20',
            'itens.*.codigo_barras'    => 'nullable|string|max:50',
            'itens.*.sku'              => 'nullable|string|max:50',
            'itens.*.unidade_medida'   => 'nullable|string|max:20',
            'itens.*.peso'             => 'nullable|numeric|min:0',
            'itens.*.categoria_id'     => 'nullable|exists:categorias,id',
            'itens.*.sub_categoria_id' => 'nullable|exists:sub_categorias,id',
        ]);

        DB::beginTransaction();

        try {
            // Atualiza cada item
            foreach ($request->itens as $itemData) {
                $item    = EntradaEncomendaItem::findOrFail($itemData['id']);
                $novaQtd = (float) $itemData['quantidade_recebida'];

                // recebido_completo: considera somente esta entrada
                // (comparado ao que foi solicitado nesta entrada)
                $item->update([
                    'quantidade_recebida' => $novaQtd,
                    'recebido_completo'   => $novaQtd >= (float) $item->quantidade_solicitada,
                    'observacao'          => $itemData['observacao'] ?? null,
                    'descricao'           => $itemData['descricao'] ?? null,
                    // Campos de produto
                    'ncm'              => $itemData['ncm'] ?? null,
                    'codigo_barras'    => $itemData['codigo_barras'] ?? null,
                    'sku'              => $itemData['sku'] ?? null,
                    'unidade_medida'   => $itemData['unidade_medida'] ?? null,
                    'peso'             => isset($itemData['peso']) && $itemData['peso'] !== '' ? $itemData['peso'] : null,
                    'categoria_id'     => $itemData['categoria_id'] ?? null,
                    'sub_categoria_id' => $itemData['sub_categoria_id'] ?? null,
                ]);
            }

            // Recalcula status: considera TODAS as entradas do grupo para saber se está completo
            $entradaEncomenda->refresh()->load('itens');

            $grupo = $entradaEncomenda->grupo ?? ConsultaPrecoGrupo::with('itens')
                ->findOrFail($entradaEncomenda->grupo_id);

            // Soma de TODAS as entradas do grupo (incluindo esta já atualizada)
            $totalRecebidoMap = EntradaEncomendaItem::whereHas(
                'entrada',
                fn($q) => $q->where('grupo_id', $grupo->id)
            )->get()
                ->groupBy('consulta_preco_id')
                ->map(fn($itens) => $itens->sum('quantidade_recebida'));

            $todosCompletos = $grupo->itens->every(function ($item) use ($totalRecebidoMap) {
                $recebido = (float) ($totalRecebidoMap[$item->id] ?? 0);
                return $recebido >= (float) $item->quantidade;
            });

            $novoStatus = $todosCompletos ? 'Recebido completo' : 'Recebido parcialmente';

            $entradaEncomenda->update([
                'data_recebimento' => $request->data_recebimento,
                'recebido_por'     => $request->filled('recebido_por')
                    ? $request->recebido_por
                    : $entradaEncomenda->recebido_por,
                'status'           => $novoStatus,
                'observacao'       => $request->observacao ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('entrada_encomendas.show', $entradaEncomenda->id)
                ->with('success', 'Entrada atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar entrada de encomenda: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // ENCOMENDAS APROVADAS — painel de compras (o que precisa ser comprado)
    // ──────────────────────────────────────────────────────────
    public function encomendasAprovadas()
    {
        // Grupos aprovados com pelo menos um item ainda não recebido completamente
        $grupos = ConsultaPrecoGrupo::with([
            'cliente',
            'usuario',
            'itens.cor',
            'itens.fornecedorSelecionado.fornecedor',
            'itens.fornecedorSelecionado.comprador',
            'entradas.itens',
        ])
            ->where('status', 'Aprovado')
            ->latest()
            ->paginate(20);

        return view('paginas.produtos.entrada_encomendas.aprovadas', compact('grupos'));
    }
}
