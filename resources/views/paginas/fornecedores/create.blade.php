<x-layouts.app :title="__('Cadastrar Fornecedor')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <!-- Card Principal -->
            <div class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-icon name="building-office-2" class="w-5 h-5 text-primary-600" />
                    Cadastro de Fornecedor
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Preencha as informações para cadastrar um novo fornecedor.
                </p>

                <form action="{{ route('fornecedores.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="clipboard" class="w-5 h-5 text-primary-600" />
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="razao_social" label="Razão Social *" placeholder="Digite a razão social" required />
                            <x-input name="nome_fantasia" label="Nome Fantasia" placeholder="Digite o nome fantasia" />
                            <x-input name="tratamento" label="Tratamento" placeholder="Ex: Sr., Sra., Dr., etc." />
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="map-pin" class="w-5 h-5 text-primary-600" />
                            Endereço
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="cep" label="CEP" placeholder="00000-000" />
                            <x-input name="cidade" label="Cidade" placeholder="São Paulo" />
                            <x-input name="estado" label="Estado" placeholder="SP" />
                            <x-input name="bairro" label="Bairro" placeholder="Bairro" />
                            <x-input name="endereco" label="Endereço" placeholder="Rua, número, complemento" />
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="document-text" class="w-5 h-5 text-primary-600" />
                            Documentação
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="cnpj" label="CNPJ" placeholder="00.000.000/0000-00" />
                            <x-input name="inscricao_estadual" label="Inscrição Estadual" placeholder="(opcional)" />
                            <x-input name="inscricao_municipal" label="Inscrição Municipal" placeholder="(opcional)" />
                            <x-input type="date" name="data_abertura" label="Data de Abertura da Empresa" />
                            <x-input name="cnae_principal" label="CNAE Principal" placeholder="Digite o CNAE" />
                            <x-select name="regime_tributario" label="Regime Tributário">
                                <option value="">Selecione</option>
                                <option value="simples">Simples Nacional</option>
                                <option value="lucro_presumido">Lucro Presumido</option>
                                <option value="lucro_real">Lucro Real</option>
                            </x-select>
                        </div>
                    </div>

                    <!-- Contatos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Contatos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="telefone_empresa" label="Telefone Empresa" placeholder="(11) 99999-9999" />
                        </div>

                        <!-- Comercial -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="contato_comercial_nome" label="Contato Comercial (Nome/Função)" />
                            <x-input name="contato_comercial_tel" label="Telefone Contato" placeholder="(11) 99999-9999" />
                            <x-input name="contato_comercial_email" label="E-mail para Pedidos de Compra" placeholder="email@fornecedor.com" />
                        </div>

                        <!-- Financeiro -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="contato_financeiro" label="Contato Financeiro" />
                            <x-input name="telefone_financeiro" label="Telefone Financeiro" />
                            <x-input name="dados_bancarios" label="Dados Bancários (Banco, Agência, Conta, PIX)" />
                        </div>
                    </div>

                    <!-- Benefício / Certidões -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="shield-check" class="w-5 h-5 text-primary-600" />
                            Benefícios e Certificações
                        </h3>
                        <x-input name="beneficio" label="Benefício" placeholder="Ex: MEI, desconto fiscal, etc." />

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <x-input type="file" name="certidoes_negativas" label="Certidões Negativas" />
                            <x-input type="file" name="certificacoes" label="Certificações de Qualidade (ISO, PBQP-H, etc.)" />
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="bell-alert" class="w-5 h-5 text-primary-600" />
                            Status
                        </h3>
                        <x-select name="status" label="Status do Fornecedor">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                            <option value="bloqueado">Bloqueado</option>
                        </x-select>
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit" class="bg-primary-600 text-white">Cadastrar Fornecedor</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
