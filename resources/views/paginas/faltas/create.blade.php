@extends('layouts.app')
@section('title', 'Registrar Falta')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Registrar Falta</h4>
        <a href="{{ route('faltas.index') }}" class="btn btn-secondary btn-sm">← Voltar</a>
    </div>

    <form action="{{ route('faltas.store') }}" method="POST" id="form-falta">
        @csrf

        {{-- Cabeçalho --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Cliente <small class="text-muted">(opcional)</small></label>
                        <select name="cliente_id" class="form-select select2" id="select-cliente">
                            <option value="">-- Selecione --</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}" data-nome="{{ $c->nome }}">{{ $c->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nome do cliente (livre)</label>
                        <input type="text" name="nome_cliente" class="form-control"
                               placeholder="Ou digite diretamente" value="{{ old('nome_cliente') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Vendedor</label>
                        <select name="vendedor_id" class="form-select">
                            <option value="">-- Selecione --</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" @selected(old('vendedor_id') == $v->id)>
                                    {{ $v->user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Observação</label>
                        <textarea name="observacao" class="form-control" rows="2">{{ old('observacao') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Itens --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Itens em falta</span>
                <button type="button" class="btn btn-sm btn-primary" id="btn-add-item">+ Adicionar item</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0" id="tabela-itens">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40%">Produto (código ou nome)</th>
                            <th style="width:20%">Descrição</th>
                            <th style="width:12%">Qtd</th>
                            <th style="width:15%">Valor unit.</th>
                            <th style="width:10%">Total</th>
                            <th style="width:3%"></th>
                        </tr>
                    </thead>
                    <tbody id="itens-body">
                        {{-- linhas adicionadas via JS --}}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Total geral:</td>
                            <td colspan="2" class="fw-bold" id="total-geral">R$ 0,00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Salvar Falta</button>
            <a href="{{ route('faltas.index') }}" class="btn btn-light">Cancelar</a>
        </div>
    </form>
</div>

<template id="linha-template">
    <tr>
        <td>
            <input type="hidden" name="itens[__IDX__][produto_id]" class="campo-produto-id">
            <input type="text" class="form-control form-control-sm campo-busca-produto"
                   placeholder="Código ou nome..." autocomplete="off">
        </td>
        <td>
            <input type="text" name="itens[__IDX__][descricao_produto]" class="form-control form-control-sm campo-descricao">
        </td>
        <td>
            <input type="number" name="itens[__IDX__][quantidade]" class="form-control form-control-sm campo-qtd"
                   step="0.001" min="0.001" value="1">
        </td>
        <td>
            <input type="number" name="itens[__IDX__][valor_unitario]" class="form-control form-control-sm campo-valor"
                   step="0.01" min="0" value="0">
        </td>
        <td class="align-middle campo-total">R$ 0,00</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item">×</button>
        </td>
    </tr>
</template>

@endsection

@push('scripts')
<script>
let itemIndex = 0;

function adicionarLinha() {
    const template = document.getElementById('linha-template').innerHTML;
    const linha = template.replaceAll('__IDX__', itemIndex++);
    const tbody = document.getElementById('itens-body');
    tbody.insertAdjacentHTML('beforeend', linha);
    const novaLinha = tbody.lastElementChild;
    inicializarLinha(novaLinha);
}

function inicializarLinha(tr) {
    const campoBusca = tr.querySelector('.campo-busca-produto');
    const campoProdutoId = tr.querySelector('.campo-produto-id');
    const campoDescricao = tr.querySelector('.campo-descricao');
    const campoQtd = tr.querySelector('.campo-qtd');
    const campoValor = tr.querySelector('.campo-valor');
    const campoTotal = tr.querySelector('.campo-total');

    let timeout;
    let sugestoes;

    campoBusca.addEventListener('input', function() {
        clearTimeout(timeout);
        if (this.value.length < 2) return;
        timeout = setTimeout(() => {
            fetch(`{{ route('faltas.buscar_produto') }}?q=${encodeURIComponent(this.value)}`)
                .then(r => r.json())
                .then(produtos => {
                    sugestoes?.remove();
                    if (!produtos.length) return;
                    sugestoes = document.createElement('ul');
                    sugestoes.className = 'list-group position-absolute z-index-dropdown';
                    sugestoes.style.cssText = 'max-height:200px;overflow-y:auto;width:100%;top:100%;left:0;z-index:9999';
                    produtos.forEach(p => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item list-group-item-action py-1 px-2';
                        li.style.cursor = 'pointer';
                        li.textContent = `[${p.sku ?? p.id}] ${p.nome}`;
                        li.addEventListener('click', () => {
                            campoProdutoId.value = p.id;
                            campoBusca.value = `[${p.sku ?? p.id}] ${p.nome}`;
                            campoDescricao.value = p.nome;
                            campoValor.value = p.preco_venda ?? 0;
                            sugestoes.remove();
                            calcularLinha(campoQtd, campoValor, campoTotal);
                        });
                        sugestoes.appendChild(li);
                    });
                    campoBusca.parentElement.style.position = 'relative';
                    campoBusca.parentElement.appendChild(sugestoes);
                });
        }, 300);
    });

    [campoQtd, campoValor].forEach(c => c.addEventListener('input', () => calcularLinha(campoQtd, campoValor, campoTotal)));
    tr.querySelector('.btn-remove-item').addEventListener('click', () => { tr.remove(); calcularTotal(); });
}

function calcularLinha(campoQtd, campoValor, campoTotal) {
    const total = (parseFloat(campoQtd.value) || 0) * (parseFloat(campoValor.value) || 0);
    campoTotal.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    document.querySelectorAll('.campo-total').forEach(el => {
        const val = el.textContent.replace(/[^\d,]/g, '').replace(',', '.');
        total += parseFloat(val) || 0;
    });
    document.getElementById('total-geral').textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

document.getElementById('btn-add-item').addEventListener('click', adicionarLinha);
document.getElementById('form-falta').addEventListener('submit', function(e) {
    if (document.getElementById('itens-body').children.length === 0) {
        e.preventDefault();
        alert('Adicione ao menos um item antes de salvar.');
    }
});

// Adiciona primeira linha automaticamente
adicionarLinha();
</script>
@endpush
