<x-layouts.app :title="__('Curva de Vendas')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Configuração da Curva de Vendas') }}</flux:heading>
            <div class="flex gap-2">
                <flux:button icon="clock" variant="outline" href="{{ route('curva_vendas.auditoria') }}">
                    {{ __('Histórico de Alterações') }}
                </flux:button>
            </div>
        </div>

        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm">
            <form action="{{ route('curva_vendas.processar') }}" method="POST">
                @csrf
                <div class="grid gap-6 md:grid-cols-2 mb-6">
                    <div>
                        <flux:label for="periodo_inicio">{{ __('Período Início') }}</flux:label>
                        <flux:input type="date" name="periodo_inicio" id="periodo_inicio" 
                            value="{{ $config?->periodo_inicio?->format('Y-m-d') ?? now()->subMonths(3)->format('Y-m-d') }}" 
                            max="{{ now()->format('Y-m-d') }}" required />
                    </div>
                    <div>
                        <flux:label for="periodo_fim">{{ __('Período Fim') }}</flux:label>
                        <flux:input type="date" name="periodo_fim" id="periodo_fim" 
                            value="{{ $config?->periodo_fim?->format('Y-m-d') ?? now()->format('Y-m-d') }}" 
                            max="{{ now()->format('Y-m-d') }}" required />
                    </div>
                </div>

                <div id="parametros-container" class="space-y-6">
                    @php
                        $params = $config?->parametros ?? [
                            ['criterio' => 'valor', 'classes' => [
                                'A' => ['min' => 5000, 'max' => 9999999],
                                'B' => ['min' => 1000, 'max' => 4999.99],
                                'C' => ['min' => 0.01, 'max' => 999.99],
                                'D' => ['min' => 0, 'max' => 0],
                            ]]
                        ];
                    @endphp

                    @foreach($params as $index => $param)
                        <div class="parametro-item p-4 border rounded-lg dark:border-neutral-700" data-index="{{ $index }}">
                            <div class="flex items-center justify-between mb-4">
                                <flux:heading size="sm">{{ __('Parâmetro') }} {{ $index + 1 }}</flux:heading>
                                @if($index > 0)
                                    <flux:button variant="ghost" size="sm" color="red" onclick="removeParametro(this)">{{ __('Remover') }}</flux:button>
                                @endif
                            </div>

                            <div class="mb-4">
                                <flux:label>{{ __('Critério') }}</flux:label>
                                <flux:select name="parametros[{{ $index }}][criterio]">
                                    <option value="valor" {{ $param['criterio'] == 'valor' ? 'selected' : '' }}>{{ __('Valor Total Vendido') }}</option>
                                    <option value="quantidade" {{ $param['criterio'] == 'quantidade' ? 'selected' : '' }}>{{ __('Quantidade Vendida') }}</option>
                                </flux:select>
                            </div>

                            <div class="classes-container space-y-3">
                                <div class="grid grid-cols-4 gap-4 font-medium text-xs text-neutral-500 uppercase">
                                    <div>{{ __('Classe') }}</div>
                                    <div>{{ __('Valor Inicial') }}</div>
                                    <div>{{ __('Valor Final') }}</div>
                                    <div></div>
                                </div>
                                @foreach($param['classes'] as $classe => $faixa)
                                    <div class="grid grid-cols-4 gap-4 items-center">
                                        <flux:input value="{{ $classe }}" name="parametros[{{ $index }}][classes][{{ $classe }}][label]" readonly />
                                        <flux:input type="number" step="0.01" value="{{ $faixa['min'] }}" name="parametros[{{ $index }}][classes][{{ $classe }}][min]" required />
                                        <flux:input type="number" step="0.01" value="{{ $faixa['max'] }}" name="parametros[{{ $index }}][classes][{{ $classe }}][max]" required />
                                        <div class="flex justify-end">
                                            @if(!in_array($classe, ['A', 'B', 'C']))
                                                <flux:button variant="ghost" size="xs" color="red" onclick="removeClasse(this)">{{ __('Remover') }}</flux:button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-4">
                                <flux:button variant="outline" size="sm" onclick="addClasse({{ $index }})">{{ __('Adicionar Classe') }}</flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-between">
                    <flux:button variant="outline" id="btn-add-parametro" onclick="addParametro()">{{ __('Adicionar Parâmetro') }}</flux:button>
                    <flux:button variant="filled" color="indigo" type="submit">{{ __('Processar Curva de Vendas') }}</flux:button>
                </div>
            </form>
        </div>

        @if($produtos->count() > 0)
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm overflow-hidden">
                <flux:heading size="lg" class="mb-4">{{ __('Produtos Classificados') }}</flux:heading>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b dark:border-neutral-700">
                                <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('SKU') }}</th>
                                <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Produto') }}</th>
                                <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Classe') }}</th>
                                <th class="py-3 px-4 font-medium text-sm text-neutral-500">{{ __('Tipo') }}</th>
                                <th class="py-3 px-4 font-medium text-sm text-neutral-500 text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produtos as $produto)
                                <tr class="border-b dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-900/50 transition-colors">
                                    <td class="py-3 px-4 text-sm">{{ $produto->sku }}</td>
                                    <td class="py-3 px-4 text-sm">{{ $produto->nome }}</td>
                                    <td class="py-3 px-4 text-sm">
                                        @php
                                            $color = match($produto->classificacao_curva) {
                                                'A' => 'lime',
                                                'B' => 'blue',
                                                'C' => 'orange',
                                                default => 'zinc'
                                            };
                                        @endphp
                                        <flux:badge color="{{ $color }}" variant="solid">{{ $produto->classificacao_curva }}</flux:badge>
                                    </td>
                                    <td class="py-3 px-4 text-sm">
                                        @if($produto->classificacao_manual)
                                            <flux:badge color="purple" size="xs">{{ __('Manual') }}</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="xs">{{ __('Automático') }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right">
                                        <flux:button variant="ghost" size="xs" onclick="openReclassifyModal({{ $produto->id }}, '{{ $produto->nome }}', '{{ $produto->classificacao_curva }}')">
                                            {{ __('Reclassificar') }}
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $produtos->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Modal de Reclassificação --}}
    <div id="modal-reclassificar" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-neutral-800 rounded-xl p-6 w-full max-w-md shadow-xl border border-neutral-200 dark:border-neutral-700">
            <flux:heading size="lg" class="mb-4">{{ __('Reclassificar Produto') }}</flux:heading>
            <p id="modal-produto-nome" class="mb-4 text-sm text-neutral-600 dark:text-neutral-400"></p>
            
            <form id="form-reclassificar" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <flux:label>{{ __('Nova Classificação') }}</flux:label>
                        <flux:select name="nova_classificacao" id="modal-select-classe">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </flux:select>
                    </div>
                    <div>
                        <flux:label>{{ __('Justificativa') }}</flux:label>
                        <flux:textarea name="justificativa" placeholder="{{ __('Descreva o motivo da alteração...') }}" required />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <flux:button variant="ghost" onclick="closeReclassifyModal()">{{ __('Cancelar') }}</flux:button>
                    <flux:button variant="filled" color="indigo" type="submit">{{ __('Salvar') }}</flux:button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function addParametro() {
            const container = document.getElementById('parametros-container');
            const index = container.querySelectorAll('.parametro-item').length;
            if (index >= 3) return;

            const html = `
                <div class="parametro-item p-4 border rounded-lg dark:border-neutral-700" data-index="${index}">
                    <div class="flex items-center justify-between mb-4">
                        <flux:heading size="sm">{{ __('Parâmetro') }} ${index + 1}</flux:heading>
                        <flux:button variant="ghost" size="sm" color="red" onclick="removeParametro(this)">{{ __('Remover') }}</flux:button>
                    </div>

                    <div class="mb-4">
                        <flux:label>{{ __('Critério') }}</flux:label>
                        <flux:select name="parametros[${index}][criterio]">
                            <option value="valor">{{ __('Valor Total Vendido') }}</option>
                            <option value="quantidade">{{ __('Quantidade Vendida') }}</option>
                        </flux:select>
                    </div>

                    <div class="classes-container space-y-3">
                        <div class="grid grid-cols-4 gap-4 font-medium text-xs text-neutral-500 uppercase">
                            <div>{{ __('Classe') }}</div>
                            <div>{{ __('Valor Inicial') }}</div>
                            <div>{{ __('Valor Final') }}</div>
                            <div></div>
                        </div>
                        <div class="grid grid-cols-4 gap-4 items-center">
                            <flux:input value="A" name="parametros[${index}][classes][A][label]" readonly />
                            <flux:input type="number" step="0.01" name="parametros[${index}][classes][A][min]" required />
                            <flux:input type="number" step="0.01" name="parametros[${index}][classes][A][max]" required />
                            <div></div>
                        </div>
                        <div class="grid grid-cols-4 gap-4 items-center">
                            <flux:input value="B" name="parametros[${index}][classes][B][label]" readonly />
                            <flux:input type="number" step="0.01" name="parametros[${index}][classes][B][min]" required />
                            <flux:input type="number" step="0.01" name="parametros[${index}][classes][B][max]" required />
                            <div></div>
                        </div>
                        <div class="grid grid-cols-4 gap-4 items-center">
                            <flux:input value="C" name="parametros[${index}][classes][C][label]" readonly />
                            <flux:input type="number" step="0.01" name="parametros[${index}][classes][C][min]" required />
                            <flux:input type="number" step="0.01" name="parametros[${index}][classes][C][max]" required />
                            <div></div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <flux:button variant="outline" size="sm" onclick="addClasse(${index})">{{ __('Adicionar Classe') }}</flux:button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeParametro(btn) {
            btn.closest('.parametro-item').remove();
        }

        function addClasse(paramIndex) {
            const container = document.querySelector(`.parametro-item[data-index="${paramIndex}"] .classes-container`);
            const nextChar = String.fromCharCode(65 + container.querySelectorAll('.grid').length - 1); // Start from A, B, C...
            
            const html = `
                <div class="grid grid-cols-4 gap-4 items-center">
                    <flux:input value="${nextChar}" name="parametros[${paramIndex}][classes][${nextChar}][label]" />
                    <flux:input type="number" step="0.01" name="parametros[${paramIndex}][classes][${nextChar}][min]" required />
                    <flux:input type="number" step="0.01" name="parametros[${paramIndex}][classes][${nextChar}][max]" required />
                    <div class="flex justify-end">
                        <flux:button variant="ghost" size="xs" color="red" onclick="removeClasse(this)">{{ __('Remover') }}</flux:button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeClasse(btn) {
            btn.closest('.grid').remove();
        }

        function openReclassifyModal(id, nome, classe) {
            document.getElementById('modal-produto-nome').textContent = nome;
            document.getElementById('modal-select-classe').value = classe;
            document.getElementById('form-reclassificar').action = `/curva_vendas/reclassificar/${id}`;
            document.getElementById('modal-reclassificar').classList.remove('hidden');
        }

        function closeReclassifyModal() {
            document.getElementById('modal-reclassificar').classList.add('hidden');
        }
    </script>
    @endpush
</x-layouts.app>
