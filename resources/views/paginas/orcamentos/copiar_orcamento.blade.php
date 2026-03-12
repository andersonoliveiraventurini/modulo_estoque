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
                                    {{ $cliente->id }} - {{ $cliente->nome }} - {{ $cliente->nome_fantasia }} -
                                    {{ $cliente->razao_social }} - {{ $cliente->tratamento }}
                                </option>
                            @endforeach
                        </x-select>

                        <x-select name="orcamento_id" id="orcamento_id" label="Orçamento a ser copiado" required>
                            <option value="">Selecione um orçamento...</option>
                            @foreach ($orcamentos as $orcamento)
                                <option value="{{ $orcamento->id }}">
                                    {{ $orcamento->id }} - {{ $orcamento->obra }} - Cliente:
                                    {{ $orcamento->cliente->id }} - {{ $orcamento->cliente->nome }}
                                </option>
                            @endforeach
                        </x-select>
                        <div
                            class="flex items-start gap-2 mt-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>
                                <strong class="font-medium">Atenção:</strong>
                                Encomendas <strong class="font-medium">não serão
                                    duplicadas</strong> para o novo cliente. Apenas orçamento serão
                                copiados.
                            </span>
                        </div>
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
