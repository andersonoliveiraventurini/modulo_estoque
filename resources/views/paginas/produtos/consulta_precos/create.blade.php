<x-layouts.app :title="__('Cotar item')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-primary-600" />
                    Solicitar cotação
                </h2>
                <form action="{{ route('consulta_preco.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="descricao" label="Descrição do item" placeholder="Digite a descrição" required
                                class="col-span-2" value="{{ old('descricao') }}" />
                            <x-input name="quantidade" label="Quantidade" placeholder="Digite a quantidade" required
                                class="col-span-2" value="{{ old('quantidade') }}" />
                            <x-select name="cor" label="Cor">
                                <option value="">Selecione...</option>
                                @foreach ($cores as $cor)
                                <option value="{{ $cor->id }}">{{ $cor->nome }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="fornecedor_id" label="Fornecedor">
                                <option value="">Selecione...</option>
                                @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="orcamento_id" label="Orçamento">
                                <option value="">Selecione...</option>
                                @foreach ($orcamentos as $orcamento)
                                <option value="{{ $orcamento->id }}">{{ $orcamento->id }} - {{ $orcamento->obra }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <x-textarea name="observacoes" label="Observações"
                            placeholder="Digite os detalhes adicionais..." rows="4" value="{{ old('observacoes') }}"></x-textarea>
                        <!-- Ações -->
                        <div class="flex gap-4">
                            <x-button type="submit">Solicitar Cotação do Produto</x-button>
                            <x-button type="reset">Limpar Formulário</x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>