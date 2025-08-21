<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                <!-- Informações Básicas -->
                <div
                    class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <h1 class="text-2xl font-bold">Cadastro de Produto</h1>
                    <p class="text-gray-600">Preencha as informações abaixo para cadastrar um novo produto no sistema.
                    </p>
                    <br />
                    <h2 class="text-lg font-semibold mb-6">Informações Básicas</h2>

                    <x-input name="nome" label="Nome do Produto *" placeholder="Digite o nome" required
                        class="col-span-2" />
                    <br>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input name="codigo_barras" label="Código de Barras" placeholder="7891234567890" />
                        <x-input name="sku" label="SKU/Código" placeholder="PRD123456" />
                        <x-select name="fornecedor_id" label="Fornecedor">
                            <option value="">Selecione...</option>
                        </x-select>
                        <x-input name="marca" label="Marca" placeholder="Digite a marca" />
                        <x-input name="cor" label="Cor" placeholder="Digite a cor" />

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

                        <x-input name="modelo" label="Modelo" placeholder="Digite o modelo" />
                        <x-input type="number" step="0.01" name="peso" label="Peso" placeholder="0.000" />
                    </div>
                </div>

                <!-- Informações Fiscais -->
                <div
                    class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-lg font-semibold mb-6">Informações Fiscais</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        <x-input name="ncm" label="NCM - Nomenclatura Comum do Mercosul (8 dígitos)" placeholder="12345678" required />
                    </div>
                </div>

                <!-- Informações Fiscais -->
                <div
                    class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-lg font-semibold mb-6">Preços e Estoque</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input name="preco_custo" label="Preço de Custo" placeholder="0.00" />
                        <x-input name="preco_venda" label="Preço de Venda" placeholder="0.00" />
                        <x-input name="estoque_minimo" label="Estoque Mínimo" placeholder="0.00" />
                        <x-input name="estoque_atual" label="Estoque Atual" placeholder="0.00" />
                    </div>
                </div>

                <!-- Descrições e Status -->
                <div
                    class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-lg font-semibold mb-6">Descrições e Status</h2>

                    <div class="space-y-6">
                        <x-textarea name="descricao" label="Descrição"
                            placeholder="Escreva as características e detalhes do produto..." rows="3" />
                        <x-textarea name="observacoes" label="Observações"
                            placeholder="Observações gerais, condições especiais, informações adicionais..."
                            rows="3" />

                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Status do Produto</span>
                            <x-button name="status" label="Ativo" checked />
                        </div>

                        <div>
                            <label class="text-sm font-medium">Imagem do Produto</label>
                            <input type="file" name="imagem" accept="image/png,image/jpeg,image/gif"
                                class="mt-2 block w-full text-sm text-gray-600 border rounded-lg p-2">
                            <p class="text-xs text-gray-500 mt-1">PNG, JPG ou GIF até 5MB</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-4">
                        <x-button type="reset" variant="outline">Limpar Formulário</x-button>
                        <x-button type="submit" style="">Cadastrar Produto</x-button>
                    </div>
                </div>
                <!-- Botões -->
            </form>
        </div>
    </div>
</x-layouts.app>
