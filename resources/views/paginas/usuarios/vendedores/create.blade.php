<x-layouts.app :title="__('Cadastrar vendedor')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Cadastro de vendedor
                </h2>
                <form action="{{ route('vendedores.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="user_id" label="Vendedor">
                                <option value="">Selecione quem será o vendedor</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="externo" label="Externo">
                                <option value="">Selecione</option>
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </x-select>
                            <x-input name="desconto" label="Desconto na venda %" type="number" min="0"
                                max="30" step="1" value="0"
                                placeholder="Digite a porcentagem de desconto (0 a 30)" required />
                        </div>
                    </div>

                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit">Cadastrar vendedor</x-button>
                    </div>
                    <!-- Botões -->
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const descontoInput = document.querySelector('[name="desconto"]');

            descontoInput.addEventListener("input", function() {
                let val = this.value.replace(/[^0-9]/g, ""); // só números
                val = parseInt(val) || 0;

                if (val < 0) val = 0;
                if (val > 30) val = 30;

                this.value = val;
                renderProdutos?.(); // só chama se a função existir
            });
        });
    </script>

</x-layouts.app>
