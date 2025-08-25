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
                            <x-input name="nome" label="Nome *" placeholder="Digite o nome completo" required />
                            <x-input type="email" name="email" label="E-mail *" placeholder="cliente@email.com" required />
                            <x-input type="text" name="cnpj" label="CNPJ *" placeholder="00.000.000/0000-00" required />
                        </div>
                    </div>
                    
                    <!-- Documentação -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <x-input type="text" name="cpf" label="CPF (opcional)" placeholder="000.000.000-00"  />
                    </div>

                    <!-- Endereço -->
                    <div>
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="cep" label="CEP" placeholder="00000-000" />
                            <x-input name="cidade" label="Cidade" placeholder="São Paulo" />
                            <x-input name="estado" label="Estado" placeholder="SP" />
                        </div>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                        <x-input name="endereco" label="Endereço Completo" placeholder="Rua, número, complemento" />
                            </div>
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="numero" label="Número" placeholder="123" />
                            <x-input name="bairro" label="Bairro" placeholder="Bairro" />
                            <x-input name="complemento" label="Complemento" placeholder="Apto, Bloco, etc." />
                            
                        </div>
                    </div>  

                    <!-- Contatos da Empresa -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Contatos da Empresa
                        </h3>

                        <div id="contatos-wrapper" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700">
                                <x-input name="contatos[0][nome]" label="Nome" placeholder="Nome da pessoa" />
                                <x-input name="contatos[0][telefone]" label="Telefone" placeholder="(11) 99999-9999" />
                                <x-input name="contatos[0][email]" label="E-mail" placeholder="contato@empresa.com" />
                            </div>
                        </div>

                        <x-button type="button"  onclick="addContato()">
                            + Adicionar Contato
                        </x-button>
                    </div>

                    <!-- Saldo -->
                    <div>
                        <x-input type="number" step="0.01" name="saldo" label="Saldo Disponível para Compras" placeholder="R$ 0,00" />
                    </div>

                    <!-- Observações -->
                    <div>
                        <x-textarea name="observacoes" label="Observações (opcional)" placeholder="Informações adicionais sobre o cliente..." />
                    </div>

                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit" class="bg-primary-600 text-white">Cadastrar Cliente</x-button>
                        <x-button type="reset" >Limpar Formulário</x-button>
                    </div>
                        <!-- Botões -->
                    </form>
                </div>
            </div>
        </div>
            <script>
            let contatoIndex = 1;
            function addContato() {
                const wrapper = document.getElementById('contatos-wrapper');
                const div = document.createElement('div');
                div.classList = "grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700";
                div.innerHTML = `
                    <x-input name="contatos[\${contatoIndex}][nome]" label="Nome" placeholder="Nome da pessoa" />
                    <x-input name="contatos[\${contatoIndex}][telefone]" label="Telefone" placeholder="(11) 99999-9999" />
                    <x-input name="contatos[\${contatoIndex}][email]" label="E-mail" placeholder="contato@empresa.com" />
                `;
                wrapper.appendChild(div);
                contatoIndex++;
            }
        </script>
</x-layouts.app>
