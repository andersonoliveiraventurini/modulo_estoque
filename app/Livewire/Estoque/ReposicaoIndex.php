<?php

namespace App\Livewire\Estoque;

use App\Models\Armazem;
use App\Models\Corredor;
use App\Models\HubStock;
use App\Models\OrdemReposicao;
use App\Models\Posicao;
use App\Models\Produto;
use App\Models\User;
use App\Services\ReposicaoService;
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
            'quantidadeSolicitar' => 'required|integer|min:1',
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
            'armazemOrigemId' => 'nullable|exists:armazens,id',
            'executorId'      => 'required|exists:users,id',
        ], [
            'executorId.required'      => 'Selecione o executor (repositor).',
        ]);

        // Validação condicional: se armazemOrigemId for null, assumimos Entrada Direta.
        // Se no futuro houver mais tipos, aqui deve ser expandido.
        if (is_null($this->armazemOrigemId)) {
            Log::info('ReposicaoIndex: Confirmando como Entrada Direta', ['ordem_id' => $this->ordemId]);
        }

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
            'quantidadeDevolver'       => 'required|integer|min:1',
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
    public function marcarComoImpresso(int $ordemId)
    {
        $ordem = OrdemReposicao::find($ordemId);
        if ($ordem && is_null($ordem->impresso_em)) {
            $ordem->update(['impresso_em' => now()]);
        }
        
        return redirect()->route('reposicao.pdf', ['ordem' => $ordemId]);
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
        // Lê saldo direto da tabela hub_stocks (mantida pelo ReposicaoService)
        $saldoHubQuery = HubStock::with('produto')
            ->where('quantidade', '>', 0);

        if (!empty($this->searchProduto)) {
            $term = $this->searchProduto;
            $saldoHubQuery->whereHas('produto', function ($q) use ($term) {
                $q->where('nome', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        $saldoHub = $saldoHubQuery
            ->join('produtos', 'produtos.id', '=', 'hub_stocks.produto_id')
            ->orderBy('produtos.nome')
            ->select('hub_stocks.*')
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
