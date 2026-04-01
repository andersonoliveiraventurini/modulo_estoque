<?php

namespace App\Livewire\Estoque;

use App\Models\Armazem;
use App\Models\Corredor;
use App\Models\HubStock;
use App\Models\Movimentacao;
use App\Models\Posicao;
use App\Models\Produto;
use App\Services\ReposicaoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;

use App\Events\StockMovementRegistered;

#[Title('Reposição Manual – HUB')]
class StockReplenishment extends Component
{
    public $produtoId;
    public $orcamentoId; // Novo campo
    public $quantidade = 1;
    public $armazemOrigemId;
    public $corredorOrigemId;
    public $posicaoOrigemId;
    public $colaboradorId; // Novo campo
    
    public $armazensOrigem = [];
    public $corredoresOrigem = [];
    public $posicoesOrigem = [];
    public $vendedores = []; // Para selecionar o colaborador que está operando
    public $orcamentos = []; // Para selecionar o orçamento vinculado

    public function mount($orcamentoId = null)
    {        $this->orcamentoId = $orcamentoId;
        // Apenas armazéns que NÃO são HUB
        $this->armazensOrigem = Armazem::where('tipo', '!=', 'hub')->where('is_active', true)->get();
        // Colaboradores que podem realizar a movimentação
        $this->vendedores = \App\Models\User::all();
        // Orçamentos aprovados
        $this->orcamentos = \App\Models\Orcamento::where('status', 'Aprovado')->latest()->get();
    }

    public function updatedArmazemOrigemId($value)
    {
        $this->corredoresOrigem = $value ? Corredor::where('armazem_id', $value)->get() : [];
        $this->corredorOrigemId = null;
        $this->posicoesOrigem = [];
        $this->posicaoOrigemId = null;
    }

    public function updatedCorredorOrigemId($value)
    {
        $this->posicoesOrigem = $value ? Posicao::where('corredor_id', $value)->get() : [];
        $this->posicaoOrigemId = null;
    }

