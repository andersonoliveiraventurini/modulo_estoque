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
        // O grupo pode vir por query string ?grupo_id=X
        $grupoId = $request->get('grupo_id');
        $grupo   = $grupoId
            ? ConsultaPrecoGrupo::with(['cliente', 'itens.fornecedorSelecionado.fornecedor', 'usuario'])
            ->findOrFail($grupoId)
            : null;

        // Lista grupos aprovados que ainda não têm entrada completa
        $gruposDisponiveis = ConsultaPrecoGrupo::with(['cliente', 'itens'])
            ->where('status', 'Aprovado')
            ->whereDoesntHave('entradas', fn($q) => $q->where('status', 'Recebido completo'))
            ->orderBy('created_at', 'desc')
            ->get();

        $usuarios = \App\Models\User::orderBy('name')->get();

        return view('paginas.produtos.entrada_encomendas.create', compact(
            'grupo',
            'gruposDisponiveis',
            'usuarios'
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
        ]);

        DB::beginTransaction();

        try {
            $grupo = ConsultaPrecoGrupo::with('cliente')->findOrFail($request->grupo_id);

            // ── Calcula o pendente consolidado ANTES desta entrada ───────────
            // Soma tudo já recebido em entradas anteriores para cada item
            $jaRecebidoMap = EntradaEncomendaItem::whereHas('entrada', fn($q) =>
                $q->where('grupo_id', $grupo->id)
            )->get()->groupBy('consulta_preco_id')->map(fn($itens) =>
                $itens->sum('quantidade_recebida')
            );

            // Verifica se todos os itens desta entrada ficam completos
            // considerando o acumulado total (anteriores + esta entrada)
            $todosCompletos = true;
            foreach ($request->itens as $itemData) {
                $cpId          = $itemData['consulta_preco_id'];
                $jaRecebido    = (float) ($jaRecebidoMap[$cpId] ?? 0);
                $novaQtd       = (float) $itemData['quantidade_recebida'];
                $totalSolicitado = (float) $itemData['quantidade_solicitada'];

                // quantidade_solicitada no form é o total original do item na cotação
                if (($jaRecebido + $novaQtd) < $totalSolicitado) {
                    $todosCompletos = false;
                    break;
                }
            }

            $statusEntrada = $todosCompletos ? 'Recebido completo' : 'Recebido parcialmente';

            $entrada = EntradaEncomenda::create([
                'grupo_id'         => $grupo->id,
                'recebido_por'     => $request->filled('recebido_por') ? $request->recebido_por : auth()->id(),
                'entregue_para'    => $request->entregue_para ?? null,
                'cliente_id'       => $grupo->cliente_id,
                'data_recebimento' => $request->data_recebimento,
                'data_entrega'     => $request->data_entrega ?? null,
                'status'           => $request->filled('data_entrega') ? 'Entregue' : $statusEntrada,
                'observacao'       => $request->observacao ?? null,
            ]);

            foreach ($request->itens as $itemData) {
                $cpId            = $itemData['consulta_preco_id'];
                $jaRecebido      = (float) ($jaRecebidoMap[$cpId] ?? 0);
                $novaQtd         = (float) $itemData['quantidade_recebida'];
                $totalSolicitado = (float) $itemData['quantidade_solicitada'];

                // recebido_completo = acumulado total >= solicitado na cotação
                $estaCompleto = ($jaRecebido + $novaQtd) >= $totalSolicitado;

                EntradaEncomendaItem::create([
                    'entrada_id'            => $entrada->id,
                    'consulta_preco_id'     => $cpId,
                    'quantidade_solicitada' => $totalSolicitado,   // total original
                    'quantidade_recebida'   => $novaQtd,           // só o que chegou nesta entrada
                    'recebido_completo'     => $estaCompleto,      // baseado no acumulado
                    'observacao'            => $itemData['observacao'] ?? null,
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
        ]);

        $grupo = $entradaEncomenda->grupo;

        // Soma tudo que já foi recebido para cada item, em todas as entradas do grupo
        $recebidoMap = EntradaEncomendaItem::whereHas(
            'entrada',
            fn($q) =>
            $q->where('grupo_id', $grupo->id)
        )->get()->groupBy('consulta_preco_id')->map(
            fn($itens) =>
            $itens->sum('quantidade_recebida')
        );

        // Monta resumo por item
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
