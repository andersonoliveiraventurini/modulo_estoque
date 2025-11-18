<x-layouts.app :title="__('Editar item de cotação')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-primary-600" />
                    Editar cotação
                </h2>
                <form action="{{ route('consulta_preco.update', $consulta->id) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')
                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="descricao" label="Descrição do item" placeholder="Digite a descrição"
                                required class="col-span-2" value="{{ old('descricao') ?? $consulta->descricao }}" />
                            <x-input name="quantidade" label="Quantidade" placeholder="Digite a quantidade"
                                value="{{ old('quantidade') ?? $consulta->quantidade }}" required />

                            <x-input name="preco_compra" label="Preço de compra" placeholder="Digite o preço de compra"
                                value="{{ old('preco_compra') ?? ($consulta->preco_compra ?? '') }}" required x-data
                                x-init="$el.addEventListener('input', function(e) {
                                    let value = e.target.value
                                        .replace(/\D/g, '') // Remove tudo que não é número
                                        .replace(/(\d)(\d{2})$/, '$1,$2') // Coloca a vírgula antes das 2 últimas casas
                                        .replace(/(?=(\d{3})+(\D))\B/g, '.'); // Coloca ponto como separador de milhar
                                    e.target.value = value;
                                })" />

                            <x-input name="preco_venda" label="Preço de venda" placeholder="Digite o preço de venda"
                                value="{{ old('preco_venda') ?? ($consulta->preco_venda ?? '') }}" required x-data
                                x-init="$el.addEventListener('input', function(e) {
                                    let value = e.target.value
                                        .replace(/\D/g, '')
                                        .replace(/(\d)(\d{2})$/, '$1,$2')
                                        .replace(/(?=(\d{3})+(\D))\B/g, '.');
                                    e.target.value = value;
                                })" />
                            <x-select name="cor" label="Cor">
                                <option value="">Selecione...</option>
                                @foreach ($cores as $cor)
                                    <option value="{{ $cor->id }}"
                                        {{ old('cor', $consulta->cor ?? '') == $cor->id ? 'selected' : '' }}>
                                        {{ $cor->nome }}
                                    </option>
                                @endforeach
                            </x-select>
                            <x-select name="fornecedor_id" label="Fornecedor">
                                <option value="">Selecione...</option>
                                @foreach ($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}"
                                        {{ old('fornecedor_id', $consulta->fornecedor_id ?? '') == $fornecedor->id ? 'selected' : '' }}>
                                        {{ $fornecedor->nome_fantasia }}
                                    </option>
                                @endforeach
                            </x-select>                            
                            <x-input name="prazo_entrega" label="Prazo de entrega" placeholder="Digite o prazo de entrega"
                                value="{{ old('prazo_entrega') ?? $consulta->prazo_entrega }}" required />
                        </div>
                        <x-textarea name="observacoes" label="Observações"
                            placeholder="Digite os detalhes adicionais..." rows="4"
                            value="{{ old('observacoes') ?? $consulta->observacoes }}"></x-textarea>
                        <!-- Ações -->
                        <div class="flex gap-4">
                            <x-button type="submit">Salvar informações</x-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
