<x-layouts.app :title="__('Realizar pagamento')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-primary-600" />
                    Pagamento
                </h2>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Pagamento no Balcão - Orçamento #{{ $orcamento->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$toggle('showModal')"></button>
                </div>

                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Informações do Orçamento -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>Informações do Orçamento</strong>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Cliente:</strong> {{ $orcamento->cliente->nome ?? 'N/A' }}</p>
                                    <p><strong>Vendedor:</strong> {{ $orcamento->vendedor->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Condição Original:</strong>
                                        {{ $orcamento->condicaoPagamento->nome ?? 'N/A' }}</p>
                                    <p><strong>Valor Total:</strong> R$ {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formas de Pagamento -->
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <strong>Formas de Pagamento</strong>
                            <button type="button" class="btn btn-sm btn-success" wire:click="adicionarFormaPagamento">
                                <i class="fas fa-plus"></i> Adicionar Forma
                            </button>
                        </div>
                        <div class="card-body">
                            @forelse($condicoesPagamento as $index => $forma)
                                <div class="row mb-3 align-items-end" wire:key="forma-{{ $index }}">
                                    <div class="col-md-5">
                                        <label class="form-label">Forma de Pagamento</label>
                                        <select wire:model.live="condicoesPagamento.{{ $index }}.condicao_id"
                                            class="form-select">
                                            <option value="">Selecione...</option>
                                            @foreach ($condicoesPagamento as $condicao)
                                                <option value="{{ $condicao->id }}">{{ $condicao->nome }}</option>
                                            @endforeach
                                        </select>
                                        @error('condicoesPagamento.' . $index . '.condicao_id')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Valor</label>
                                        <input type="number"
                                            wire:model.live="condicoesPagamento.{{ $index }}.valor"
                                            class="form-control" step="0.01" min="0" placeholder="0,00">
                                        @error('condicoesPagamento.' . $index . '.valor')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        @if (count($condicoesPagamento) > 1)
                                            <button type="button" class="btn btn-danger w-100"
                                                wire:click="removerFormaPagamento({{ $index }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">Nenhuma forma de pagamento adicionada.</p>
                            @endforelse

                            <!-- Alertas sobre desconto -->
                            {{-- @if ($verificarRemocaoDesconto()) --}}
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Atenção!</strong> A condição original era PIX/Dinheiro com desconto.
                                Ao usar cartão, você pode remover o desconto original.
                                <button type="button" class="btn btn-sm btn-warning ms-2"
                                    wire:click="removerDescontoOriginal">
                                    Remover Desconto Original
                                </button>
                            </div>
                            {{--  @endif --}}
                        </div>
                    </div>

                    <!-- Desconto no Balcão -->
                    {{--@if ($podeAplicarDesconto)--}}
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <strong>Desconto no Balcão (até 3%)</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Valor do Desconto</label>
                                        <input type="number" wire:model.live="descontoBalcao" class="form-control"
                                            step="0.01" min="0" max="{{ $orcamento->valor_total_itens * 0.03 }}"
                                            placeholder="0,00">
                                        <small class="text-muted">Máximo: R$
                                            {{ number_format($orcamento->valor_total_itens * 0.03, 2, ',', '.') }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Percentual</label>
                                        <input type="text" class="form-control"
                                            value="{{ $orcamento->valor_total_itens > 0 ? number_format(($descontoBalcao / $orcamento->valor_total_itens) * 100, 2, ',', '.') : 0 }}%"
                                            readonly>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Desconto disponível apenas para pagamentos em PIX ou Dinheiro, sem desconto prévio.
                                </div>
                            </div>
                        </div>
                    {{--@endif--}}

                    <!-- Nota Fiscal -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>Documento Fiscal</strong>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input type="checkbox" wire:model.live="precisaNotaFiscal" class="form-check-input"
                                    id="precisaNotaFiscal-{{ $orcamentoId }}">
                                <label class="form-check-label" for="precisaNotaFiscal-{{ $orcamentoId }}">
                                    Precisa de Nota Fiscal?
                                </label>
                            </div>

                            @if ($precisaNotaFiscal)
                                <div class="alert alert-warning mb-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Os dados da nota fiscal devem ser os mesmos do pagamento.
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" wire:model.live="notaOutroCnpjCpf" class="form-check-input"
                                        id="notaOutroCnpjCpf-{{ $orcamentoId }}">
                                    <label class="form-check-label" for="notaOutroCnpjCpf-{{ $orcamentoId }}">
                                        A nota sai para outro CNPJ ou CPF?
                                    </label>
                                </div>

                                @if ($notaOutroCnpjCpf)
                                    <div class="mb-3">
                                        <label class="form-label">CNPJ/CPF</label>
                                        <input type="text" wire:model="cnpjCpfNota" class="form-control"
                                            placeholder="Digite o CNPJ ou CPF">
                                        @error('cnpjCpfNota')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle"></i>
                                    Será gerado um cupom fiscal.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Resumo do Pagamento -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <strong>Resumo do Pagamento</strong>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td><strong>Valor Total dos Itens:</strong></td>
                                    <td class="text-end">R$ {{ number_format($orcamento->valor_total_itens, 2, ',', '.') }}</td>
                                </tr>
                                @if ($descontoAplicado > 0)
                                    <tr>
                                        <td><strong>Desconto Original:</strong></td>
                                        <td class="text-end text-danger">- R$
                                            {{ number_format($descontoAplicado, 2, ',', '.') }}</td>
                                    </tr>
                                @endif
                                @if ($descontoBalcao > 0)
                                    <tr>
                                        <td><strong>Desconto no Balcão:</strong></td>
                                        <td class="text-end text-danger">- R$
                                            {{ number_format($descontoBalcao, 2, ',', '.') }}</td>
                                    </tr>
                                @endif
                                <tr class="border-top">
                                    <td><strong>Valor a Pagar:</strong></td>
                                    <td class="text-end">
                                        <h5 class="mb-0">R$ {{ number_format($valorComDesconto, 2, ',', '.') }}</h5>
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Valor Pago:</strong></td>
                                    <td class="text-end">
                                        <h5 class="mb-0 text-primary">R$ {{ number_format($valorPago, 2, ',', '.') }}
                                        </h5>
                                    </td>
                                </tr>
                                @if ($troco > 0)
                                    <tr>
                                        <td><strong>Troco:</strong></td>
                                        <td class="text-end">
                                            <h5 class="mb-0 text-success">R$ {{ number_format($troco, 2, ',', '.') }}
                                            </h5>
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            @if ($valorPago < $valorComDesconto)
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Falta pagar: R$ {{ number_format($valorComDesconto - $valorPago, 2, ',', '.') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$toggle('showModal')">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" wire:click="finalizarPagamento"
                        wire:loading.attr="disabled" @if ($valorPago < $valorComDesconto) disabled @endif>
                        <span wire:loading.remove wire:target="finalizarPagamento">
                            <i class="fas fa-check"></i> Finalizar Pagamento
                        </span>
                        <span wire:loading wire:target="finalizarPagamento">
                            <i class="fas fa-spinner fa-spin"></i> Processando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
