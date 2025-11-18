<x-layouts.app :title="__('Pré-cadastro de cliente')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Pré-cadastro de cliente
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Preencha o CNPJ primeiro e aguarde os dados serem preenchidos automaticamente. Se for pessoa física,
                    preencha o CPF.
                </p>
                <form action="{{ route('clientes.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <!-- Informações Pessoais -->
                    <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                        <x-heroicon-o-building-office-2 class="w-5 h-5 text-primary-600" />
                        Para empresa
                    </h2>
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <input type="hidden" name="pre_cadastro" value="1">
                            <x-input id="cnpj" type="text" name="cnpj" label="CNPJ"
                                placeholder="00.000.000/0000-00" onblur="buscarCNPJ_precadastro(this.value);" />
                            <x-input id="razao_social" name="razao_social" label="Razão social"
                                placeholder="Digite a razão social" />
                            <x-input id="nome_fantasia" name="nome_fantasia" label="Nome Fantasia"
                                placeholder="Digite o nome fantasia" />
                            <x-input type="text" name="tratamento" label="Tratamento *" placeholder="Apelido" />
                        </div><br />
                        <hr />
                        <br />
                        <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                            <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                            Para pessoa física
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input type="text" name="cpf" label="CPF" placeholder="000.000.000-00" />
                            <x-input name="nome" label="Nome" placeholder="Digite o nome completo" />
                            <x-input type="date" name="data_nascimento" label="Data de Nascimento" />
                        </div>
                    </div>
                    <hr />
                    <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                        <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                        Vendedor
                    </h2>
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="vendedor_id" label="Vendedor Responsável">
                                <option value="">Selecione um vendedor</option>
                                @foreach ($vendedores as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="vendedor_externo_id" label="Vendedor Externo">
                                <option value="">Selecione um vendedor externo</option>
                                @foreach ($vendedores_externos as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <!-- Contatos da Empresa -->
                    <div class="space-y-4">
                        <hr />
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
                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Endereço do cliente
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="endereco_cep" name="endereco_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacep(this.value);" onkeypress="mascara(this, '#####-###')" size="10"
                                maxlength="9" value="{{ old('endereco_cep') }}" required />
                        </div>

                        <!-- Wrapper que será ocultado até o CEP ser válido -->
                        <div id="endereco-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input id="endereco_cidade" name="endereco_cidade" label="Cidade" readonly="readonly"
                                    placeholder="Cidade" value="{{ old('endereco_cidade') }}" />

                                <x-input id="endereco_estado" name="endereco_estado" label="Estado" placeholder="Estado"
                                    readonly="readonly" value="{{ old('endereco_estado') }}" />

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
                    <!-- Endereço de entrega -->
                    <div class="space-y-4">
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Endereço de entrega
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input id="entrega_cep" name="entrega_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacepentrega(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9" value="{{ old('entrega_cep') }}" />
                        </div>

                        <!-- Wrapper que será ocultado até o CEP ser válido -->
                        <div id="endereco-entrega-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-input id="entrega_cidade" name="entrega_cidade" label="Cidade"
                                    readonly="readonly" placeholder="Cidade" value="{{ old('entrega_cidade') }}" />
                                <x-input id="entrega_estado" name="entrega_estado" label="Estado"
                                    placeholder="Estado" readonly="readonly" value="{{ old('entrega_estado') }}" />
                                <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro"
                                    placeholder="Bairro" readonly="readonly" value="{{ old('entrega_bairro') }}" />
                                <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                                    placeholder="Rua, número, complemento" readonly="readonly"
                                    value="{{ old('entrega_logradouro') }}" />
                                <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                    value="{{ old('entrega_numero') }}" />
                                <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                    placeholder="Complemento - Apto, Bloco, etc."
                                    value="{{ old('entrega_compl') }}" />
                            </div>
                        </div>
                    </div>
                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit">Cadastrar Cliente</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>
                    <!-- Botões -->
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
            </button>
        `;
            wrapper.appendChild(div);
            contatoIndex++;
        }

        function removeContato(button) {
            button.closest('div').remove();
        }
    </script>
</x-layouts.app>
