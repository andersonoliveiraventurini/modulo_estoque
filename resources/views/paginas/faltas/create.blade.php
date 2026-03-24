<x-layouts.app title="Registrar Falta">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="bg-white p-6 shadow rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-neutral-200 dark:border-neutral-700">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold flex items-center gap-2 text-gray-900 dark:text-gray-100">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-indigo-500" />
                    Registrar Falta de Produto
                </h2>
                <a href="{{ route('faltas.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 transition">&larr; Voltar</a>
            </div>

            <form action="{{ route('faltas.store') }}" method="POST" id="form-falta" class="space-y-6">
                @csrf

                {{-- Cabeçalho --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl border border-gray-100 dark:border-zinc-700">
                    <div>
                        <flux:select name="cliente_id" id="select-cliente" label="Cliente (Cadastrado)" searchable placeholder="Selecione um cliente...">
                            <option value="">-- Selecione --</option>
                            @foreach($clientes as $c)
                                <option value="{{ $c->id }}" data-nome="{{ $c->nome }}">{{ $c->id }} - {{ $c->nome }}</option>
                            @endforeach
                        </flux:select>
                    </div>
                    
                    <flux:input name="nome_cliente" label="Nome do Cliente (Livre)" placeholder="Ou digite diretamente caso não tenha cadastro" value="{{ old('nome_cliente') }}" />
                    
                    <div>
                        <flux:select name="vendedor_id" required label="Vendedor Responsável *">
                            <option value="">-- Selecione --</option>
                            @foreach($vendedores as $v)
                                <option value="{{ $v->id }}" @selected(old('vendedor_id') == $v->id)>
                                    {{ $v->user->name }}
                                </option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="md:col-span-3">
                        <flux:textarea name="observacao" label="Observações Internas" rows="2" placeholder="Motivo da falta, urgência, etc...">{{ old('observacao') }}</flux:textarea>
                    </div>
                </div>

                {{-- Itens --}}
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <x-heroicon-o-list-bullet class="w-5 h-5 text-gray-400" />
                            Produtos em Falta
                        </h3>
                        <flux:button type="button" variant="primary" id="btn-add-item" size="sm">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" /> Adicionar Item
                        </flux:button>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-zinc-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700" id="tabela-itens">
                            <thead class="bg-gray-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase w-[35%]">Produto (SKU ou Nome)</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase">Descrição</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase w-24">Qtd.</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase w-32">V. Unitário</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase w-32">V. Total</th>
                                    <th class="px-2 py-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="itens-body" class="divide-y divide-gray-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                {{-- Linhas JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end p-4 bg-gray-50 dark:bg-zinc-800 rounded-lg">
                        <div class="text-right">
                            <span class="text-xs font-bold text-gray-400 dark:text-zinc-500 uppercase tracking-widest">VALOR TOTAL ESTIMADO</span>
                            <div class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400" id="total-geral">R$ 0,00</div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-zinc-700">
                    <flux:button type="submit" variant="primary" class="px-10">Finalizar Registro</flux:button>
                    <flux:button href="{{ route('faltas.index') }}" variant="ghost" class="px-6">Cancelar</flux:button>
                </div>
            </form>
        </div>
    </div>

    {{-- Template de Linha --}}
    <template id="linha-template">
        <tr class="dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors">
            <td class="px-4 py-2 relative">
                <input type="hidden" name="itens[__IDX__][produto_id]" class="campo-produto-id">
                <input type="text" class="w-full text-sm border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 rounded-md campo-busca-produto focus:ring-indigo-500"
                       placeholder="Buscar produto..." autocomplete="off">
            </td>
            <td class="px-4 py-2">
                <input type="text" name="itens[__IDX__][descricao_produto]" class="w-full text-sm border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 rounded-md campo-descricao" placeholder="...">
            </td>
            <td class="px-4 py-2 text-center">
                <input type="number" name="itens[__IDX__][quantidade]" class="w-full text-sm text-center border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 rounded-md campo-qtd"
                       step="1" min="0.001" value="1">
            </td>
            <td class="px-4 py-2">
                <input type="number" name="itens[__IDX__][valor_unitario]" class="w-full text-sm border-gray-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 rounded-md campo-valor"
                       step="1" min="0" value="0">
            </td>
            <td class="px-4 py-2 text-sm font-bold text-gray-900 dark:text-zinc-100 campo-total">
                R$ 0,00
            </td>
            <td class="px-2 py-2 text-center">
                <button type="button" class="text-red-500 hover:text-red-700 btn-remove-item p-1 rounded-full hover:bg-red-50 transition">
                    <x-heroicon-o-trash class="w-5 h-5" />
                </button>
            </td>
        </tr>
    </template>

    @push('scripts')
    <script>
    let itemIndex = 0;

    function adicionarLinha() {
        const template = document.getElementById('linha-template').innerHTML;
        const linha = template.replaceAll('__IDX__', itemIndex++);
        const tbody = document.getElementById('itens-body');
        tbody.insertAdjacentHTML('beforeend', linha);
        inicializarLinha(tbody.lastElementChild);
    }

    function inicializarLinha(tr) {
        const campoBusca = tr.querySelector('.campo-busca-produto');
        const campoProdutoId = tr.querySelector('.campo-produto-id');
        const campoDescricao = tr.querySelector('.campo-descricao');
        const campoQtd = tr.querySelector('.campo-qtd');
        const campoValor = tr.querySelector('.campo-valor');
        const campoTotal = tr.querySelector('.campo-total');

        let timeout;
        let sugestoes;

        campoBusca.addEventListener('input', function() {
            clearTimeout(timeout);
            if (this.value.length < 2) {
                sugestoes?.remove();
                return;
            }
            timeout = setTimeout(() => {
                fetch(`{{ route('faltas.buscar_produto') }}?q=${encodeURIComponent(this.value)}`)
                    .then(r => r.json())
                    .then(produtos => {
                        sugestoes?.remove();
                        if (!produtos.length) return;
                        
                        sugestoes = document.createElement('ul');
                        // Estilização robusta com posicionamento FIXED para não ser cortado por overflow
                        sugestoes.className = 'fixed z-[9999] bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-2xl rounded-lg max-h-64 overflow-y-auto py-2 text-sm';
                        
                        function posicionarSugestoes() {
                            const rect = campoBusca.getBoundingClientRect();
                            sugestoes.style.top = rect.bottom + 'px';
                            sugestoes.style.left = rect.left + 'px';
                            sugestoes.style.width = rect.width + 'px';
                        }
                        
                        posicionarSugestoes();

                        produtos.forEach(p => {
                            const li = document.createElement('li');
                            li.className = 'px-4 py-2.5 cursor-pointer hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:text-zinc-200 transition-colors border-b last:border-0 border-gray-50 dark:border-zinc-700/50';
                            
                            const sku = p.sku ? `<span class="font-mono text-[10px] bg-zinc-100 dark:bg-zinc-700 px-1.5 py-0.5 rounded mr-2 text-zinc-500 dark:text-zinc-400 group-hover:text-white">${p.sku}</span>` : '';
                            li.innerHTML = `${sku}<span class="font-medium">${p.nome}</span>`;
                            
                            li.addEventListener('click', () => {
                                campoProdutoId.value = p.id;
                                campoBusca.value = p.nome;
                                campoDescricao.value = p.nome;
                                campoValor.value = p.preco_venda ?? 0;
                                sugestoes.remove();
                                calcularLinha(campoQtd, campoValor, campoTotal);
                            });
                            sugestoes.appendChild(li);
                        });
                        
                        document.body.appendChild(sugestoes);
                    });
            }, 300);
        });

        [campoQtd, campoValor].forEach(c => c.addEventListener('input', () => calcularLinha(campoQtd, campoValor, campoTotal)));
        tr.querySelector('.btn-remove-item').addEventListener('click', () => { tr.remove(); calcularTotal(); });
    }

    function calcularLinha(campoQtd, campoValor, campoTotal) {
        const total = (parseFloat(campoQtd.value) || 0) * (parseFloat(campoValor.value) || 0);
        campoTotal.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        calcularTotal();
    }

    function calcularTotal() {
        let total = 0;
        document.querySelectorAll('.campo-total').forEach(el => {
            const val = el.textContent.replace(/[^\d,]/g, '').replace(',', '.');
            total += parseFloat(val) || 0;
        });
        document.getElementById('total-geral').textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    // Fecha sugestões ao clicar fora ou rolar a página
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.campo-busca-produto')) {
            document.querySelectorAll('ul.fixed').forEach(u => u.remove());
        }
    });

    window.addEventListener('scroll', () => {
        document.querySelectorAll('ul.fixed').forEach(u => u.remove());
    }, true);

    document.getElementById('form-falta').addEventListener('submit', function(e) {
        if (document.getElementById('itens-body').children.length === 0) {
            e.preventDefault();
            alert('Adicione ao menos um item antes de salvar.');
        }
    });

    // Inicia com uma linha
    adicionarLinha();
    </script>
    @endpush
</x-layouts.app>
