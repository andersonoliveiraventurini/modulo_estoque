<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimentacaoRequest;
use App\Http\Requests\UpdateMovimentacaoRequest;
use App\Models\Armazem;
use App\Models\Corredor;
use App\Models\Fornecedor;
use App\Models\Movimentacao;
use App\Models\Pedido;
use App\Models\PedidoCompra;
use App\Models\Posicao;
use App\Models\Produto;
use App\Models\InconsistenciaRecebimento;
use App\Models\User;
use App\Mail\InconsistenciaRecebimentoMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Log;

class MovimentacaoController extends Controller
{
    public function index()
    {
        $movimentacoes = Movimentacao::with(['itens.produto', 'itens.fornecedor', 'pedido', 'usuario', 'usuarioEditou', 'supervisor'])
            ->orderBy('id', 'desc')
            ->paginate(20);
        return view('paginas.movimentacao.index', compact('movimentacoes'));
    }

    public function create()
    {
        $fornecedores = Fornecedor::all();
        $pedidos = Pedido::all();
        $pedidoCompras = PedidoCompra::whereIn('status', ['aguardando', 'parcialmente_recebido'])
            ->with('fornecedor')
            ->latest()
            ->get();
        $armazens = Armazem::all();
        $produtos = Produto::with('cor')->where('ativo', true)->get(['id', 'nome', 'sku', 'fornecedor_id', 'cor_id']);
        return view('paginas.movimentacao.create', compact('fornecedores', 'pedidos', 'pedidoCompras', 'armazens', 'produtos'));
    }

