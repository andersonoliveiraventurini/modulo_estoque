<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimentacaoRequest;
use App\Http\Requests\UpdateMovimentacaoRequest;
use App\Models\Armazem;
use App\Models\Fornecedor;
use App\Models\Movimentacao;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;

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
        $armazens = Armazem::all();
        $produtos = Produto::with('cor')->where('ativo', true)->get(['id', 'nome', 'sku', 'fornecedor_id', 'cor_id']);
        return view('paginas.movimentacao.create', compact('fornecedores', 'pedidos', 'armazens', 'produtos'));
    }

    public function store(StoreMovimentacaoRequest $request)
    {
        try {
            DB::beginTransaction();

            $movimentacao = Movimentacao::create([
                'tipo' => $request->tipo_entrada,
                'pedido_id' => $request->pedido_id,
                'nota_fiscal_fornecedor' => $request->nota_fiscal_fornecedor,
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
                    } else {
                        $produto->removerEstoque($produtoData['quantidade']);
                    }
                }
            }

            DB::commit();
            return redirect()->route('movimentacao.index')->with('success', 'Movimentação criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Erro ao salvar: ' . $e->getMessage()]);
        }
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
            $movimentacao->update([
                'tipo' => $request->tipo_entrada,
                'pedido_id' => $request->pedido_id,
                'nota_fiscal_fornecedor' => $request->nota_fiscal_fornecedor,
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
            return redirect()->route('movimentacao.index')->with('success', 'Movimentação deletada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao deletar: ' . $e->getMessage()]);
        }
    }
}
