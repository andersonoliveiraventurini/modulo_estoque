<x-layouts.app :title="__('Editar vendedor')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Editar Vendedor
                </h2>

                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Usuário vinculado:
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">
                        {{ $vendedor->user?->name ?? 'Usuário não encontrado' }}
                    </span>
                </p>

                <form action="{{ route('vendedores.update', $vendedor) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-select name="externo" label="Tipo">
                            <option value="0" @selected((int) $vendedor->externo === 0)>Vendedor Interno</option>
                            <option value="1" @selected((int) $vendedor->externo === 1)>Vendedor Externo</option>
                            <option value="2" @selected((int) $vendedor->externo === 2)>Assistente de Vendas</option>
                        </x-select>

                        <x-input
                            name="desconto"
                            label="Desconto na venda %"
                            type="number"
                            min="0"
                            max="30"
                            step="1"
                            value="{{ old('desconto', $vendedor->desconto) }}"
                            placeholder="Porcentagem de desconto (0 a 30)"
                            required />
                    </div>

                    <div class="flex gap-4">
                        <x-button type="submit">Salvar alterações</x-button>
                        <a href="{{ route('vendedores.index') }}">
                            <x-button type="button" variant="secondary">Cancelar</x-button>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const descontoInput = document.querySelector('[name="desconto"]');
            descontoInput.addEventListener("input", function () {
                let val = parseInt(this.value.replace(/[^0-9]/g, "")) || 0;
                if (val < 0) val = 0;
                if (val > 30) val = 30;
                this.value = val;
            });
        });
    </script>
</x-layouts.app>