    public function store(StoreMovimentacaoRequest $request)
    {
        Log::info('Iniciando criação de movimentação de estoque', [
            'user' => auth()->user()->name,
            'payload' => $request->except(['arquivo_nota_fiscal'])
        ]);

        try {
            DB::beginTransaction();

            $nfPath = null;
            if ($request->hasFile('arquivo_nota_fiscal')) {
                $nfPath = $request->file('arquivo_nota_fiscal')->store('notas_fiscais', 'public');
            }

            $movimentacao = Movimentacao::create([
                'tipo' => $request->tipo_entrada,
                'status' => 'pendente', // Sempre pendente na criação
                'data_movimentacao' => $request->data_movimentacao ?: now()->toDateString(),
                'pedido_id' => $request->pedido_id,
                'pedido_compra_id' => $request->pedido_compra_id,
                'nota_fiscal_fornecedor' => $request->nota_fiscal_fornecedor,
                'arquivo_nota_fiscal' => $nfPath,
                'romaneiro' => $request->romaneiro,
                'observacao' => $request->observacao,
                'usuario_id' => auth()->id(),
            ]);

            foreach ($request->produtos as $produtoData) {
                $movimentacao->itens()->create([
                    'fornecedor_id' => $produtoData['fornecedor_id'] ?? null,
                    'produto_id' => $produtoData['produto_id'],
                    'quantidade' => $produtoData['quantidade'],
                    'valor_unitario' => $produtoData['valor'] ?? 0,
                    'valor_total' => $produtoData['valor_total'] ?? 0,
                    'armazem_id' => $produtoData['armazem_id'] ?? null,
                    'corredor_id' => $produtoData['corredor_id'] ?? null,
                    'posicao_id' => $produtoData['posicao_id'] ?? null,
                    'endereco' => $produtoData['armazem'] ?? null,
                    'corredor' => $produtoData['corredor'] ?? null,
                    'posicao' => $produtoData['posicao'] ?? null,
                    'observacao' => $produtoData['observacao'] ?? null,
                ]);
                
                // O estoque NÃO é alterado aqui. Aguarda aprovação.
            }

            DB::commit();

            Log::info('Movimentação criada com sucesso (aguardando aprovação)', [
                'movimentacao_id' => $movimentacao->id
            ]);

            return redirect()->route('movimentacao.index')->with('success', 'Movimentação enviada para aprovação da supervisão!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Falha crítica ao criar movimentação de estoque', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            return back()->withInput()->withErrors(['error' => 'Erro interno ao processar movimentação. Por favor, tente novamente.']);
        }
    }

    /**
     * Aprova uma movimentação e efetiva a alteração no estoque.
     */
    public function aprovar(Movimentacao $movimentacao)
    {
        Log::info('Solicitação de aprovação de movimentação', [
            'movimentacao_id' => $movimentacao->id,
            'supervisor' => auth()->user()->name
        ]);

        if ($movimentacao->status !== 'pendente') {
            Log::warning('Tentativa de aprovar movimentação não pendente', ['movimentacao_id' => $movimentacao->id]);
            return back()->with('error', 'Esta movimentação já foi processada.');
        }

        try {
            DB::beginTransaction();

            foreach ($movimentacao->itens as $item) {
                $produto = $item->produto;
                if (!$produto) {
                    throw new \Exception("Produto ID {$item->produto_id} não encontrado.");
                }

                if ($movimentacao->tipo == 'entrada') {
                    $produto->addEstoque($item->quantidade);
                } else {
                    $produto->removerEstoque($item->quantidade);
                    app(\App\Services\EstoqueService::class)->verificarAlertaEstoqueBaixo($produto);
                }
            }

            $movimentacao->update([
                'status' => 'aprovado',
                'supervisor_id' => auth()->id(),
                'aprovado_em' => now(),
            ]);

            // Atualizar status do Pedido de Compra se houver
            if ($movimentacao->pedido_compra_id) {
                $this->atualizarStatusPedidoCompra($movimentacao->pedidoCompra);
            }

            DB::commit();

            Log::info('Movimentação aprovada e estoque atualizado', ['movimentacao_id' => $movimentacao->id]);

            return redirect()->route('movimentacao.index')->with('success', 'Movimentação aprovada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao aprovar movimentação', [
                'movimentacao_id' => $movimentacao->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erro ao aprovar: ' . $e->getMessage());
        }
    }

    /**
     * Rejeita uma movimentação.
     */
    public function rejeitar(Movimentacao $movimentacao)
    {
        if ($movimentacao->status !== 'pendente') {
            return back()->with('error', 'Esta movimentação já foi processada.');
        }

        $movimentacao->update([
            'status' => 'rejeitado',
            'supervisor_id' => auth()->id(),
        ]);

        Log::info('Movimentação rejeitada pelo supervisor', [
            'movimentacao_id' => $movimentacao->id,
            'supervisor' => auth()->user()->name
        ]);

        return redirect()->route('movimentacao.index')->with('success', 'Movimentação rejeitada.');
    }

    private function atualizarStatusPedidoCompra(PedidoCompra $pedido)
    {
        $pedido->load('itens');
        
        $recebidoTotalmente = true;
        $recebidoParcialmente = false;
        $pendencias = [];

        foreach ($pedido->itens as $item) {
            $qtdRecebida = DB::table('movimentacao_produtos')
                ->join('movimentacoes', 'movimentacoes.id', '=', 'movimentacao_produtos.movimentacao_id')
                ->where('movimentacoes.pedido_compra_id', $pedido->id)
                ->where('movimentacao_produtos.produto_id', $item->produto_id)
                ->where('movimentacoes.tipo', 'entrada')
                ->where('movimentacoes.status', 'aprovado') // Apenas aprovadas contam
                ->whereNull('movimentacoes.deleted_at')
                ->sum('movimentacao_produtos.quantidade');

            if ($qtdRecebida < $item->quantidade) {
                $recebidoTotalmente = false;
                if ($qtdRecebida > 0) {
                    $recebidoParcialmente = true;
                }
                $falta = $item->quantidade - $qtdRecebida;
                $pendencias[] = "- " . ($item->produto->nome ?? 'Produto ID '.$item->produto_id) . ": Falta {$falta}";
            } else if ($qtdRecebida > 0) {
                $recebidoParcialmente = true;
            }
        }

        $novoStatus = 'aguardando';
        if ($recebidoTotalmente) {
            $novoStatus = 'recebido';
        } elseif ($recebidoParcialmente) {
            $novoStatus = 'parcialmente_recebido';
        }

        $obsAdicional = "";
        if ($novoStatus === 'parcialmente_recebido' && !empty($pendencias)) {
            $obsAdicional = "\n\n[RECEBIMENTO PARCIAL - " . now()->format('d/m/Y H:i') . "]\n" . implode("\n", $pendencias);
        }

        $pedido->update([
            'status' => $novoStatus,
            'observacao' => $pedido->observacao . $obsAdicional
        ]);
    }

    public function show(Movimentacao $movimentacao)
    {
        $movimentacao->load(['itens.produto', 'itens.fornecedor', 'pedido', 'supervisor']);
        return view('paginas.movimentacao.show', compact('movimentacao'));
    }

    public function edit(Movimentacao $movimentacao)
    {
        if ($movimentacao->status !== 'pendente') {
            return redirect()->route('movimentacao.index')->with('error', 'Apenas movimentações pendentes podem ser editadas.');
        }

        $fornecedores = Fornecedor::all();
        $pedidos = Pedido::all();
        $armazens = Armazem::all();
        $produtos = Produto::with('cor')->where('ativo', true)->get(['id', 'nome', 'sku', 'fornecedor_id', 'cor_id']);
        $movimentacao->load('itens');
        return view('paginas.movimentacao.edit', compact('movimentacao', 'fornecedores', 'pedidos', 'armazens', 'produtos'));
    }

    public function update(UpdateMovimentacaoRequest $request, Movimentacao $movimentacao)
    {
        if ($movimentacao->status !== 'pendente') {
             return redirect()->route('movimentacao.index')->with('error', 'Apenas movimentações pendentes podem ser editadas.');
        }

        Log::info('Iniciando atualização de movimentação pendente', [
            'movimentacao_id' => $movimentacao->id,
            'user' => auth()->user()->name
        ]);

        try {
            DB::beginTransaction();

            // Como ainda é pendente, não houve alteração de estoque real.
            // Apenas atualizamos os dados.

            // 1. Calcular resumo para log de auditoria
            $estoqueAntigo = $movimentacao->itens->pluck('quantidade', 'produto_id')->toArray();
            $mudancas = [];

            // Deletar os itens antigos
            $movimentacao->itens()->delete();

            // Atualizar movimentação pai
            $nfPath = $movimentacao->arquivo_nota_fiscal;
            if ($request->hasFile('arquivo_nota_fiscal')) {
                if ($nfPath) Storage::disk('public')->delete($nfPath);
                $nfPath = $request->file('arquivo_nota_fiscal')->store('notas_fiscais', 'public');
            }

            $movimentacao->update([
                'tipo' => $request->tipo_entrada,
                'data_movimentacao' => $request->data_movimentacao ?: $movimentacao->data_movimentacao,
                'pedido_id' => $request->pedido_id,
                'nota_fiscal_fornecedor' => $request->nota_fiscal_fornecedor,
                'arquivo_nota_fiscal' => $nfPath,
                'romaneiro' => $request->romaneiro,
                'observacao' => $request->observacao,
                'usuario_editou_id' => auth()->id(),
            ]);

            // Inserir os novos itens
            foreach ($request->produtos as $produtoData) {
                $movimentacao->itens()->create([
                    'fornecedor_id' => $produtoData['fornecedor_id'] ?? null,
                    'produto_id' => $produtoData['produto_id'],
                    'quantidade' => $produtoData['quantidade'],
                    'valor_unitario' => $produtoData['valor'] ?? 0,
                    'valor_total' => $produtoData['valor_total'] ?? 0,
                    'endereco' => $produtoData['armazem'] ?? null,
                    'corredor' => $produtoData['corredor'] ?? null,
                    'posicao' => $produtoData['posicao'] ?? null,
                    'observacao' => $produtoData['observacao'] ?? null,
                ]);
            }

            DB::commit();

            Log::info('Movimentação atualizada com sucesso', ['movimentacao_id' => $movimentacao->id]);

            return redirect()->route('movimentacao.index')->with('success', 'Movimentação atualizada e aguardando aprovação!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar movimentação', ['error' => $e->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    public function destroy(Movimentacao $movimentacao)
    {
        Log::info('Solicitação de exclusão de movimentação', [
            'movimentacao_id' => $movimentacao->id,
            'status' => $movimentacao->status,
            'user' => auth()->user()->name
        ]);

        try {
            DB::beginTransaction();

            // Se for aprovada, precisa estornar o estoque
            if ($movimentacao->status == 'aprovado') {
                foreach ($movimentacao->itens as $item) {
                    $produto = $item->produto;
                    if ($produto) {
                        if ($movimentacao->tipo == 'entrada') {
                            $produto->removerEstoque($item->quantidade);
                        } else {
                            $produto->addEstoque($item->quantidade);
                        }
                    }
                }
                Log::info('Estoque estornado devido a exclusão de movimentação aprovada', ['movimentacao_id' => $movimentacao->id]);
            }

            $movimentacao->itens()->delete();
            $movimentacao->delete();

            DB::commit();

            if ($movimentacao->pedido_compra_id && $movimentacao->status == 'aprovado') {
                $this->atualizarStatusPedidoCompra($movimentacao->pedidoCompra);
            }

            return redirect()->route('movimentacao.index')->with('success', 'Movimentação removida com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir movimentação', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erro ao deletar: ' . $e->getMessage()]);
        }
    }
}
