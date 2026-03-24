@extends('layouts.app')
@section('title', 'Relatório de Faltas')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Relatório — Faltas sem Pedido</h4>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨️ Imprimir</button>
    </div>

    <form method="GET" class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <select name="produto_id" class="form-select form-select-sm select2">
                        <option value="">Todos os produtos</option>
                        @foreach($produtos as $p)
                            <option value="{{ $p->id }}" @selected(request('produto_id') == $p->id)>{{ $p->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="vendedor_id" class="form-select form-select-sm">
                        <option value="">Todos vendedores</option>
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}" @selected(request('vendedor_id') == $v->id)>{{ $v->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="cliente" class="form-control form-control-sm"
                           placeholder="Cliente" value="{{ request('cliente') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Data</th>
                        <th>Nº Falta</th>
                        <th>Nome do Cliente</th>
                        <th>Cód. Produto</th>
                        <th>Descrição Produto</th>
                        <th class="text-center">Qtd</th>
                        <th class="text-end">Valor (Qtd × Preço)</th>
                        <th>Vendedor</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalGeral = 0; @endphp
                    @forelse($faltas as $falta)
                        @foreach($falta->itens as $item)
                        @php $totalGeral += $item->valor_total; @endphp
                        <tr>
                            <td>{{ $falta->created_at->format('d/m/Y') }}</td>
                            <td>{{ $falta->numero_falta }}</td>
                            <td>{{ $falta->cliente?->nome ?? $falta->nome_cliente ?? '-' }}</td>
                            <td>{{ $item->produto?->sku ?? $item->produto?->id ?? '-' }}</td>
                            <td>{{ $item->descricao_produto }}</td>
                            <td class="text-center">{{ number_format($item->quantidade, 3, ',', '.') }}</td>
                            <td class="text-end">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                            <td>{{ $falta->vendedor?->user->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">Nenhum registro encontrado.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <td colspan="6" class="text-end">Total geral:</td>
                        <td class="text-end">R$ {{ number_format($totalGeral, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
