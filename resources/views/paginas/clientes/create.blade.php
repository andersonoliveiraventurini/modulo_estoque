<x-layouts.app :title="__('Cadastrar cliente')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->

            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-icon name="user" class="w-5 h-5 text-primary-600" />
                    Cadastro de Cliente
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Preencha as informações do cliente para realizar o cadastro.
                </p>

                <form action="{{ route('clientes.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <!-- Informações Pessoais -->
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="cnpj" type="text" name="cnpj" label="CNPJ (opcional)"
                                placeholder="00.000.000/0000-00" />
                            <x-input type="text" name="cpf" label="CPF (opcional)"
                                placeholder="000.000.000-00" />
                            <x-input name="nome" label="Nome" placeholder="Digite o nome completo" required />
                            <x-input id="razao_social" name="razao_social" label="Razão social"
                                placeholder="Digite a razão social" required />
                            <x-input id="nome_fantasia" name="nome_fantasia" label="Nome Fantasia"
                                placeholder="Digite o nome fantasia" required />
                            <x-input type="text" name="tratamento" label="Tratamento *" placeholder="Apelido"
                                required />
                        </div>
                    </div>

                    <!-- Documentação -->
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="vendedor_id" label="Vendedor Responsável">
                                <option value="">Selecione um vendedor</option>
                                @foreach ($vendedores as $v)
                                    <option value="{{ $v->id }}">{{ $v->id }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="vendedor_externo_id" label="Vendedor Externo">
                                <option value="">Selecione um vendedor externo</option>
                                @foreach ($vendedores as $v)
                                    <option value="{{ $v->id }}">{{ $v->id }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <br />
                    <!-- Contatos da Empresa -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
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
                    <br />
                    <!-- Endereço -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Endereço do cliente
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="endereco_cep" name="endereco_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacep(this.value);" onkeypress="mascara(this, '#####-###')" size="10"
                                maxlength="9" value="{{ old('endereco_cep') }}" />
                            <x-input id="endereco_cidade" name="endereco_cidade" label="Cidade" readonly="readonly"
                                placeholder="Cidade" value="{{ old('endereco_cidade') }}" />
                            <x-input id="endereco_estado" name="endereco_estado" label="Estado" placeholder="Estado"
                                readonly="readonly" value="{{ old('endereco_estado') }}" />
                            <x-input id="endereco_bairro" name="endereco_bairro" label="Bairro" placeholder="Bairro"
                                readonly="readonly" value="{{ old('endereco_bairro') }}" />
                            <x-input id="endereco_numero" name="endereco_numero" label="Número" placeholder="N°"
                                value="{{ old('endereco_numero') }}" />
                            <x-input id="endereco_compl" name="endereco_compl" label="Complemento"
                                placeholder="Complemento - Apto, Bloco, etc." value="{{ old('endereco_compl') }}" />
                        </div>
                        <x-input id="endereco_logradouro" name="endereco_logradouro" label="Logradouro"
                            placeholder="Rua, número, complemento" readonly="readonly"
                            value="{{ old('endereco_logradouro') }}" />
                    </div>
                    <br />

                    <!-- Endereço de entrega -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Endereço de entrega
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="entrega_cep" name="entrega_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacepentrega(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9" value="{{ old('entrega_cep') }}" />
                            <x-input id="entrega_cidade" name="entrega_cidade" label="Cidade" readonly="readonly"
                                placeholder="Cidade" value="{{ old('entrega_cidade') }}" />
                            <x-input id="entrega_estado" name="entrega_estado" label="Estado" placeholder="Estado"
                                readonly="readonly" value="{{ old('entrega_estado') }}" />
                            <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro" placeholder="Bairro"
                                readonly="readonly" value="{{ old('entrega_bairro') }}" />
                            <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                value="{{ old('entrega_numero') }}" />
                            <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                placeholder="Complemento - Apto, Bloco, etc." value="{{ old('entrega_compl') }}" />
                        </div>
                        <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                            placeholder="Rua, número, complemento" readonly="readonly"
                            value="{{ old('entrega_logradouro') }}" />
                    </div>
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit" class="bg-primary-600 text-white">Cadastrar Cliente</x-button>
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
                <x-icon name="trash" class="w-5 h-5" />
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
