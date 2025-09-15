<x-layouts.app :title="__('Criar item')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-600" />
                    Cadastro de produto
                </h2>
                <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-clipboard class="w-5 h-5 text-primary-600" />
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="nome" label="Nome do Produto *" placeholder="Digite o nome" required
                                class="col-span-2" />
                            <x-input name="codigo_barras" label="Código de Barras" placeholder="7891234567890" />
                            <x-input name="sku" label="SKU/Código" placeholder="PRD123456" />
                            <x-select name="fornecedor_id" label="Fornecedor">
                                <option value="">Selecione...</option>
                                @foreach ($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome_fantasia }}</option>
                                @endforeach
                            </x-select>
                            <x-input name="marca" label="Marca" placeholder="Digite a marca" />
                            <x-input name="modelo" label="Modelo" placeholder="Digite o modelo" />
                           <x-select name="cor" label="Cor">
                                <option value="">Selecione...</option>
                                @foreach ($cores as $cor)
                                    <option value="{{ $cor->id }}">{{ $cor->nome }}</option>
                                @endforeach
                            </x-select>

                            <x-select name="unidade" label="Unidade de Medida *">
                                <option value="">Selecione...</option>
                                <option value="UN - Unidade">UN - Unidade</option>
                                <option value="PC - Peça">PC - Peça</option>
                                <option value="KG - Quilograma">KG - Quilograma</option>
                                <option value="G - Grama">G - Grama</option>
                                <option value="L - Litro">L - Litro</option>
                                <option value="ML - Mililitro">ML - Mililitro</option>
                                <option value="M - Metro">M - Metro</option>
                                <option value="CM - Centímetro">CM - Centímetro</option>
                                <option value="M2 - Metro Quadrado">M2 - Metro Quadrado</option>
                                <option value="M3 - Metro Cúbico">M3 - Metro Cúbico</option>
                                <option value="CX - Caixa">CX - Caixa</option>
                                <option value="PAR - Par">PAR - Par</option>
                                <option value="DZ - Dúzia">DZ - Dúzia</option>
                                <option value="PT - Pacote">PT - Pacote</option>
                            </x-select>

                            <x-input type="number" step="0.01" name="peso" label="Peso" placeholder="0.000" />
                            <x-input name="estoque_minimo" label="Estoque Mínimo" placeholder="0.00" />
                            <x-select id="categoria_id" name="categoria_id" label="Categoria">
                                <option value="">Selecione...</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nome }}</option>
                                @endforeach
                            </x-select>

                            <x-select id="subcategoria_id" name="subcategoria_id" label="Subcategoria"
                                style="display:none;">
                                <option value="">Selecione...</option>
                            </x-select>

                            <x-select name="flag_encomenda" label="Produto sob encomenda">
                                <option value="">Selecione...</option>
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </x-select>
                        </div>
                        <!-- Endereço -->
                        <div class="space-y-4">
                            <!-- Endereço -->
                            <div class="space-y-4"><br />
                                <hr />
                                <h3 class="text-lg font-medium flex items-center gap-2">
                                    <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                    Informações Fiscais - Entrada
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">

                                    <x-input type="number" step="0.01" name="icms" label="% ICMS" />
                                    <x-input type="number" step="0.01" name="pis" label="PIS" />
                                    <x-input type="number" step="0.01" name="cofins" label="Cofins" />
                                </div>
                                <!-- Endereço -->
                                <br />
                                <hr />
                                <h3 class="text-lg font-medium flex items-center gap-2">
                                    <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                    Financeiro
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">

                                    <x-input name="preco_custo" label="Preço de Custo" placeholder="0.00" />
                                    <x-input name="custo_frete_fornecedor" label="Custo Frete Fornecedor"
                                        placeholder="0.00" />
                                    <x-input name="custo_operacional" label="Custo Operacional" placeholder="0.00" />

                                    <x-input name="margem_lucro" label="Margem de Lucro" placeholder="0.00" />
                                    <x-input name="preco_venda" label="Preço de Venda" placeholder="0.00" />
                                    <x-select name="liberar_desconto" label="Liberar desconto">
                                        <option value="">Selecione...</option>
                                        <option value="1">Sim</option>
                                        <option value="0">Não</option>
                                    </x-select>
                                </div>
                                <!-- Endereço -->
                                <div class="space-y-4">
                                    <hr />
                                    <h3 class="text-lg font-medium flex items-center gap-2">
                                        <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                        Observações
                                    </h3>
                                    <div class="space-y-6">
                                        <x-textarea name="descricao" label="Descrição"
                                            placeholder="Escreva as características e detalhes do produto..."
                                            rows="3" />
                                        <x-textarea name="observacoes" label="Observações"
                                            placeholder="Observações gerais, condições especiais, informações adicionais..."
                                            rows="3" />

                                        <div>
                                            <label class="text-sm font-medium">Imagem do Produto</label>
                                            <input type="file" name="imagem"
                                                accept="image/png,image/jpeg,image/gif"
                                                class="mt-2 block w-full text-sm text-gray-600 border rounded-lg p-2">
                                            <p class="text-xs text-gray-500 mt-1">PNG, JPG ou GIF até 5MB</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4"><br />
                                        <hr />
                                        <h3 class="text-lg font-medium flex items-center gap-2">
                                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                                            Informações Fiscais - Saída
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                            <x-select name="tipo_sped" label="Tipo Produto SPED">
                                                <option value="">Selecione...</option>
                                                <option value="00">00 - Mercadoria para Revenda</option>
                                                <option value="01">01 - Matéria-Prima</option>
                                                <option value="02">02 - Embalagem</option>
                                                <option value="03">03 - Produto em Processo</option>
                                                <option value="04">04 - Produto Acabado</option>
                                                <option value="05">05 - Subproduto</option>
                                                <option value="06">06 - Produto Intermediário</option>
                                                <option value="07">07 - Material de Uso e Consumo</option>
                                                <option value="08">08 - Ativo Imobilizado</option>
                                                <option value="09">09 - Serviços</option>
                                                <option value="10">10 - Outros Insumos</option>
                                                <option value="99">99 - Outras</option>
                                            </x-select>
                                            <x-input name="ncm"
                                                label="NCM - Nomenclatura Comum do Mercosul (8 dígitos)"
                                                placeholder="12345678" required />
                                            <x-input type="number" step="0.01" name="substituicao_tributaria"
                                                label="% Substituição Tributária" />
                                            <x-input type="number" step="0.01" name="icms" label="% ICMS" />
                                            <x-input type="number" step="0.01" name="pis" label="PIS" />
                                            <x-input type="number" step="0.01" name="cofins" label="Cofins" />
                                            <x-input name="classificacao_fiscal" label="CL Fiscal" />
                                        </div>
                                        <br />
                                        <!-- Ações -->
                                        <div class="flex gap-4">
                                             <x-button type="submit" >Cadastrar
                                                Produto</x-button>
                                            <x-button type="reset">Limpar Formulário</x-button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('categoria_id').addEventListener('change', function() {
            let categoriaId = this.value;
            let subSelect = document.getElementById('subcategoria_id');

            if (categoriaId) {
                fetch(`/categorias/${categoriaId}/subcategorias`)
                    .then(response => response.json())
                    .then(data => {
                        subSelect.innerHTML = '<option value="">Selecione...</option>';
                        if (data.length > 0) {
                            data.forEach(sub => {
                                subSelect.innerHTML += `<option value="${sub.id}">${sub.nome}</option>`;
                            });
                            subSelect.style.display = 'block';
                        } else {
                            subSelect.style.display = 'none';
                        }
                    });
            } else {
                subSelect.style.display = 'none';
            }
        });
    </script>
</x-layouts.app>
