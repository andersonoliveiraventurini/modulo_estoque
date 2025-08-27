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
                        
                                <x-input id="cnpj" type="text" name="cnpj" label="CNPJ (opcional)" placeholder="00.000.000/0000-00" />
                             <x-input name="nome" label="Nome" placeholder="Digite o nome completo" required />
                            <x-input id="razao_social" name="razao_social" label="Razão social" placeholder="Digite a razão social" required />
                            <x-input id="nome_fantasia" name="nome_fantasia" label="Nome Fantasia" placeholder="Digite o nome fantasia" required />
                            <x-input type="text" name="tratamento" label="Tratamento *" placeholder="Apelido" required />
                            <x-input type="email" name="email" label="E-mail *" placeholder="cliente@email.com" required />
     
                        </div>
                    </div>

                    <!-- Documentação -->
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input type="text" name="cpf" label="CPF (opcional)" placeholder="000.000.000-00"  />
                            <x-select name="vendedor_id" label="Vendedor Responsável" >
                                <option value="">Selecione um vendedor</option>
                                @foreach($vendedores as $v)
                                    <option value="{{ $v->id }}">{{ $v->id }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="vendedor_externo_id" label="Vendedor Externo" >
                                <option value="">Selecione um vendedor externo</option>
                                @foreach($vendedores as $v)
                                    <option value="{{ $v->id }}">{{ $v->id }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <br/>
                    <!-- Contatos da Empresa -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Contatos
                        </h3>

                        <div id="contatos-wrapper" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative">
                                <x-input name="contatos[0][nome]" label="Nome" placeholder="Nome da pessoa" />
                                <x-input name="contatos[0][telefone]" label="Telefone" placeholder="(11) 99999-9999" />
                                <x-input name="contatos[0][email]" label="E-mail" placeholder="contato@empresa.com" />

                            </div>
                        </div>

                    <br/>
                        <x-button type="button" onclick="addContato()">
                            + Adicionar Contato
                        </x-button>
                    </div>
                    <br/>
                    <!-- Endereço -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Endereço do cliente
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="cep" name="cep" label="CEP" placeholder="00000-000" />
                            <x-input id="cidade" name="cidade" label="Cidade" placeholder="São Paulo" />
                            <x-input id="estado" name="estado" label="Estado" placeholder="SP" />
                            <x-input id="bairro" name="bairro" label="Bairro" placeholder="Bairro" />
                            <x-input name="numero" label="Número" placeholder="123" />
                            <x-input name="complemento" label="Complemento" placeholder="Apto, Bloco, etc." />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <x-input id="endereco" name="endereco" label="Endereço Completo" placeholder="Rua, número, complemento" />
                        </div>
                    </div>
                    <br/>

                    <!-- Endereço de entrega -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-icon name="users" class="w-5 h-5 text-primary-600" />
                            Endereço de entrega
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input name="cep_entrega" label="CEP" placeholder="00000-000" />
                            <x-input name="cidade_entrega" label="Cidade" placeholder="São Paulo" />
                            <x-input name="estado_entrega" label="Estado" placeholder="SP" />
                            <x-input name="bairro_entrega" label="Bairro" placeholder="Bairro" />
                            <x-input name="numero_entrega" label="Número" placeholder="123" />
                            <x-input name="complemento_entrega" label="Complemento" placeholder="Apto, Bloco, etc." />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            <x-input name="endereco_entrega" label="Endereço Completo" placeholder="Rua, número, complemento" />
                        </div>
                    </div>
                    <br/>
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
                    <br/>
        `;
            wrapper.appendChild(div);
            contatoIndex++;
        }

        function removeContato(button) {
            button.closest('div').remove();
        }

        async function buscarCNPJ() {
            const cnpj = document.getElementById("cnpj").value.replace(/\D/g, "");
            if (cnpj.length !== 14) {
                alert("CNPJ inválido!");
                return;
            }

            try {
                const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
                if (!response.ok) throw new Error("Erro ao consultar CNPJ");

                const data = await response.json();

                // dados principais

        document.querySelector('[name="razao_social"]').value = data.razao_social || "";
        document.querySelector('[name="nome_fantasia"]').value = data.nome_fantasia || "";

                // endereço
                document.getElementById("endereco").value = data.logradouro || "";
                document.getElementById("bairro").value = data.bairro || "";
                document.getElementById("cidade").value = data.municipio || "";
                document.getElementById("estado").value = data.uf || "";
                document.getElementById("cep").value = data.cep || "";

                // Contato
                if (data.ddd_telefone_1) {
                    const telefone = `(${data.ddd_telefone_1.substring(0,2)}) ${data.ddd_telefone_1.substring(2)}`;
                    document.querySelector("[name='contatos[0][telefone]']").value = telefone;
                }
                if (data.email) {
                    document.querySelector("[name='contatos[0][email]']").value = data.email;
                }

                // Opcional: sócios (primeiro da lista)
                if (data.qsa && data.qsa.length > 0) {
                    document.querySelector("[name='contatos[0][nome]']").value = data.qsa[0].nome_socio || "";
                }

            } catch (err) {
                alert("Erro: " + err.message);
            }
        }

        // --- AUTOMATIZAÇÃO ---
        // roda a função automaticamente ao sair do campo CNPJ
        document.addEventListener("DOMContentLoaded", () => {
            document.getElementById("cnpj").addEventListener("blur", buscarCNPJ);
        });

    </script>
</x-layouts.app>
