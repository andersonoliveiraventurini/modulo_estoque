<x-layouts.app :title="__('Cadastrar Fornecedor')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-truck class="w-5 h-5 text-primary-600" />
                    Cadastro de Fornecedor
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Preencha o CNPJ e aguarde as informações serem preenchidas automaticamente.
                </p>

                <form action="{{ route('fornecedores.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf

                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-clipboard class="w-5 h-5 text-primary-600" />
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="cnpj" type="text" name="cnpj" label="CNPJ"
                                placeholder="00.000.000/0000-00" onblur="buscarCNPJfornecedor(this.value);" />
                            <x-input name="inscricao_estadual" label="Inscrição Estadual" placeholder="(opcional)" />
                            <x-input name="inscricao_municipal" label="Inscrição Municipal" placeholder="(opcional)" />
                            <x-input name="razao_social" label="Razão Social *" placeholder="Digite a razão social" />
                            <x-input name="nome_fantasia" label="Nome Fantasia" placeholder="Digite o nome fantasia" />
                            <x-input name="tratamento" label="Tratamento" placeholder="Ex: Sr., Sra., Dr., etc." />
                            <x-input type="date" name="data_abertura" label="Data de Abertura da Empresa" />
                            <x-input name="cnae_principal" label="CNAE Principal" placeholder="Digite o CNAE" />
                            <x-select name="regime_tributario" label="Regime Tributário">
                                <option value="">Selecione</option>
                                <option value="simples">Simples Nacional</option>
                                <option value="lucro_presumido">Lucro Presumido</option>
                                <option value="lucro_real">Lucro Real</option>
                            </x-select> <x-input name="beneficio" label="Benefício"
                                placeholder="Ex: MEI, desconto fiscal, etc." />
                            <x-input type="file" name="certidoes_negativas" label="Certidões Negativas" />
                            <x-input type="file" name="certificacoes_qualidade"
                                label="Certificações de Qualidade (ISO, PBQP-H, etc.)" />
                            <x-select name="status" label="Status do Fornecedor">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="bloqueado">Bloqueado</option>
                            </x-select>
                        </div>
                    </div>


                    <!-- Contatos da Empresa -->
                    <div class="space-y-4"><br />
                        <hr /><br />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Contatos
                        </h3>

                        <div id="contatos-wrapper" class="space-y-4">
                            <div
                                class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative">
                                <x-input name="contatos[0][nome]" label="Nome" placeholder="Nome da pessoa" />
                                <x-input name="contatos[0][telefone]" label="Telefone" placeholder="(11) 99999-9999" />
                                <x-input name="contatos[0][email]" label="E-mail" placeholder="contato@empresa.com" />
                            </div>
                        </div>

                        <x-button type="button" onclick="addContato()">
                            + Adicionar Contato
                        </x-button>
                    </div>
                    <!-- Endereço -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Endereço do fornecedor
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="endereco_cep" name="endereco_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacep(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9" value="{{ old('endereco_cep') }}" required />
                        </div>

                        <!-- Wrapper que será ocultado até o CEP ser válido -->
                        <div id="endereco-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input id="endereco_cidade" name="endereco_cidade" label="Cidade"
                                    readonly="readonly" placeholder="Cidade" value="{{ old('endereco_cidade') }}" />

                                <x-input id="endereco_estado" name="endereco_estado" label="Estado"
                                    placeholder="Estado" readonly="readonly" value="{{ old('endereco_estado') }}" />

                                <x-input id="endereco_bairro" name="endereco_bairro" label="Bairro"
                                    placeholder="Bairro" readonly="readonly" value="{{ old('endereco_bairro') }}" />

                                <x-input id="endereco_logradouro" name="endereco_logradouro" label="Logradouro"
                                    placeholder="Rua, número, complemento" readonly="readonly"
                                    value="{{ old('endereco_logradouro') }}" />

                                <x-input id="endereco_numero" name="endereco_numero" label="Número" placeholder="N°"
                                    value="{{ old('endereco_numero') }}" />

                                <x-input id="endereco_compl" name="endereco_compl" label="Complemento"
                                    placeholder="Complemento - Apto, Bloco, etc."
                                    value="{{ old('endereco_compl') }}" />
                            </div>
                        </div>
                    </div>
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                         <x-button type="submit" >Cadastrar Fornecedor</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/valida.js') }}"></script>

    <script>
        let contatoIndex = 1;

        function addContato() {
            const wrapper = document.getElementById('contatos-wrapper');
            const div = document.createElement('div');
            div.classList = "grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative";
            div.innerHTML = `
            <x-input name="contatos[\${contatoIndex}][nome]" label="Nome" placeholder="Nome da pessoa" />
            <x-input name="contatos[\${contatoIndex}][telefone]" label="Telefone" placeholder="(11) 99999-9999" />
            <x-input name="contatos[\${contatoIndex}][email]" label="E-mail" placeholder="contato@empresa.com" />

            <!-- Botão de excluir -->
            <button type="button" onclick="removeContato(this)"
                class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                <x-heroicon-o-trash class="w-5 h-5" />
            </button>`;
            wrapper.appendChild(div);
            contatoIndex++;
        }

        function removeContato(button) {
            button.closest('div').remove();
        }
    </script>
</x-layouts.app>
