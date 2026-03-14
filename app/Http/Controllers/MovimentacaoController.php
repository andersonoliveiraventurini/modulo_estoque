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

class MovimentacaoController extends Controller
{
    public function index()
    {
        $movimentacoes = Movimentacao::with(['itens.produto', 'itens.fornecedor', 'pedido', 'usuario', 'usuarioEditou'])->orderBy('id', 'desc')->get();
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
        try {
            DB::beginTransaction();

            $nfPath = null;
            if ($request->hasFile('arquivo_nota_fiscal')) {
                $nfPath = $request->file('arquivo_nota_fiscal')->store('notas_fiscais', 'public');
            }

            $movimentacao = Movimentacao::create([
                'tipo' => $request->tipo_entrada,
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
                    'endereco' => $produtoData['armazem'] ?? null,
                    'corredor' => $produtoData['corredor'] ?? null,
                    'posicao' => $produtoData['posicao'] ?? null,
                    'observacao' => $produtoData['observacao'] ?? null,
                ]);

                // Atualizar o estoque
                $produto = Produto::find($produtoData['produto_id']);
                if ($produto) {
                    if ($request->tipo_entrada == 'entrada') {
                        $produto->addEstoque($produtoData['quantidade']);
                        
                        // Checar Inconsistência se houver Pedido de Compra vinculado
                        if ($request->pedido_compra_id) {
                            $pcItem = DB::table('pedido_compra_itens')
                                ->where('pedido_compra_id', $request->pedido_compra_id)
                                ->where('produto_id', $produtoData['produto_id'])
                                ->first();

                            if ($pcItem) {
                                // Qtd já recebida anteriormente (exclua a atual da conta)
                                $qtdRecebidaJa = DB::table('movimentacao_produtos')
                                    ->join('movimentacoes', 'movimentacoes.id', '=', 'movimentacao_produtos.movimentacao_id')
                                    ->where('movimentacoes.pedido_compra_id', $request->pedido_compra_id)
                                    ->where('movimentacao_produtos.produto_id', $produtoData['produto_id'])
                                    ->where('movimentacoes.id', '<>', $movimentacao->id)
                                    ->sum('movimentacao_produtos.quantidade');

                                $totalEsperado = $pcItem->quantidade;
                                $totalRecebidoComEsta = $qtdRecebidaJa + $produtoData['quantidade'];

                                if ($totalRecebidoComEsta > $totalEsperado) {
                                    // Registrar Inconsistência
                                    $inconsistencia = InconsistenciaRecebimento::create([
                                        'pedido_compra_id' => $request->pedido_compra_id,
                                        'produto_id' => $produtoData['produto_id'],
                                        'quantidade_esperada' => $totalEsperado,
                                        'quantidade_recebida' => $totalRecebidoComEsta,
                                        'usuario_id' => auth()->id(),
                                        'movimentacao_id' => $movimentacao->id,
                                        'observacao' => "Recebimento excedente: {$totalRecebidoComEsta} vs esperado {$totalEsperado}",
                                    ]);

                                    // Enviar e-mail para Administradores
                                    $admins = User::permission('admin')->get(); 
                                    if ($admins->isEmpty()) {
                                        // Fallback para admin role se permission falhar ou não existirem usuários diretos
                                        $admins = User::role('admin')->get();
                                    }

                                    if ($admins->isNotEmpty()) {
                                        Mail::to($admins)->send(new InconsistenciaRecebimentoMail($inconsistencia));
                                    }
                                }
                            }
                        }
                    } else {
                        $produto->removerEstoque($produtoData['quantidade']);
                    }
                }
            }

            DB::commit();

            // Atualizar status do Pedido de Compra se houver
            if ($request->pedido_compra_id) {
                $this->atualizarStatusPedidoCompra(PedidoCompra::find($request->pedido_compra_id));
            }

            return redirect()->route('movimentacao.index')->with('success', 'Movimentação criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
    }

    private function atualizarStatusPedidoCompra(PedidoCompra $pedido)
    {
        $pedido->load('itens');
        
        $recebidoTotalmente = true;
        $recebidoParcialmente = false;
        $pendencias = [];

        foreach ($pedido->itens as $item) {
            // Soma todas as movimentações de ENTRADA para este produto vinculadas a este pedido
            $qtdRecebida = DB::table('movimentacao_produtos')
                ->join('movimentacoes', 'movimentacoes.id', '=', 'movimentacao_produtos.movimentacao_id')
                ->where('movimentacoes.pedido_compra_id', $pedido->id)
                ->where('movimentacao_produtos.produto_id', $item->produto_id)
                ->where('movimentacoes.tipo', 'entrada')
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
        $movimentacao->load(['itens.produto', 'itens.fornecedor', 'pedido']);
        return view('paginas.movimentacao.show', compact('movimentacao'));
    }

    public function edit(Movimentacao $movimentacao)
    {
        $fornecedores = Fornecedor::all();
        $pedidos = Pedido::all();
        $armazens = Armazem::all();
        $produtos = Produto::with('cor')->where('ativo', true)->get(['id', 'nome', 'sku', 'fornecedor_id', 'cor_id']);
        $movimentacao->load('itens');
        return view('paginas.movimentacao.edit', compact('movimentacao', 'fornecedores', 'pedidos', 'armazens', 'produtos'));
    }

    public function update(UpdateMovimentacaoRequest $request, Movimentacao $movimentacao)
    {
        try {
            DB::beginTransaction();

            // Mapearemos o que tinha no banco (por NOME Produto) e qtd
            $estoqueAntigo = [];
            
            // Estornar estoque antigo e guardar referências
            foreach ($movimentacao->itens as $itemAntigo) {
                $produto = Produto::find($itemAntigo->produto_id);
                if ($produto) {
                    // Armazena pro relatorio: "Produto Y" => qtd
                    if(!isset($estoqueAntigo[$produto->nome])) {
                        $estoqueAntigo[$produto->nome] = 0;
                    }
                    $estoqueAntigo[$produto->nome] += $itemAntigo->quantidade;

                    if ($movimentacao->tipo == 'entrada') {
                        $produto->removerEstoque($itemAntigo->quantidade);
                    } else {
                        $produto->addEstoque($itemAntigo->quantidade);
                    }
                }
            }

            // Mapear o que está vindo no request
            $estoqueNovo = [];
            foreach ($request->produtos as $produtoData) {
                $produto = Produto::find($produtoData['produto_id']);
                if ($produto) {
                    if(!isset($estoqueNovo[$produto->nome])) {
                        $estoqueNovo[$produto->nome] = 0;
                    }
                    $estoqueNovo[$produto->nome] += $produtoData['quantidade'];
                }
            }

            // Criar o resumo comparativo escrito
            $mudancas = [];
            
            // 1. Produtos que já existiam (mudaram de qtd ou foram removidos)
            foreach ($estoqueAntigo as $nomeProd => $qtdAntiga) {
                $qtdNova = $estoqueNovo[$nomeProd] ?? 0;
                
                if ($qtdAntiga != $qtdNova) {
                    if ($qtdNova == 0) {
                        $mudancas[] = "{$nomeProd} (removido)";
                    } else {
                        $mudancas[] = "{$nomeProd} de {$qtdAntiga} para {$qtdNova}";
                    }
                }
                // Tira do array novo pra sobrar apenas os recém inseridos
                unset($estoqueNovo[$nomeProd]);
            }

            // 2. Sobraram apenas produtos que são novidades (não estavam na movimentacao antes)
            foreach ($estoqueNovo as $nomeProd => $qtdNova) {
                if ($qtdNova > 0) {
                    $mudancas[] = "{$nomeProd} (adicionado +{$qtdNova})";
                }
            }

            $resumoEdicao = null;
            if (count($mudancas) > 0) {
                $resumoEdicao = "Alterou itens: " . implode(', ', $mudancas);
            }

            // Deletar os itens antigos (forçamos limpar pra recriar)
            $movimentacao->itens()->delete();

            // Atualizar movimentação pai
            $nfPath = $movimentacao->arquivo_nota_fiscal;
            if ($request->hasFile('arquivo_nota_fiscal')) {
                // Apaga o arquivo antigo se existir
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
                'resumo_edicao' => $resumoEdicao,
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

                // Ajustar novo saldo de estoque
                $produto = Produto::find($produtoData['produto_id']);
                if ($produto) {
                    if ($request->tipo_entrada == 'entrada') {
                        $produto->addEstoque($produtoData['quantidade']);
                    } else {
                        $produto->removerEstoque($produtoData['quantidade']);
                    }
                }
            }

            DB::commit();

            if ($movimentacao->pedido_compra_id) {
                $this->atualizarStatusPedidoCompra(PedidoCompra::find($movimentacao->pedido_compra_id));
            }

            return redirect()->route('movimentacao.index')->with('success', 'Movimentação atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erro ao atualizar: ' . $e->getMessage()]);
        }
    }

    public function destroy(Movimentacao $movimentacao)
    {
        try {
            DB::beginTransaction();

            // Estornar estoque antes de deletar
            foreach ($movimentacao->itens as $item) {
                $produto = Produto::find($item->produto_id);
                if ($produto) {
                    if ($movimentacao->tipo == 'entrada') {
                        $produto->removerEstoque($item->quantidade);
                    } else {
                        $produto->addEstoque($item->quantidade);
                    }
                }
            }

            $movimentacao->itens()->delete();
            $movimentacao->delete();

            DB::commit();

            if ($movimentacao->pedido_compra_id) {
                $this->atualizarStatusPedidoCompra(PedidoCompra::find($movimentacao->pedido_compra_id));
            }

            return redirect()->route('movimentacao.index')->with('success', 'Movimentação deletada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao deletar: ' . $e->getMessage()]);
        }
    }
}