    public function transferir()
    {
        $this->validate([
            'produtoId'        => 'required|exists:produtos,id',
            'quantidade'       => 'required|numeric|min:0.001',
            'armazemOrigemId'  => 'required|exists:armazens,id',
            'corredorOrigemId' => 'nullable|exists:corredores,id',
            'posicaoOrigemId'  => 'nullable|exists:posicoes,id',
            'colaboradorId'    => 'required|exists:users,id',
            'orcamentoId'      => 'nullable|exists:orcamentos,id',
        ], [
            'produtoId.required'       => 'Selecione o produto.',
            'quantidade.min'           => 'A quantidade deve ser maior que zero.',
            'armazemOrigemId.required' => 'Selecione o armazém de origem.',
            'colaboradorId.required'   => 'Selecione o colaborador que executa a ação.',
        ]);

        try {
            DB::transaction(function () {
                $produto = Produto::lockForUpdate()->findOrFail($this->produtoId);
                
                // 1. Saída do Secundário
                $saida = Movimentacao::create([
                    'tipo'              => 'saida_para_hub',
                    'status'            => 'aprovado',
                    'data_movimentacao' => now()->toDateString(),
                    'observacao'        => "Transferência Manual para HUB - Executado por Colaborador #{$this->colaboradorId}" . ($this->orcamentoId ? " (Vinculado ao Orçamento #{$this->orcamentoId})" : ""),
                    'is_reposicao'      => true,
                    'usuario_id'        => auth()->id(), // Operador Logado
                    'aprovado_em'       => now(),
                ]);

                $saida->itens()->create([
                    'produto_id'  => $this->produtoId,
                    'quantidade'  => $this->quantidade,
                    'armazem_id'  => $this->armazemOrigemId,
                    'corredor_id' => $this->corredorOrigemId,
                    'posicao_id'  => $this->posicaoOrigemId,
                ]);

                // Registrar Log de Saída do Secundário
                event(new StockMovementRegistered([
                    'produto_id' => $this->produtoId,
                    'posicao_id' => $this->posicaoOrigemId,
                    'tipo_movimentacao' => 'replenishment',
                    'quantidade' => -$this->quantidade,
                    'colaborador_id' => $this->colaboradorId,
                    'orcamento_id' => $this->orcamentoId,
                    'observacao' => "Reposição HUB (Saída Secundário) - Movimentação #{$saida->id}",
                ]));

                // 2. Entrada no HUB (ID 1 fixo)
                $entrada = Movimentacao::create([
                    'tipo'              => 'entrada_hub',
                    'status'            => 'aprovado',
                    'data_movimentacao' => now()->toDateString(),
                    'observacao'        => "Recebimento Manual do HUB (Origem: Armazém #{$this->armazemOrigemId})",
                    'is_reposicao'      => true,
                    'usuario_id'        => auth()->id(),
                    'aprovado_em'       => now(),
                ]);

                $entrada->itens()->create([
                    'produto_id' => $this->produtoId,
                    'quantidade' => $this->quantidade,
                    'armazem_id' => 1, // HUB
                ]);

                // Registrar Log de Entrada no HUB
                event(new StockMovementRegistered([
                    'produto_id' => $this->produtoId,
                    'posicao_id' => null, // No HUB, saldo rápido
                    'tipo_movimentacao' => 'replenishment',
                    'quantidade' => $this->quantidade,
                    'colaborador_id' => $this->colaboradorId,
                    'orcamento_id' => $this->orcamentoId,
                    'observacao' => "Reposição HUB (Entrada HUB) - Movimentação #{$entrada->id}",
                ]));

                // 3. Atualizar HubStock
                $hubStock = HubStock::firstOrCreate(
                    ['produto_id' => $this->produtoId],
                    ['quantidade' => 0, 'quantidade_reservada' => 0]
                );
                $hubStock->increment('quantidade', $this->quantidade);

                // 4. Se houver vínculo com orçamento, mover reserva de 'Geral' para 'HUB'
                if ($this->orcamentoId) {
                    $reservaGeral = \App\Models\EstoqueReserva::where('orcamento_id', $this->orcamentoId)
                        ->where('produto_id', $this->produtoId)
                        ->whereNull('armazem_id')
                        ->where('status', 'ativa')
                        ->first();
                    
                    if ($reservaGeral) {
                        $qtdMover = min($reservaGeral->quantidade, $this->quantidade);
                        
                        // Reduz do geral
                        if ($reservaGeral->quantidade == $qtdMover) {
                            $reservaGeral->delete();
                        } else {
                            $reservaGeral->decrement('quantidade', $qtdMover);
                        }

                        // Adiciona no HUB
                        \App\Models\EstoqueReserva::create([
                            'orcamento_id'  => $this->orcamentoId,
                            'produto_id'    => $this->produtoId,
                            'armazem_id'    => 1, // HUB
                            'quantidade'    => $qtdMover,
                            'status'        => 'ativa',
                            'criado_por_id' => auth()->id(),
                        ]);
                    }
                }

                Log::info('StockReplenishment: transferência concluída', [
                    'produto'    => $produto->nome,
                    'quantidade' => $this->quantidade,
                    'origem'     => $this->armazemOrigemId,
                ]);
            });

            session()->flash('success', 'Transferência realizada com sucesso!');
            $this->reset(['produtoId', 'quantidade', 'armazemOrigemId', 'corredorOrigemId', 'posicaoOrigemId', 'orcamentoId']);
            $this->corredoresOrigem = [];
            $this->posicoesOrigem = [];

        } catch (\Exception $e) {
            Log::error('StockReplenishment: erro na transferência', ['error' => $e->getMessage()]);
            $this->addError('transferencia', 'Erro ao realizar transferência: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.estoque.stock-replenishment', [
            'produtos' => Produto::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }
}
