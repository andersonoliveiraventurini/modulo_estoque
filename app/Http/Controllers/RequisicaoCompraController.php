<?php

namespace App\Http\Controllers;

use App\Models\RequisicaoCompra;
use App\Models\RequisicaoCompraItem;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RequisicaoCompraController extends Controller
{
    public function index()
    {
        $requisicoes = RequisicaoCompra::with('solicitante')
            ->latest()
            ->paginate(20);

        return view('paginas.requisicao_compras.index', compact('requisicoes'));
    }

    public function create()
    {
        $produtos = Produto::select('id', 'nome', 'sku')->get();
        return view('paginas.requisicao_compras.create', compact('produtos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data_requisicao' => 'required|date',
            'observacao'      => 'nullable|string',
            'itens'           => 'required|array|min:1',
            'itens.*.produto_id' => 'nullable|exists:produtos,id',
            'itens.*.quantidade' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($request) {
            $valorTotal = 0;
            foreach ($request->itens as $item) {
                $valorTotal += ($item['quantidade'] * ($item['valor_unitario_estimado'] ?? 0));
            }

            $requisicao = RequisicaoCompra::create([
                'solicitante_id' => auth()->id(),
                'data_requisicao' => $request->data_requisicao,
                'observacao' => $request->observacao,
                'status' => 'pendente',
                'valor_estimado' => $valorTotal,
                'nivel_aprovacao' => 1, // Inicia sempre no nível 1
            ]);

            foreach ($request->itens as $item) {
                RequisicaoCompraItem::create([
                    'requisicao_compra_id' => $requisicao->id,
                    'produto_id' => $item['produto_id'] ?? null,
                    'descricao_livre' => $item['descricao_livre'] ?? null,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario_estimado' => $item['valor_unitario_estimado'] ?? 0,
                ]);
            }
        });

        return redirect()->route('requisicao_compras.index')->with('success', 'Requisição de compra enviada!');
    }

    public function show(RequisicaoCompra $requisicaoCompra)
    {
        $requisicaoCompra->load(['solicitante', 'itens.produto', 'aprovador']);
        return view('paginas.requisicao_compras.show', compact('requisicaoCompra'));
    }

    public function aprovar(RequisicaoCompra $requisicaoCompra)
    {
        $user = Auth::user();
        $valorTotal = $requisicaoCompra->valor_estimado;
        $nivelAtual = $requisicaoCompra->nivel_aprovacao;

        // Lógica de níveis baseada em valor (Exemplo)
        // Nível 1: Supervisor (Até R$ 1.000)
        // Nível 2: Gerente (Até R$ 5.000)
        // Nível 3: Diretor (Acima de R$ 5.000)
        
        $proximoNivel = null;
        if ($valorTotal > 5000 && $nivelAtual < 3) {
            $proximoNivel = 3;
        } elseif ($valorTotal > 1000 && $nivelAtual < 2) {
            $proximoNivel = 2;
        }

        $historico = $requisicaoCompra->aprovacoes_json ?? [];
        $historico[] = [
            'nivel' => $nivelAtual,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'data' => now()->toDateTimeString(),
        ];

        if ($proximoNivel) {
            $requisicaoCompra->update([
                'nivel_aprovacao' => $proximoNivel,
                'aprovacoes_json' => $historico,
                'status' => 'pendente', // Mantém pendente até o último nível
            ]);
            
            Log::info("Requisição #{$requisicaoCompra->id} aprovada no nível {$nivelAtual}. Aguardando nível {$proximoNivel}.");
            return back()->with('success', "Aprovado nível {$nivelAtual}. Agora aguarda aprovação superior.");
        }

        $requisicaoCompra->update([
            'status' => 'aprovada',
            'aprovador_id' => $user->id,
            'aprovado_em' => now(),
            'aprovacoes_json' => $historico,
        ]);

        Log::info("Requisição #{$requisicaoCompra->id} totalmente aprovada.");

        // Notificar equipe de compras e admins
        $admins = \App\Models\User::role('admin')->get();
        $compras = \App\Models\User::role('compras')->get();
        $recipients = $admins->concat($compras)->unique('id');

        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Mail::to($recipients)->send(new \App\Mail\RequisicaoAprovadaAlerta($requisicaoCompra));
        }

        return back()->with('success', 'Requisição aprovada com sucesso! A equipe de compras foi notificada.');
    }

    public function rejeitar(RequisicaoCompra $requisicaoCompra, Request $request)
    {
        $request->validate(['motivo' => 'required|string|min:5']);

        $requisicaoCompra->update([
            'status' => 'rejeitada',
            'rejeitado_por_id' => Auth::id(),
            'rejeitado_em' => now(),
            'observacao' => $requisicaoCompra->observacao . "\nMOTIVO REJEIÇÃO: " . $request->motivo,
        ]);

        Log::warning("Requisição #{$requisicaoCompra->id} rejeitada por " . Auth::user()->name);

        return back()->with('success', 'Requisição rejeitada.');
    }

    public function gerarPedido(RequisicaoCompra $requisicaoCompra)
    {
        if ($requisicaoCompra->status !== 'aprovada') {
            return back()->with('error', 'Apenas requisições aprovadas podem gerar pedidos.');
        }

        if ($requisicaoCompra->pedidoCompra()->exists()) {
            return back()->with('error', 'Esta requisição já possui um pedido de compra vinculado.');
        }

        try {
            DB::beginTransaction();

            // Criar Pedido de Compra (Rascunho)
            $pedido = \App\Models\PedidoCompra::create([
                'requisicao_compra_id' => $requisicaoCompra->id,
                'usuario_id'           => auth()->id(),
                'data_pedido'          => now(),
                'status'               => 'aguardando',
                'observacao'           => "Gerado automaticamente a partir da Requisição #{$requisicaoCompra->id}.\n" . $requisicaoCompra->observacao,
                'valor_total'          => $requisicaoCompra->valor_estimado,
                // O fornecedor deve ser definido manualmente pelo comprador após a geração
                'fornecedor_id'        => $requisicaoCompra->itens()->first()->produto->fornecedor_id ?? 1, // Fallback ou primeiro produto
            ]);

            // Copiar Itens
            foreach ($requisicaoCompra->itens as $item) {
                \App\Models\PedidoCompraItem::create([
                    'pedido_compra_id' => $pedido->id,
                    'produto_id'       => $item->produto_id,
                    'descricao_livre'  => $item->descricao_livre,
                    'quantidade'       => $item->quantidade,
                    'valor_unitario'   => $item->valor_unitario_estimado,
                    'valor_total'      => $item->quantidade * $item->valor_unitario_estimado,
                ]);
            }

            DB::commit();

            Log::info("Pedido de Compra #{$pedido->id} gerado a partir da Requisição #{$requisicaoCompra->id}");

            return redirect()->route('pedido_compras.edit', $pedido->id)
                ->with('success', 'Pedido de compra gerado com sucesso! Por favor, revise os dados e selecione o fornecedor.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao gerar pedido para requisição #{$requisicaoCompra->id}: " . $e->getMessage());
            return back()->with('error', 'Erro interno ao gerar pedido. Verifique os logs.');
        }
    }
}
