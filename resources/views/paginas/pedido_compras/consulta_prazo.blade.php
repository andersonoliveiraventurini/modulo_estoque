<x-layouts.app title="Consulta de Prazos - Pedidos de Compra">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
                    Consulta de Prazos e Follow-ups
                </h2>
            </div>

            {{-- Filtros --}}
            <form method="GET" class="mb-6 p-4 bg-gray-50 dark:bg-neutral-800 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <x-input name="numero_pedido" label="Nº Pedido" placeholder="Busca..." value="{{ request('numero_pedido') }}" />
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fornecedor</label>
                        <select name="fornecedor_id" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            @foreach($fornecedores as $f)
                                <option value="{{ $f->id }}" @selected(request('fornecedor_id') == $f->id)>{{ $f->nome_fantasia }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" class="mt-1 block w-full border-gray-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 rounded-md shadow-sm text-sm">
                            <option value="">Todos</option>
                            <option value="aguardando" @selected(request('status') == 'aguardando')>Aguardando</option>
                            <option value="parcialmente_recebido" @selected(request('status') == 'parcialmente_recebido')>Parcial</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <x-button type="submit" variant="primary" class="w-full">Filtrar</x-button>
                        <a href="{{ route('pedido_compras.consulta_prazo') }}" class="p-2 text-gray-500 hover:text-indigo-600 bg-white dark:bg-neutral-900 border border-gray-300 dark:border-neutral-700 rounded-md">
                            <x-heroicon-o-arrow-path class="w-5 h-5" />
                        </a>
                    </div>
                </div>
            </form>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-neutral-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Previsão</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Pedido</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fornecedor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Último Follow-up</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-900">
                        @forelse($pedidos as $pedido)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm font-medium {{ $pedido->previsao_entrega < now() ? 'text-red-600 font-bold' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $pedido->previsao_entrega?->format('d/m/Y') ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                <a href="{{ route('pedido_compras.show', $pedido) }}" class="text-indigo-600 hover:underline">
                                    {{ $pedido->numero_pedido ?? '#'.$pedido->id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $pedido->fornecedor->nome_fantasia }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $pedido->status == 'aguardando' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($pedido->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($last = $pedido->followUps->first())
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold text-indigo-600">{{ ucfirst($last->tipo) }}</span>
                                        <span class="truncate max-w-xs">{{ $last->descricao }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $last->created_at->diffForHumans() }} por {{ $last->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs">Sem interações</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="abrirModalFollowup({{ $pedido->id }}, '{{ $pedido->numero_pedido ?? '#'.$pedido->id }}')" 
                                    class="text-amber-600 hover:text-amber-800 p-1" title="Adicionar Follow-up">
                                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Nenhum pedido pendente encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $pedidos->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Follow-up --}}
    <div id="modal-followup" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 w-[500px] shadow-xl relative">
            <button onclick="fecharModalFollowup()" class="absolute top-3 right-3 text-gray-400 hover:text-red-600">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Registrar Follow-up</h3>
            <p id="followup-pedido-label" class="text-sm text-indigo-600 font-medium mb-4"></p>
            
            <form id="form-followup" onsubmit="salvarFollowup(event)">
                @csrf
                <input type="hidden" id="followup-pedido-id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Interação</label>
                        <select id="followup-tipo" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2 text-sm">
                            <option value="cobranca">Cobrança de Entrega</option>
                            <option value="atualizacao_prazo">Atualização de Prazo</option>
                            <option value="observacao">Apenas Observação</option>
                        </select>
                    </div>
                    
                    <div id="div-previsao" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nova Previsão de Entrega</label>
                        <input type="date" id="followup-previsao" class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição / Resultado do Contato</label>
                        <textarea id="followup-descricao" rows="4" required
                            class="mt-1 block w-full border border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white rounded-md px-3 py-2 text-sm"
                            placeholder="Ex: Fornecedor informou atraso na transportadora..."></textarea>
                    </div>

                    <x-button type="submit" variant="primary" class="w-full">Salvar Interação</x-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function abrirModalFollowup(id, label) {
            document.getElementById('followup-pedido-id').value = id;
            document.getElementById('followup-pedido-label').textContent = 'Pedido: ' + label;
            document.getElementById('modal-followup').classList.remove('hidden');
        }

        function fecharModalFollowup() {
            document.getElementById('modal-followup').classList.add('hidden');
            document.getElementById('form-followup').reset();
            document.getElementById('div-previsao').classList.add('hidden');
        }

        document.getElementById('followup-tipo').addEventListener('change', function() {
            if (this.value === 'atualizacao_prazo') {
                document.getElementById('div-previsao').classList.remove('hidden');
            } else {
                document.getElementById('div-previsao').classList.add('hidden');
            }
        });

        function salvarFollowup(e) {
            e.preventDefault();
            const id = document.getElementById('followup-pedido-id').value;
            const data = {
                _token: '{{ csrf_token() }}',
                descricao: document.getElementById('followup-descricao').value,
                tipo: document.getElementById('followup-tipo').value,
                previsao_nova: document.getElementById('followup-previsao').value
            };

            fetch(`/pedido_compras/${id}/followups`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Erro ao salvar follow-up.');
                }
            })
            .catch(() => alert('Erro na requisição.'));
        }
    </script>
    @endpush
</x-layouts.app>
