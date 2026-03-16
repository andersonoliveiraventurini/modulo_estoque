<x-layouts.app :title="__('Editar cliente')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->
           
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-pencil-square class="w-5 h-5 text-primary-600" />
                    Editar Produto
                </h2>

                <form id="produtoForm" action="{{ route('produtos.update', $produto) }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $produto->id }}">

                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-clipboard class="w-5 h-5 text-primary-600" />
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="nome" label="Nome do Produto *" value="{{ old('nome', $produto->nome) }}"
                                required class="col-span-2" />

                            <x-input name="codigo_barras" label="Código de Barras"
                                value="{{ old('codigo_barras', $produto->codigo_barras) }}" />

                            <x-input name="sku" label="SKU/Código" value="{{ old('sku', $produto->sku) }}" required />

                            <x-select name="fornecedor_id" label="Fornecedor" required>
                                <option value="">Selecione...</option>
                                @foreach ($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $produto->fornecedor_id) == $fornecedor->id)>
                                        {{ $fornecedor->nome_fantasia }}
                                    </option>
                                @endforeach
                            </x-select>

                            <x-input name="part_number" label="Part Number" value="{{ old('part_number', $produto->part_number) }}" />

                            <x-select name="cor_id" label="Cor">
                                <option value="">Selecione...</option>
                                @foreach ($cores as $cor)
                                    <option value="{{ $cor->id }}" @selected(old('cor_id', $produto->cor_id) == $cor->id)>
                                        {{ $cor->nome }}
                                    </option>
                                @endforeach
                            </x-select>

                            @php
                                $unidades = [
                                    'UN'  => 'UN - Unidade',
                                    'PC'  => 'PC - Peça',
                                    'CX'  => 'CX - Caixa',
                                    'KG'  => 'KG - Quilograma',
                                    'G'   => 'G - Grama',
                                    'M'   => 'M - Metro',
                                    'M2'  => 'M2 - Metro quadrado',
                                    'M3'  => 'M3 - Metro cúbico',
                                    'L'   => 'L - Litro',
                                    'ML'  => 'ML - Mililitro',
                                    'PAR' => 'PAR - Par',
                                    'RL'  => 'RL - Rolo',
                                    'PT'  => 'PT - Pacote',
                                ];
                            @endphp
                            <x-select name="unidade_medida" label="Unidade de Medida *" required>
                                <option value="">Selecione...</option>
                                @foreach ($unidades as $val => $label)
                                    <option value="{{ $val }}" @selected(old('unidade_medida', $produto->unidade_medida) == $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </x-select>

                            <x-input type="number" step="0.01" name="peso" label="Peso"
                                value="{{ old('peso', $produto->peso) }}" />

                            <x-input name="estoque_minimo" label="Estoque Mínimo" placeholder="0.00"
                                value="{{ old('estoque_minimo', $produto->estoque_minimo) }}" />

                            <x-select id="categoria_id" name="categoria_id" label="Categoria">
                                <option value="">Selecione...</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}" @selected(old('categoria_id', $produto->categoria_id) == $categoria->id)>
                                        {{ $categoria->nome }}
                                    </option>
                                @endforeach
                            </x-select>

                            <x-select id="subcategoria_id" name="subcategoria_id" label="Subcategoria">
                                <option value="">Selecione...</option>
                                @foreach ($subcategorias as $sub)
                                    <option value="{{ $sub->id }}" @selected(old('subcategoria_id', $produto->subcategoria_id) == $sub->id)>
                                        {{ $sub->nome }}
                                    </option>
                                @endforeach
                            </x-select>

                            <x-select name="flag_encomenda" label="Produto sob encomenda">
                                <option value="">Selecione...</option>
                                <option value="1" @selected(old('flag_encomenda', $produto->flag_encomenda) == 1)>
                                    Sim
                                </option>
                                <option value="0" @selected(old('flag_encomenda', $produto->flag_encomenda) === 0)>
                                    Não
                                </option>
                            </x-select>
                        </div>

                        <!-- Informações Fiscais - Entrada -->
                        <div class="space-y-4"><br />
                            <hr />
                            <h3 class="text-lg font-medium flex items-center gap-2">
                                <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                Informações Fiscais - Entrada
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                 <x-input name="preco_base" label="Preço de Base - sem imposto" placeholder="0.00" 
                                   value="{{ old('preco_base', $produto->preco_base) }}" />
                                   
                                <x-input type="number" step="0.01" name="icms" label="% ICMS"
                                    value="{{ old('icms', $produto->icms) }}" />

                                <x-input type="number" step="0.01" name="pis" label="PIS"
                                    value="{{ old('pis', $produto->pis) }}" />

                                <x-input type="number" step="0.01" name="cofins" label="Cofins"
                                    value="{{ old('cofins', $produto->cofins) }}" />

                                    <x-input type="number" step="0.01" name="mva" label="MVA"
                                    value="{{ old('mva', $produto->mva) }}" />
                            </div>

                            <!-- Financeiro -->
                            <br />
                            <hr />
                            <h3 class="text-lg font-medium flex items-center gap-2">
                                <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                Financeiro
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">

                                <x-input name="preco_custo" label="Preço de Custo" placeholder="0.00"
                                    value="{{ old('preco_custo', $produto->preco_custo) }}" />

                                <x-input name="custo_frete_fornecedor" label="Custo Frete Fornecedor" placeholder="0.00"
                                    value="{{ old('custo_frete_fornecedor', $produto->custo_frete_fornecedor) }}" />

                                <x-input name="custo_operacional" label="Custo Operacional" placeholder="0.00"
                                    value="{{ old('custo_operacional', $produto->custo_operacional) }}" />

                                <x-input name="margem_lucro" label="Margem de Lucro" placeholder="0.00"
                                    value="{{ old('margem_lucro', $produto->margem_lucro) }}" />

                                <x-input name="preco_venda" label="Preço de Venda" placeholder="0.00"
                                    value="{{ old('preco_venda', $produto->preco_venda) }}" />

                                <x-select name="liberar_desconto" label="Liberar desconto">
                                    <option value="">Selecione...</option>
                                    <option value="1" @selected(old('liberar_desconto', $produto->liberar_desconto) == 1)>Sim</option>
                                    <option value="0" @selected(old('liberar_desconto', $produto->liberar_desconto) === 0)>Não</option>
                                </x-select>
                            </div>

                            <!-- Observações -->
                            <div class="space-y-4">
                                <hr />
                                <h3 class="text-lg font-medium flex items-center gap-2">
                                    <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                    Observações
                                </h3>
                                <div class="space-y-6">
                                    <x-textarea name="descricao" label="Descrição"
                                        placeholder="Escreva as características e detalhes do produto..."
                                        rows="3">{{ old('descricao', $produto->descricao) }}</x-textarea>

                                    <x-textarea name="observacoes" label="Observações"
                                        placeholder="Observações gerais, condições especiais, informações adicionais..."
                                        rows="3">{{ old('observacoes', $produto->observacoes) }}</x-textarea>
                                </div>

                                <!-- Imagens -->
                                <h3 class="text-lg font-medium flex items-center gap-2">
                                    <x-heroicon-o-photo class="w-5 h-5 text-primary-600" />
                                    Imagens do Produto
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach ($produto->images as $img)
                                        <div class="relative border rounded-lg p-2 flex flex-col items-center">
                                            <img src="{{ asset('storage/' . $img->caminho) }}"
                                                class="w-32 h-32 object-cover rounded shadow">

                                            <div class="mt-2 text-sm">
                                                @if ($img->principal)
                                                    <span class="text-green-600 font-semibold">Principal</span>
                                                @else
                                                    <x-button type="button" 
                                                        onclick="event.preventDefault(); document.getElementById('form-principal-{{$img->id}}').submit();"
                                                        class="text-blue-600 underline text-xs" variant="text">Definir como Principal</x-button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6">
                                    <label class="text-sm font-medium">Adicionar Novas Imagens</label>
                                    <input type="file" name="images[]" id="imagesInput" multiple
                                        accept="image/png,image/jpeg,image/gif"
                                        class="mt-2 block w-full text-sm text-gray-600 border rounded-lg p-2">
                                    <div id="imagePreviews" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                                </div>

                                <!-- Informações Fiscais - Saída -->
                                <div class="space-y-4"><br />
                                    <hr />
                                    <h3 class="text-lg font-medium flex items-center gap-2">
                                        <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                        Informações Fiscais - Saída
                                    </h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                        @php
                                            $tiposSped = [
                                                '00' => '00 - Mercadoria para Revenda',
                                                '01' => '01 - Matéria-Prima',
                                                '02' => '02 - Embalagem',
                                                '03' => '03 - Produto em Processo',
                                                '04' => '04 - Produto Acabado',
                                                '05' => '05 - Subproduto',
                                                '06' => '06 - Produto Intermediário',
                                                '07' => '07 - Material de Uso e Consumo',
                                                '08' => '08 - Ativo Imobilizado',
                                                '09' => '09 - Serviços',
                                                '10' => '10 - Outros Insumos',
                                                '99' => '99 - Outras',
                                            ];
                                        @endphp
                                        <x-select name="tipo_sped" label="Tipo Produto SPED">
                                            <option value="">Selecione...</option>
                                            @foreach ($tiposSped as $key => $label)
                                                <option value="{{ $key }}" @selected(old('tipo_sped', $produto->tipo_sped) == $key)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </x-select>

                                        <x-input name="ncm"
                                            label="NCM - Nomenclatura Comum do Mercosul (8 dígitos)"
                                            placeholder="12345678" value="{{ old('ncm', $produto->ncm) }}"
                                            required />

                                        <x-input type="number" step="0.01" name="substituicao_tributaria"
                                            label="% Substituição Tributária"
                                            value="{{ old('substituicao_tributaria', $produto->substituicao_tributaria) }}" />

                                        <x-input type="number" step="0.01" name="icms" label="% ICMS"
                                            value="{{ old('icms', $produto->icms) }}" />

                                        <x-input type="number" step="0.01" name="pis" label="PIS"
                                            value="{{ old('pis', $produto->pis) }}" />

                                        <x-input type="number" step="0.01" name="cofins" label="Cofins"
                                            value="{{ old('cofins', $produto->cofins) }}" />

                                        <x-input name="classificacao_fiscal" label="CL Fiscal"
                                            value="{{ old('classificacao_fiscal', $produto->classificacao_fiscal) }}" />
                                    </div>
                                </div>

                                <hr class="my-6" />

                                <!-- Ações -->
                                <div class="flex gap-4">
                                    <x-button type="submit" id="btnSubmit">Salvar Alterações</x-button>
                                </div>
                </form>

                {{-- Hidden forms for destroying and setting principal images so they aren't nested --}}
                @foreach ($produto->images as $img)
                    @if (!$img->principal)
                        <form id="form-principal-{{$img->id}}" action="{{ route('produtos.imagens.principal', [$produto, $img]) }}" method="POST" class="hidden">
                            @csrf
                            @method('PATCH')
                        </form>
                    @endif
                    <form id="form-destroy-{{$img->id}}" action="{{ route('produtos.imagens.destroy', [$produto, $img]) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                    
                    {{-- Add a delete button using standard absolute positioning from outside the main form via script --}}
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const container = document.querySelector('img[src="{{ asset('storage/' . $img->caminho) }}"]').parentElement;
                            const deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.className = 'absolute top-2 right-2 text-red-600 text-sm font-bold bg-white rounded-full w-6 h-6 flex items-center justify-center shadow hover:bg-red-50';
                            deleteBtn.innerHTML = '✕';
                            deleteBtn.onclick = function(e) {
                                e.preventDefault();
                                document.getElementById('form-destroy-{{$img->id}}').submit();
                            };
                            container.appendChild(deleteBtn);
                        });
                    </script>
                @endforeach

            </div>
        </div>
    </div>

    <script>
        // Image Previews State
        let selectedFiles = [];
        const imagesInput = document.getElementById('imagesInput');
        const imagePreviews = document.getElementById('imagePreviews');

        imagesInput.addEventListener('change', function() {
            for (const file of this.files) {
                if (file.type.startsWith('image/')) {
                    selectedFiles.push(file);
                }
            }
            imagesInput.value = ''; // Reset input to allow adding the same file again if desired
            renderPreviews();
        });

        function renderPreviews() {
            imagePreviews.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative border rounded-lg p-2 flex flex-col items-center shadow-sm';
                    div.innerHTML = `<img src="${e.target.result}" class="w-full h-32 object-cover rounded shadow-sm">
                                     <span class="text-xs text-center mt-2 truncate w-full" title="${file.name}">${file.name}</span>
                                     <button type="button" class="absolute top-2 right-2 text-red-600 text-sm font-bold bg-white rounded-full w-6 h-6 flex items-center justify-center shadow hover:bg-red-50" onclick="removeFile(${index})">✕</button>`;
                    imagePreviews.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        }

        window.removeFile = function(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
        };

        // AJAX Form Submission
        document.getElementById('produtoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = document.getElementById('btnSubmit');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Salvando...';

            document.querySelectorAll('.js-val-error').forEach(el => el.remove());

            const formData = new FormData(form);
            formData.delete('images[]'); // Remove any files mistakenly left in the default input
            
            selectedFiles.forEach(file => {
                formData.append('images[]', file);
            });

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok || response.redirected) {
                    if (response.url && response.url !== window.location.href) {
                        window.location.href = response.url; // Seguindo o redirect
                    } else {
                        const resData = await response.json().catch(() => null);
                        if (resData && resData.redirect) {
                            window.location.href = resData.redirect;
                        } else {
                            window.location.href = "{{ route('produtos.index') }}"; 
                        }
                    }
                } else if (response.status === 422) {
                    const data = await response.json();
                    let firstErrorInput = null;
                    
                    for (const [field, messages] of Object.entries(data.errors)) {
                        let inputName = field;
                        if (field.includes('.')) {
                            const [base, idx] = field.split('.');
                            inputName = `${base}[${idx}]`;
                        }
                        
                        let input = form.querySelector(`[name="${inputName}"]`) || form.querySelector(`[name="${inputName}[]"]`);
                        
                        if (input) {
                            const err = document.createElement('p');
                            err.className = 'text-red-500 text-xs mt-1 js-val-error mb-2';
                            err.innerText = messages[0];
                            input.parentNode.appendChild(err);
                            if (!firstErrorInput) firstErrorInput = input;
                        }
                    }
                    if (firstErrorInput) {
                        firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    alert('Ocorreu um erro ao processar sua requisição.');
                }
            } catch (error) {
                console.error('Submit error', error);
                alert('Erro na conexão com o servidor.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Salvar Alterações';
            }
        });
    </script>
</x-layouts.app>
