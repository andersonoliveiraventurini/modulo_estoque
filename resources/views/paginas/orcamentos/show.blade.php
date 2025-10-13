<x-layouts.app :title="__('Gerenciar Orçamento #' . $orcamento->id)">
    <div class="flex flex-col gap-6">

        {{-- Cabeçalho --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Gerenciar Orçamento #{{ $orcamento->id }} - @if ($orcamento->pdf_path)
                    <a href="{{ asset('storage/' . $orcamento->pdf_path) }}" target="_blank">
                        <x-button size="sm" variant="primary">
                            <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                            PDF
                        </x-button>
                    </a>
                @endif
            </h2>

            <div class="flex flex-wrap justify-between items-center">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? '---' }}</p>
                        <p><strong>Telefone:</strong> {{ $orcamento->cliente->telefone ?? '---' }}</p>
                        <p><strong>Endereço:</strong> {{ $orcamento->cliente->endereco ?? '---' }}</p>
                        <p><strong>Obra:</strong> {{ $orcamento->obra ?? '---' }}</p>
                    </div>
                    <div>
                        <p><strong>Data do Orçamento:</strong> {{ $orcamento->created_at->format('d/m/Y') }}</p>
                        <p><strong>Validade:</strong> {{ \Carbon\Carbon::parse($orcamento->validade)->format('d/m/Y') }}
                        </p>
                        <p><strong>Prazo de Entrega:</strong> {{ $orcamento->prazo_entrega ?? '---' }}</p>
                        <p><strong>Vendedor:</strong> {{ $orcamento->vendedor->name ?? '---' }}</p>
                    </div>
                </div>

                {{-- Status --}}
                <div class="text-right">
                    @if ($orcamento->status === 'Aprovar desconto')
                        <span
                            class="inline-block bg-yellow-200 text-yellow-800 text-sm px-3 py-1 rounded-full font-medium mb-2">Aguardando
                            aprovação de desconto</span>
                    @else
                        <p><strong>Status:</strong></p>
                        <form id="form-status-{{ $orcamento->id }}" class="inline-flex gap-2"
                            data-id="{{ $orcamento->id }}"
                            data-url="{{ route('orcamentos.atualizar-status', $orcamento->id) }}">
                            @csrf
                            @method('PUT')
                            <select name="status"
                                class="border border-gray-300 rounded px-2 py-1 text-sm status-select"
                                data-id="{{ $orcamento->id }}">
                                @foreach (['Pendente', 'Aprovado', 'Cancelado', 'Rejeitado', 'Expirado'] as $s)
                                    <option value="{{ $s }}" @selected($orcamento->status === $s)>
                                        {{ $s }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button"
                                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 atualizar-status"
                                data-id="{{ $orcamento->id }}">
                                Atualizar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Itens do Orçamento --}}
    @if ($orcamento->itens->count() > 0)
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7h9M15.5 20V10" />
                </svg>
                Itens do Orçamento
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 border">Código</th>
                            <th class="px-3 py-2 border">Produto</th>
                            <th class="px-3 py-2 border">Part Number</th>
                            <th class="px-3 py-2 border">Fornecedor</th>
                            <th class="px-3 py-2 border">Cor</th>
                            <th class="px-3 py-2 border">Qtd</th>
                            <th class="px-3 py-2 border">Preço Unit.</th>
                            <th class="px-3 py-2 border">Preço Unit. c/ Desc.</th>
                            <th class="px-3 py-2 border">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($orcamento->itens as $item)
                            <tr>
                                <td class="px-3 py-2 border">{{ $item->produto->codigo ?? $item->produto_id }}</td>
                                <td class="px-3 py-2 border">{{ $item->produto->nome ?? '—' }}</td>
                                <td class="px-3 py-2 border">{{ $item->produto->part_number ?? '—' }}</td>
                                <td class="px-3 py-2 border">{{ $item->produto->fornecedor->nome ?? '—' }}</td>
                                <td class="px-3 py-2 border">{{ $item->produto->cor ?? '—' }}</td>
                                <td class="px-3 py-2 border text-center">{{ $item->quantidade }}</td>
                                <td class="px-3 py-2 border">R$
                                    {{ number_format($item->valor_unitario, 2, ',', '.') }}
                                <td class="px-3 py-2 border text-green-600 font-medium">R$
                                    {{ number_format($item->valor_unitario_com_desconto, 2, ',', '.') }}</td>
                                </td>
                                <td class="px-3 py-2 border">R$
                                    {{ number_format($item->valor_com_desconto, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Vidros / Esteiras --}}
    @if ($orcamento->vidros->count() > 0)
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                Vidros e Esteiras
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 border text-center">Qtd</th>
                            <th class="px-3 py-2 border">Descrição</th>
                            <th class="px-3 py-2 border text-center">Altura (mm)</th>
                            <th class="px-3 py-2 border text-center">Largura (mm)</th>
                            <th class="px-3 py-2 border text-right">Preço m²</th>
                            <th class="px-3 py-2 border text-right">Desc.</th>
                            <th class="px-3 py-2 border text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orcamento->vidros as $vidro)
                            <tr>
                                <td class="px-3 py-2 border text-center">{{ $vidro->quantidade }}</td>
                                <td class="px-3 py-2 border">{{ $vidro->descricao }}</td>
                                <td class="px-3 py-2 border text-center">{{ $vidro->altura }}</td>
                                <td class="px-3 py-2 border text-center">{{ $vidro->largura }}</td>
                                <td class="px-3 py-2 border text-right">R$
                                    {{ number_format($vidro->preco_metro_quadrado, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 border text-right">R$
                                    {{ number_format($vidro->preco_metro_quadrado * (($orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0) / 100), 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 border text-right font-semibold text-green-600">R$
                                    {{ number_format($vidro->valor_com_desconto, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Totais e Descontos --}}
    @php
        $totalItens = $orcamento->itens->sum('valor_com_desconto');
        $totalVidros = $orcamento->vidros->sum('valor_com_desconto');
        $totalFixos = $orcamento->descontos->where('tipo', 'fixo')->sum('valor');
        $percentual = $orcamento->descontos->where('tipo', 'percentual')->max('porcentagem') ?? 0;
        $valorFinal =
            $totalItens + $totalVidros - $totalFixos + ($orcamento->frete ?? 0) + ($orcamento->guia_recolhimento ?? 0);
    @endphp

    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h3 class="text-lg font-semibold mb-4">Totais e Descontos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p><strong>Desconto Percentual:</strong> {{ number_format($percentual, 2, ',', '.') }}%</p>
                @foreach ($orcamento->descontos->where('tipo', 'fixo') as $desc)
                    <p><strong>{{ $desc->motivo }}:</strong> -R$ {{ number_format($desc->valor, 2, ',', '.') }}
                    </p>
                @endforeach
                @if ($orcamento->guia_recolhimento > 0)
                    <p><strong>Guia de Recolhimento:</strong> R$
                        {{ number_format($orcamento->guia_recolhimento, 2, ',', '.') }}</p>
                @endif
                @if ($orcamento->frete > 0)
                    <p><strong>Frete:</strong> R$ {{ number_format($orcamento->frete, 2, ',', '.') }}</p>
                @endif
            </div>
            <div>
                <p><strong>Total Produtos:</strong> R$ {{ number_format($totalItens, 2, ',', '.') }}</p>
                <p><strong>Total Vidros:</strong> R$ {{ number_format($totalVidros, 2, ',', '.') }}</p>
                <p class="text-lg font-semibold text-green-600 mt-2">
                    Valor Final: R$ {{ number_format($valorFinal, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>


    {{-- Observações --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-neutral-200 dark:border-neutral-700 p-6 shadow">
        <h4 class="font-medium mb-2">Observações</h4>
        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
            {{ $orcamento->observacoes ?? 'Nenhuma observação registrada.' }}</p>
    </div>

    {{-- Ações --}}
    <div class="flex justify-between">
        <a href="{{ route('orcamentos.index') }}"
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Voltar</a>
        <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST"
            onsubmit="return confirm('Tem certeza que deseja excluir este orçamento?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Excluir
                Orçamento</button>
        </form>
    </div>

    </div>

    {{-- SweetAlert2 e AJAX para atualizar status --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.atualizar-status').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const form = document.querySelector(`#form-status-${id}`);
                    const select = form.querySelector('.status-select');
                    const novoStatus = select.value;
                    const url = form.dataset.url;

                    Swal.fire({
                        title: 'Confirmação',
                        text: `Deseja realmente alterar o status para "${novoStatus}"?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, atualizar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': form.querySelector(
                                        'input[name="_token"]').value,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    _method: 'PUT',
                                    status: novoStatus
                                })
                            })
                            .then(response => {
                                if (!response.ok) throw new Error(
                                    'Erro ao atualizar status');
                                return response.json().catch(() => ({}));
                            })
                            .then(data => {
                                Swal.fire({
                                    title: 'Sucesso!',
                                    text: data.message ||
                                        'Status atualizado com sucesso!',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            })
                            .catch(err => {
                                Swal.fire({
                                    title: 'Erro!',
                                    text: 'Não foi possível atualizar o status.',
                                    icon: 'error'
                                });
                            });
                    });
                });
            });
        });
    </script>
</x-layouts.app>
