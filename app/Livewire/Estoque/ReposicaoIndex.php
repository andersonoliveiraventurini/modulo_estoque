<?php

namespace App\Livewire\Estoque;

use App\Models\Armazem;
use App\Models\Corredor;
use App\Models\OrdemReposicao;
use App\Models\Posicao;
use App\Models\Produto;
use App\Models\User;
use App\Services\ReposicaoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('HUB – Reposição de Produtos')]
class ReposicaoIndex extends Component
{
    use WithPagination;

    // --- Tab ativa: 'hub' | 'ordens' ---
    public string $tab = 'hub';

    // --- Filtros da aba HUB ---
    public string $searchProduto = '';

    // --- Modal solicitar reposição ---
    public bool $modalSolicitar = false;
    public ?int $produtoSolicitarId = null;
    public float $quantidadeSolicitar = 1;

    // --- Modal executar reposição ---
    public bool $modalExecutar = false;
    public ?int $ordemId = null;
    public ?int $armazemOrigemId = null;
    public ?int $corredorOrigemId = null;
    public ?int $posicaoOrigemId = null;
    public ?int $executorId = null;

    // --- Modal devolver ao estoque ---
    public bool $modalDevolver = false;
    public ?int $produtoDevolverProdutoId = null;
    public float $quantidadeDevolver = 1;
    public ?int $armazemDestinoId = null;
    public ?int $corredorDestinoId = null;
    public ?int $posicaoDestinoId = null;
    public ?int $executorDevolucaoId = null;

    // ─── Watcher para resetar cascata ────────────────────────────────────────
    public function updatedArmazemOrigemId()
    {
        $this->corredorOrigemId = null;
        $this->posicaoOrigemId = null;
    }

    public function updatedCorredorOrigemId()
    {
        $this->posicaoOrigemId = null;
    }

    public function updatedArmazemDestinoId()
    {
        $this->corredorDestinoId = null;
        $this->posicaoDestinoId = null;
    }

    public function updatedCorredorDestinoId()
    {
        $this->posicaoDestinoId = null;
    }

    // ─── Abrir modal solicitar ────────────────────────────────────────────────
    public function abrirModalSolicitar(?int $produtoId = null): void
    {
        $this->reset(['produtoSolicitarId', 'quantidadeSolicitar']);
        $this->produtoSolicitarId = $produtoId;
        $this->quantidadeSolicitar = 1;
        $this->modalSolicitar = true;
    }

    public function fecharModalSolicitar(): void
    {
        $this->modalSolicitar = false;
    }

