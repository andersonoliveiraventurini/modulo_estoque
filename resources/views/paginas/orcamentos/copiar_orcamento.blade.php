<x-layouts.app :title="__('Copiar Orçamento')">

    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Copiar Orçamento para outro Cliente
                </h2>
                <!-- Pesquisa de Produtos -->
                <div class="space-y-4">
                    <!-- Campos iniciais -->
                    <form method="POST" id="formDuplicarOrcamento" class="space-y-8">
                        @csrf

                        <x-select name="cliente_id" id="cliente_id"
                            label="Selecione o Cliente para o qual deseja copiar o orçamento" required>
                            <option value="">Selecione um cliente...</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->id }} - {{ $cliente->nome }} - {{ $cliente->nome_fantasia }} - {{ $cliente->razao_social }} - {{ $cliente->tratamento }}
                                </option>
                            @endforeach
                        </x-select>

                        <x-select name="orcamento_id" id="orcamento_id" label="Orçamento a ser copiado" required>
                            <option value="">Selecione um orçamento...</option>
                            @foreach ($orcamentos as $orcamento)
                                <option value="{{ $orcamento->id }}">
                                    {{ $orcamento->id }} - {{ $orcamento->obra }} - Cliente: {{ $orcamento->cliente->id }} - {{ $orcamento->cliente->nome }}
                                </option>
                            @endforeach
                        </x-select>

                        <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
                            Copiar Orçamento
                        </button>
                    </form>

                    <script>
                        document.getElementById('formDuplicarOrcamento').addEventListener('submit', function(e) {
                            e.preventDefault();

                            const orcamentoId = document.getElementById('orcamento_id').value;
                            const clienteId = document.getElementById('cliente_id').value;

                            if (!orcamentoId) {
                                alert('Selecione um orçamento.');
                                return;
                            }

                            let action = `/orcamentos/duplicar/${orcamentoId}`;

                            if (clienteId) {
                                action += `/${clienteId}`;
                            }

                            this.action = action;
                            this.submit();
                        });
                    </script>

                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
