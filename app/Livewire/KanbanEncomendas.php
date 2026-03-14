<?php

namespace App\Livewire;

use App\Models\ConsultaPreco;
use App\Models\ConsultaPrecoGrupo;
use App\Models\Fornecedor;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class KanbanEncomendas extends Component
{
    use WithPagination;

    // ── Aba ativa: 'kanban' ou 'lista' ──────────────────────
    public string $aba = 'kanban';

    // ── Filtros kanban ───────────────────────────────────────
    public string $search        = '';
    public string $clienteFilter = '';
    public string $vendedorFilter = '';

    // ── Filtros lista (compartilhados compras + vendas) ──────
    public string $perfilFiltro    = 'compras'; // 'compras' | 'vendas'
    public string $descricaoFiltro = '';
    public string $vendedorLista   = '';
    public string $fornecedorFiltro = '';
    public string $corFiltro       = '';
    public string $dataInicio      = '';
    public string $dataFim         = '';

    protected $queryString = ['aba', 'perfilFiltro'];

    public function updatedAba(): void
    {
        $this->resetPage();
    }

    public function limparFiltros(): void
    {
        $this->search         = '';
        $this->clienteFilter  = '';
        $this->vendedorFilter = '';
    }

    public function limparFiltrosLista(): void
    {
        $this->descricaoFiltro  = '';
        $this->vendedorLista    = '';
        $this->fornecedorFiltro = '';
        $this->corFiltro        = '';
        $this->dataInicio       = '';
        $this->dataFim          = '';
        $this->resetPage();
    }

    // ── Colunas do kanban ────────────────────────────────────
    private function colunas(): array
    {
        return [
            ['id' => 'solicitada',         'title' => 'Encomenda Solicitada',   'description' => 'Pedidos recebidos aguardando cotação',       'color' => 'zinc',   'workflow_status' => 'solicitada'],
            ['id' => 'cotando',            'title' => 'Sendo Cotado',            'description' => 'Compras preenchendo preços',                 'color' => 'blue',   'workflow_status' => 'cotando'],
            ['id' => 'aguardando_cliente', 'title' => 'Ag. Aprovação do Cliente','description' => 'Orçamento enviado, aguardando pagamento',    'color' => 'orange', 'workflow_status' => 'aguardando_cliente'],
            ['id' => 'aprovado',           'title' => 'Aprovado',                'description' => 'Cotação aprovada pelo vendedor',            'color' => 'yellow', 'workflow_status' => 'aprovado'],
            ['id' => 'aguardando',         'title' => 'Aguardando Recebimento',  'description' => 'Comprado, aguardando chegada',               'color' => 'purple', 'workflow_status' => 'aguardando'],
            ['id' => 'recebido',           'title' => 'Recebido',                'description' => 'Material recebido no estoque',              'color' => 'green',  'workflow_status' => 'recebido'],
            ['id' => 'entregue',           'title' => 'Entregue ao Cliente',     'description' => 'Entregue ao vendedor/cliente',              'color' => 'emerald','workflow_status' => 'entregue'],   ];
    }

    private function resolverColuna(ConsultaPrecoGrupo $grupo): string
    {
        $status = $grupo->status ?? '';

        if ($grupo->entradas->contains(fn($e) => $e->status === 'Entregue')) return 'entregue';
        if ($grupo->entradas->contains(fn($e) => in_array($e->status, ['Recebido completo', 'Recebido parcialmente']))) return 'recebido';
        if ($status === 'Aprovado' && $grupo->entradas->isEmpty()) return 'aguardando';
        if ($status === 'Aprovado') return 'aprovado';

        // Orçamento gerado mas cliente ainda não aprovou/pagou
        if ($grupo->orcamento_id && $grupo->orcamento?->status === 'Pendente') return 'aguardando_cliente';

        if (in_array($status, ['Aguardando preços', 'Preços preenchidos', 'Em revisão'])) return 'cotando';
        return 'solicitada';
    }

    // ── Query da lista de itens ──────────────────────────────
    private function queryItens()
    {
        $query = ConsultaPreco::with([
            'grupo.cliente',
            'grupo.usuario',
            'fornecedorSelecionado.fornecedor',
            'cor',
        ])->whereHas('grupo'); // só itens que têm grupo

        // Descrição
        if ($this->descricaoFiltro) {
            $query->where('descricao', 'like', '%' . $this->descricaoFiltro . '%');
        }

        // Fornecedor (pelo fornecedor selecionado na cotação)
        if ($this->fornecedorFiltro) {
            $query->whereHas('fornecedorSelecionado.fornecedor', fn($q) =>
                $q->where('nome_fantasia', 'like', '%' . $this->fornecedorFiltro . '%')
                  ->orWhere('nome', 'like', '%' . $this->fornecedorFiltro . '%')
            );
        }

        // Vendedor (usuário do grupo)
        if ($this->vendedorLista) {
            $query->whereHas('grupo.usuario', fn($q) =>
                $q->where('name', 'like', '%' . $this->vendedorLista . '%')
            );
        }

        // Cor
        if ($this->corFiltro) {
            $query->whereHas('cor', fn($q) =>
                $q->where('nome', 'like', '%' . $this->corFiltro . '%')
            );
        }

        // Range de data (data de criação do grupo = data da encomenda)
        if ($this->dataInicio) {
            $query->whereHas('grupo', fn($q) =>
                $q->whereDate('created_at', '>=', $this->dataInicio)
            );
        }
        if ($this->dataFim) {
            $query->whereHas('grupo', fn($q) =>
                $q->whereDate('created_at', '<=', $this->dataFim)
            );
        }

        // Perfil: compras vê todos os itens aprovados/em andamento
        // Vendas vê todos os itens dos seus grupos
        if ($this->perfilFiltro === 'compras') {
            $query->whereHas('grupo', fn($q) =>
                $q->whereIn('status', ['Aprovado', 'Aguardando preços', 'Preços preenchidos', 'Em revisão'])
            );
        }
        // vendas: sem filtro de status adicional

        return $query->orderByDesc(fn($q) => $q->select('created_at')->from('consulta_preco_grupos')
            ->whereColumn('consulta_preco_grupos.id', 'consulta_precos.grupo_id')
            ->limit(1)
        );
    }

    public function render()
    {
        // ── Dados do kanban ──────────────────────────────────
        $columns = collect();
        if ($this->aba === 'kanban') {
            $query = ConsultaPrecoGrupo::with(['cliente', 'usuario', 'itens', 'entradas.itens','orcamento'])->latest();

            if ($this->search) {
                $query->where(fn($q) =>
                    $q->where('id', 'like', '%' . $this->search . '%')
                      ->orWhereHas('cliente', fn($c) =>
                          $c->where('nome_fantasia', 'like', '%' . $this->search . '%')
                            ->orWhere('nome', 'like', '%' . $this->search . '%')
                      )
                );
            }
            if ($this->clienteFilter) {
                $query->whereHas('cliente', fn($c) =>
                    $c->where('nome_fantasia', 'like', '%' . $this->clienteFilter . '%')
                      ->orWhere('nome', 'like', '%' . $this->clienteFilter . '%')
                );
            }
            if ($this->vendedorFilter) {
                $query->whereHas('usuario', fn($u) =>
                    $u->where('name', 'like', '%' . $this->vendedorFilter . '%')
                );
            }

            $grupos   = $query->get();
            $agrupado = $grupos->groupBy(fn($g) => $this->resolverColuna($g));

            $columns = collect($this->colunas())->map(function ($col) use ($agrupado) {
                $itens      = $agrupado->get($col['id'], collect());
                $valorTotal = $itens->sum(fn($g) =>
                    $g->itens->sum(fn($i) => ($i->preco_venda ?? 0) * ($i->quantidade ?? 1))
                );
                return array_merge($col, [
                    'grupos'      => $itens,
                    'count'       => $itens->count(),
                    'valor_total' => $valorTotal,
                ]);
            });
        }

        // ── Dados da lista ───────────────────────────────────
        $itensLista = null;
        if ($this->aba === 'lista') {
            $itensLista = $this->queryItens()->paginate(30);
        }

        return view('livewire.kanban-encomendas', compact('columns', 'itensLista'));
    }
}