    // ─── Solicitar reposição ──────────────────────────────────────────────────
    public function solicitarReposicao(ReposicaoService $service): void
    {
        $this->validate([
            'produtoSolicitarId'  => 'required|exists:produtos,id',
            'quantidadeSolicitar' => 'required|numeric|min:0.01',
        ], [
            'produtoSolicitarId.required'  => 'Selecione o produto.',
            'quantidadeSolicitar.min'      => 'A quantidade deve ser maior que zero.',
        ]);

        try {
            $service->solicitarReposicao($this->produtoSolicitarId, $this->quantidadeSolicitar);
            $this->modalSolicitar = false;
            $this->tab = 'ordens';
            session()->flash('success', 'Solicitação de reposição criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('ReposicaoIndex: falha ao solicitar reposição', [
                'error' => $e->getMessage(),
                'user'  => auth()->id(),
            ]);
            $this->addError('solicitar', 'Erro ao criar solicitação: ' . $e->getMessage());
        }
    }

    // ─── Abrir modal executar ─────────────────────────────────────────────────
    public function abrirModalExecutar(int $ordemId): void
    {
        $this->reset(['armazemOrigemId', 'corredorOrigemId', 'posicaoOrigemId', 'executorId']);
        $this->ordemId = $ordemId;
        $this->modalExecutar = true;
    }

    public function fecharModalExecutar(): void
    {
        $this->modalExecutar = false;
    }

    // ─── Confirmar execução da reposição ─────────────────────────────────────
    public function confirmarReposicao(ReposicaoService $service): void
    {
        $this->validate([
            'ordemId'         => 'required|exists:ordens_reposicao,id',
            'armazemOrigemId' => 'required|exists:armazens,id',
            'executorId'      => 'required|exists:users,id',
        ], [
            'armazemOrigemId.required' => 'Informe o armazém de origem.',
            'executorId.required'      => 'Selecione o executor (repositor).',
        ]);

        $ordem = OrdemReposicao::find($this->ordemId);
        if (!$ordem || $ordem->status !== 'pendente') {
            $this->addError('executar', 'Esta ordem não está mais pendente.');
            return;
        }

        try {
            $service->confirmarReposicao(
                $ordem,
                $this->armazemOrigemId,
                $this->corredorOrigemId,
                $this->posicaoOrigemId,
                $this->executorId
            );
            $this->modalExecutar = false;
            session()->flash('success', 'Reposição confirmada e movimentação registrada!');
        } catch (\Exception $e) {
            Log::error('ReposicaoIndex: falha ao confirmar reposição', [
                'ordem_id' => $this->ordemId,
                'error'    => $e->getMessage(),
            ]);
            $this->addError('executar', 'Erro ao confirmar: ' . $e->getMessage());
        }
    }

    // ─── Abrir modal devolver ─────────────────────────────────────────────────
    public function abrirModalDevolver(int $produtoId): void
    {
        $this->reset(['quantidadeDevolver', 'armazemDestinoId', 'corredorDestinoId', 'posicaoDestinoId', 'executorDevolucaoId']);
        $this->produtoDevolverProdutoId = $produtoId;
        $this->quantidadeDevolver = 1;
        $this->modalDevolver = true;
    }

    public function fecharModalDevolver(): void
    {
        $this->modalDevolver = false;
    }

    // ─── Confirmar devolução ──────────────────────────────────────────────────
    public function confirmarDevolucao(ReposicaoService $service): void
    {
        $this->validate([
            'produtoDevolverProdutoId' => 'required|exists:produtos,id',
            'quantidadeDevolver'       => 'required|numeric|min:0.01',
            'armazemDestinoId'         => 'required|exists:armazens,id',
            'executorDevolucaoId'      => 'required|exists:users,id',
        ], [
            'armazemDestinoId.required'     => 'Informe o armazém de destino.',
            'executorDevolucaoId.required'  => 'Selecione o executor da devolução.',
        ]);

        try {
            $service->devolverAoEstoque(
                $this->produtoDevolverProdutoId,
                $this->quantidadeDevolver,
                $this->armazemDestinoId,
                $this->corredorDestinoId,
                $this->posicaoDestinoId,
                $this->executorDevolucaoId
            );
            $this->modalDevolver = false;
            session()->flash('success', 'Devolução ao estoque registrada com sucesso!');
        } catch (\Exception $e) {
            Log::error('ReposicaoIndex: falha ao registrar devolução', [
                'produto_id' => $this->produtoDevolverProdutoId,
                'error'      => $e->getMessage(),
            ]);
            $this->addError('devolver', 'Erro ao devolver: ' . $e->getMessage());
        }
    }

    // ─── Marcar ordem como impressa (indica que PDF foi gerado) ──────────────
    public function marcarComoImpresso(int $ordemId): void
    {
        $ordem = OrdemReposicao::find($ordemId);
        if ($ordem && is_null($ordem->impresso_em)) {
            $ordem->update(['impresso_em' => now()]);
        }
        $this->dispatch('abrirPdf', ['url' => route('reposicao.pdf', ['ordem' => $ordemId])]);
    }

    // ─── Cancelar ordem de reposição ─────────────────────────────────────────
    public function cancelarOrdem(int $ordemId): void
    {
        $ordem = OrdemReposicao::find($ordemId);
        if (!$ordem || $ordem->status !== 'pendente') {
            session()->flash('error', 'Apenas ordens pendentes podem ser canceladas.');
            return;
        }
        $ordem->update(['status' => 'cancelada']);
        Log::info('Ordem de reposição cancelada', ['ordem_id' => $ordemId, 'usuario' => auth()->id()]);
        session()->flash('success', 'Ordem de reposição cancelada.');
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        // Saldo de produtos no HUB (Armazem 1)
        $query = DB::table('movimentacao_produtos as mp')
            ->join('movimentacoes as m', 'm.id', '=', 'mp.movimentacao_id')
            ->join('produtos as p', 'p.id', '=', 'mp.produto_id')
            ->where('mp.armazem_id', ReposicaoService::HUB_ARMAZEM_ID)
            ->where('m.status', 'aprovado')
            ->whereNull('m.deleted_at');

        if (!empty($this->searchProduto)) {
            $term = $this->searchProduto;
            $query->where(function ($sub) use ($term) {
                $sub->where('p.nome', 'like', "%{$term}%")
                    ->orWhere('p.sku', 'like', "%{$term}%");
            });
        }

        $saldoHub = $query->selectRaw("
                mp.produto_id,
                p.nome as produto_nome,
                p.sku as produto_sku,
                SUM(CASE WHEN m.tipo IN ('entrada_hub','entrada') THEN mp.quantidade
                         WHEN m.tipo IN ('saida_para_hub','saida','devolucao_hub') THEN -mp.quantidade
                         ELSE 0 END) as saldo
            ")
            ->groupBy('mp.produto_id', 'p.nome', 'p.sku')
            ->having('saldo', '>', 0)
            ->orderBy('p.nome')
            ->paginate(15, ['*'], 'saldoPage');

        // Ordens de reposição
        $ordens = OrdemReposicao::with(['produto', 'solicitadoPor', 'executor', 'armazemOrigem'])
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'ordensPage');

        // Dados para selects
        $armazens     = Armazem::orderBy('nome')->get();
        $corredores   = $this->armazemOrigemId
            ? Corredor::where('armazem_id', $this->armazemOrigemId)->orderBy('nome')->get()
            : collect();
        $posicoes     = $this->corredorOrigemId
            ? Posicao::where('corredor_id', $this->corredorOrigemId)->orderBy('nome')->get()
            : collect();

        $corredoresDestino = $this->armazemDestinoId
            ? Corredor::where('armazem_id', $this->armazemDestinoId)->orderBy('nome')->get()
            : collect();
        $posicoesDestino   = $this->corredorDestinoId
            ? Posicao::where('corredor_id', $this->corredorDestinoId)->orderBy('nome')->get()
            : collect();

        $usuarios     = User::orderBy('name')->get(['id', 'name']);
        $produtos     = Produto::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'sku']);

        return view('livewire.estoque.reposicao-index', compact(
            'saldoHub', 'ordens', 'armazens', 'corredores', 'posicoes',
            'corredoresDestino', 'posicoesDestino', 'usuarios', 'produtos'
        ));
    }
}
