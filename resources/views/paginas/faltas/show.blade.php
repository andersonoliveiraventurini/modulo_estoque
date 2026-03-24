@extends('layouts.app')
@section('title', 'Falta ' . $falta->numero_falta)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Falta {{ $falta->numero_falta }}</h4>
        <a href="{{ route('faltas.index') }}" class="btn btn-secondary btn-sm">← Voltar</a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <p><strong>Data:</strong> {{ $falta->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Cliente:</strong> {{ $falta->cliente?->nome ?? $falta->nome_cliente ?? '-' }}</p>
                    <p><strong>Vendedor:</strong> {{ $falta->vendedor?->user->name ?? '-' }}</p>
                    <p><strong>Registrado por:</strong> {{ $falta->user->name }}</p>
                    @if($falta->observacao)
                        <p><strong>Observação:</strong> {{ $falta->observacao }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th>Descrição</th>
                        <th>Qtd</th>
                        <th class="text-end">Valor Unit.</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($falta->itens as $item)
                    <tr>
                        <td>{{ $item->produto?->sku ?? '-' }}</td>
                        <td>{{ $item->descricao_produto }}</td>
                        <td>{{ number_format($item->quantidade, 3, ',', '.') }}</td>
                        <td class="text-end">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td class="text-end">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Total:</td>
                        <td class="text-end">R$ {{ number_format($falta->valor_total, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
