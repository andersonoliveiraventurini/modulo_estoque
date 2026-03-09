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

    // ──────────────────────────────────────────────────────────
    // CREATE — formulário de entrada a partir de um grupo aprovado/pago
    // ──────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $grupoId = $request->get('grupo_id');

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
            'subCategorias'
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
            'data_entrega'     => 'nullable|date|after_or_equal:data_recebimento',
            'recebido_por'     => 'nullable|exists:users,id',
            'entregue_para'    => 'nullable|exists:users,id',
            'observacao'       => 'nullable|string|max:1000',
            'itens'            => 'required|array|min:1',
            'itens.*.consulta_preco_id'     => 'required|exists:consulta_precos,id',
            'itens.*.quantidade_solicitada' => 'required|numeric|min:0',
            'itens.*.quantidade_recebida'   => 'required|numeric|min:0',
            'itens.*.observacao'            => 'nullable|string|max:500',
            // Campos de produto — todos opcionais
            'itens.*.ncm'               => 'nullable|string|max:20',
            'itens.*.codigo_barras'     => 'nullable|string|max:50',
            'itens.*.sku'               => 'nullable|string|max:50',
            'itens.*.unidade_medida'    => 'nullable|string|max:20',
            'itens.*.peso'              => 'nullable|numeric|min:0',
            'itens.*.categoria_id'      => 'nullable|exists:categorias,id',
            'itens.*.sub_categoria_id'  => 'nullable|exists:sub_categorias,id',
        ]);

        DB::beginTransaction();

        try {
            $grupo = ConsultaPrecoGrupo::with('cliente')->findOrFail($request->grupo_id);

            // Soma já recebido em entradas anteriores
            $jaRecebidoMap = EntradaEncomendaItem::whereHas('entrada', fn($q) =>
                $q->where('grupo_id', $grupo->id)
            )->get()->groupBy('consulta_preco_id')->map(fn($itens) =>
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
                'entregue_para'    => $request->entregue_para ?? null,
                'cliente_id'       => $grupo->cliente_id,
                'data_recebimento' => $request->data_recebimento,
                'data_entrega'     => $request->data_entrega ?? null,
                'status'           => $request->filled('data_entrega') ? 'Entregue'
                    : ($todosCompletos ? 'Recebido completo' : 'Recebido parcialmente'),
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
                    // Campos de produto opcionais
                    'ncm'               => $itemData['ncm'] ?? null,
                    'codigo_barras'     => $itemData['codigo_barras'] ?? null,
                    'sku'               => $itemData['sku'] ?? null,
                    'unidade_medida'    => $itemData['unidade_medida'] ?? null,
                    'peso'              => $itemData['peso'] ?? null,
                    'categoria_id'      => $itemData['categoria_id'] ?? null,
                    'sub_categoria_id'  => $itemData['sub_categoria_id'] ?? null,
                ]);
            }

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
            'usuarios'
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
    public function edit(EntradaEncomenda $entradaEncomenda)
    {
        $entradaEncomenda->load([
            'grupo.cliente',
            'itens.consultaPreco',
        ]);

        $usuarios = \App\Models\User::orderBy('name')->get();

        return view('paginas.produtos.entrada_encomendas.edit', compact('entradaEncomenda', 'usuarios'));
    }

    // ──────────────────────────────────────────────────────────
    // UPDATE — atualiza entrada (ex: complementar itens faltantes)
    // ──────────────────────────────────────────────────────────
    public function update(Request $request, EntradaEncomenda $entradaEncomenda)
    {
        $request->validate([
            'data_entrega'  => 'nullable|date',
            'entregue_para' => 'nullable|exists:users,id',
            'observacao'    => 'nullable|string|max:1000',
            'itens'         => 'required|array',
            'itens.*.id'                     => 'required|exists:entrada_encomenda_itens,id',
            'itens.*.quantidade_recebida'    => 'required|numeric|min:0',
            'itens.*.observacao'             => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->itens as $itemData) {
                $item = EntradaEncomendaItem::findOrFail($itemData['id']);
                $novaQtd = (float)$itemData['quantidade_recebida'];

                $item->update([
                    'quantidade_recebida' => $novaQtd,
                    'recebido_completo'   => $novaQtd >= (float)$item->quantidade_solicitada,
                    'observacao'          => $itemData['observacao'] ?? $item->observacao,
                ]);
            }

            // Reavalia status da entrada
            $entradaEncomenda->refresh()->load('itens');
            $todosCompletos = $entradaEncomenda->estaCompleto();

            $entradaEncomenda->update([
                'entregue_para' => $request->entregue_para ?? $entradaEncomenda->entregue_para,
                'data_entrega'  => $request->data_entrega  ?? $entradaEncomenda->data_entrega,
                'status'        => $request->filled('data_entrega') ? 'Entregue'
                    : ($todosCompletos ? 'Recebido completo' : 'Recebido parcialmente'),
                'observacao'    => $request->observacao ?? $entradaEncomenda->observacao,
            ]);

            DB::commit();

            return redirect()
                ->route('entrada_encomendas.show', $entradaEncomenda->id)
                ->with('success', 'Entrada atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar entrada: ' . $e->getMessage());
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
