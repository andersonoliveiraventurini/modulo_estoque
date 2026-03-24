@extends('layouts.app')
@section('title', 'Faltas')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Faltas sem Pedido</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('faltas.relatorio') }}" class="btn btn-outline-secondary btn-sm">📊 Relatório</a>
            <a href="{{ route('faltas.create') }}" class="btn btn-primary btn-sm">+ Nova Falta</a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="cliente" class="form-control form-control-sm"
                           placeholder="Cliente" value="{{ request('cliente') }}">
                </div>
                <div class="col-md-3">
                    <select name="vendedor_id" class="form-select form-select-sm">
                        <option value="">Todos vendedores</option>
                        @foreach($vendedores as $v)
                            <option value="{{ $v->id }}" @selected(request('vendedor_id') == $v->id)>
                                {{ $v->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Itens</th>
                        <th class="text-end">Valor Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faltas as $falta)
                    <tr>
                        <td><span class="badge bg-warning text-dark">{{ $falta->numero_falta }}</span></td>
                        <td>{{ $falta->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $falta->cliente?->nome ?? $falta->nome_cliente ?? '-' }}</td>
                        <td>{{ $falta->vendedor?->user->name ?? '-' }}</td>
                        <td>{{ $falta->itens->count() }}</td>
                        <td class="text-end">{{ number_format($falta->valor_total, 2, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('faltas.show', $falta) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma falta registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($faltas->hasPages())
        <div class="card-footer">{{ $faltas->links() }}</div>
        @endif
    </div>
</div>
@endsection
