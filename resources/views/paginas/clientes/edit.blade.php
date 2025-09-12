<x-layouts.app :title="__('Editar cliente')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Editar Cliente
                </h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                    Edite as informações do cliente conforme necessário.
                </p>

                <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Informações Pessoais -->
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="cnpj" type="text" name="cnpj" label="CNPJ"
                                placeholder="00.000.000/0000-00" onblur="buscarCNPJ(this.value);"
                                value="{{ old('cnpj', $cliente->cnpj) }}" />
                            <x-input id="razao_social" name="razao_social" label="Razão social"
                                placeholder="Digite a razão social" required
                                value="{{ old('razao_social', $cliente->razao_social) }}" />
                            <x-input id="nome_fantasia" name="nome_fantasia" label="Nome Fantasia"
                                placeholder="Digite o nome fantasia" required
                                value="{{ old('nome_fantasia', $cliente->nome_fantasia) }}" />
                            <x-input type="text" name="tratamento" label="Tratamento *" placeholder="Apelido"
                                required value="{{ old('tratamento', $cliente->tratamento) }}" />
                            <x-input name="inscricao_estadual" label="Inscrição Estadual"
                                value="{{ old('inscricao_estadual', $cliente->inscricao_estadual) }}" />
                            <x-input name="inscricao_municipal" label="Inscrição Municipal"
                                value="{{ old('inscricao_municipal', $cliente->inscricao_municipal) }}" />
                            <x-show-field label="Data de Abertura" :value="$cliente->data_abertura_formatada" />
                            <x-input name="cnae" label="CNAE Principal" value="{{ old('cnae', $cliente->cnae) }}" />
                            <x-select name="regime_tributario" label="Regime Tributário">
                                <option value="">Selecione</option>
                                <option value="simples"
                                    {{ old('regime_tributario', $cliente->regime_tributario) == 'simples' ? 'selected' : '' }}>
                                    Simples Nacional</option>
                                <option value="lucro_presumido"
                                    {{ old('regime_tributario', $cliente->regime_tributario) == 'lucro_presumido' ? 'selected' : '' }}>
                                    Lucro Presumido</option>
                                <option value="lucro_real"
                                    {{ old('regime_tributario', $cliente->regime_tributario) == 'lucro_real' ? 'selected' : '' }}>
                                    Lucro Real</option>
                            </x-select>
                        </div>
                    </div>

                    <!-- Responsável e Documentos -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium">Responsável Legal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="cpf_responsavel" label="CPF do Responsável"
                                value="{{ old('cpf_responsavel', $cliente->cpf_responsavel) }}" />
                            <x-input name="nome" label="Nome" placeholder="Digite o nome completo" required
                                value="{{ old('nome', $cliente->nome_responsavel) }}" />
                            <x-input type="file" name="certidoes_negativas" label="Certidões Negativas" />
                            <x-input name="suframa" label="Inscrição SUFRAMA (se aplicável)"
                                value="{{ old('suframa', $cliente->suframa) }}" />
                        </div>
                    </div>

                    <!-- Classificação -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium">Classificação</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-select name="classificacao" label="Classificação">
                                <option value="">Selecione</option>
                                <option value="Distribuidor"
                                    {{ old('classificacao', $cliente->classificacao) == 'Distribuidor' ? 'selected' : '' }}>
                                    Distribuidor</option>
                                <option value="Revenda"
                                    {{ old('classificacao', $cliente->classificacao) == 'Revenda' ? 'selected' : '' }}>
                                    Revenda</option>
                                <option value="Construtora"
                                    {{ old('classificacao', $cliente->classificacao) == 'Construtora' ? 'selected' : '' }}>
                                    Construtora</option>
                                <option value="Consumidor Final"
                                    {{ old('classificacao', $cliente->classificacao) == 'Consumidor Final' ? 'selected' : '' }}>
                                    Consumidor Final</option>
                            </x-select>
                            <x-select name="canal_origem" label="Canal de Origem">
                                <option value="">Selecione</option>
                                <option value="Indicação"
                                    {{ old('canal_origem', $cliente->canal_origem) == 'Indicação' ? 'selected' : '' }}>
                                    Indicação</option>
                                <option value="Marketing"
                                    {{ old('canal_origem', $cliente->canal_origem) == 'Marketing' ? 'selected' : '' }}>
                                    Marketing</option>
                                <option value="Visita"
                                    {{ old('canal_origem', $cliente->canal_origem) == 'Visita' ? 'selected' : '' }}>
                                    Visita</option>
                            </x-select>
                        </div>
                    </div>

                    <!-- Informações de Crédito -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium">Informações de Crédito</h3>
                        <!-- Documentação -->
                        <div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <x-select name="vendedor_id" label="Vendedor Responsável">
                                    <option value="">Selecione um vendedor</option>
                                    @foreach ($vendedores as $v)
                                        <option value="{{ $v->id }}"
                                            {{ old('vendedor_id', $cliente->vendedor_id) == $v->id ? 'selected' : '' }}>
                                            {{ $v->id }}</option>
                                    @endforeach
                                </x-select>
                                <x-select name="vendedor_externo_id" label="Vendedor Externo">
                                    <option value="">Selecione um vendedor externo</option>
                                    @foreach ($vendedores as $v)
                                        <option value="{{ $v->id }}"
                                            {{ old('vendedor_externo_id', $cliente->vendedor_externo_id) == $v->id ? 'selected' : '' }}>
                                            {{ $v->id }}</option>
                                    @endforeach
                                </x-select>
                                <x-input name="desconto" label="Desconto (%)" type="number" step="0.01"
                                    value="{{ old('desconto', $cliente->desconto) }}" />
                                <x-select name="bloqueado" label="Bloqueado">
                                    <option value="">Selecione</option>
                                    <option value="0"
                                        {{ old('bloqueado', $cliente->bloqueado) == '0' ? 'selected' : '' }}>Não
                                    </option>
                                    <option value="1"
                                        {{ old('bloqueado', $cliente->bloqueado) == '1' ? 'selected' : '' }}>Sim
                                    </option>
                                </x-select>
                                <x-select name="negociar_titulos" label="Aceitar negociar títulos">
                                    <option value="">Selecione</option>
                                    <option value="0"
                                        {{ old('negociar_titulos', $cliente->negociar_titulos) == '0' ? 'selected' : '' }}>
                                        Não</option>
                                    <option value="1"
                                        {{ old('negociar_titulos', $cliente->negociar_titulos) == '1' ? 'selected' : '' }}>
                                        Sim</option>
                                </x-select>
                                <x-input type="number" step="0.01" name="limite_boleto"
                                    label="Limite Boleto (R$)"
                                    value="{{ old('limite_boleto', $cliente->limite_boleto) }}" />
                                <x-input type="number" step="0.01" name="limite_carteira"
                                    label="Limite Carteira (R$)"
                                    value="{{ old('limite_carteira', $cliente->limite_carteira) }}" />
                                <x-input type="number" name="inativar_apos"
                                    label="Inativar após (meses sem comprar)"
                                    value="{{ old('inativar_apos', $cliente->inativar_apos) }}" />
                            </div>
                        </div>
                    </div>

                    <!-- Contatos da Empresa -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Contatos
                        </h3>

                        <div id="contatos-wrapper" class="space-y-4">
                            @if ($cliente->contatos && count($cliente->contatos) > 0)
                                @foreach ($cliente->contatos as $index => $contato)
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative">
                                        <x-input name="contatos[{{ $index }}][nome]" label="Nome"
                                            placeholder="Nome da pessoa"
                                            value="{{ old('contatos.' . $index . '.nome', $contato->nome) }}" />
                                        <x-input name="contatos[{{ $index }}][telefone]" label="Telefone"
                                            placeholder="(11) 99999-9999"
                                            value="{{ old('contatos.' . $index . '.telefone', $contato->telefone) }}" />
                                        <x-input name="contatos[{{ $index }}][email]" label="E-mail"
                                            placeholder="contato@empresa.com"
                                            value="{{ old('contatos.' . $index . '.email', $contato->email) }}" />

                                        <!-- Botão de excluir -->
                                        <button type="button" onclick="removeContato(this)"
                                            class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                @endforeach
                                @php $contatoIndex = count($cliente->contatos); @endphp
                            @else
                                <div
                                    class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative">
                                    <x-input name="contatos[0][nome]" label="Nome" placeholder="Nome da pessoa" />
                                    <x-input name="contatos[0][telefone]" label="Telefone"
                                        placeholder="(11) 99999-9999" />
                                    <x-input name="contatos[0][email]" label="E-mail"
                                        placeholder="contato@empresa.com" />
                                </div>
                                @php $contatoIndex = 1; @endphp
                            @endif
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
                            Endereço do cliente
                        </h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                            Preencha o CEP primeiro e aguarde os dados serem preenchidos automaticamente.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="endereco_cep" name="endereco_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacep(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9"
                                value="{{ old('endereco_cep', $cliente->endereco_cep) }}" />
                            <x-input id="endereco_cidade" name="endereco_cidade" label="Cidade" readonly="readonly"
                                placeholder="Cidade"
                                value="{{ old('endereco_cidade', $cliente->endereco_cidade) }}" />
                            <x-input id="endereco_estado" name="endereco_estado" label="Estado" placeholder="Estado"
                                readonly="readonly"
                                value="{{ old('endereco_estado', $cliente->endereco_estado) }}" />
                            <x-input id="endereco_bairro" name="endereco_bairro" label="Bairro" placeholder="Bairro"
                                readonly="readonly"
                                value="{{ old('endereco_bairro', $cliente->endereco_bairro) }}" />
                            <x-input id="endereco_numero" name="endereco_numero" label="Número" placeholder="N°"
                                value="{{ old('endereco_numero', $cliente->endereco_numero) }}" />
                            <x-input id="endereco_compl" name="endereco_compl" label="Complemento"
                                placeholder="Complemento - Apto, Bloco, etc."
                                value="{{ old('endereco_compl', $cliente->endereco_compl) }}" />
                        </div>
                        <x-input id="endereco_logradouro" name="endereco_logradouro" label="Logradouro"
                            placeholder="Rua, número, complemento" readonly="readonly"
                            value="{{ old('endereco_logradouro', $cliente->endereco_logradouro) }}" />
                    </div>

                    <!-- Endereço de entrega -->
                    <div class="space-y-4"><br />
                        <hr />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Endereço de entrega
                        </h3>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6">
                            Preencha o CEP primeiro e aguarde os dados serem preenchidos automaticamente.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="entrega_cep" name="entrega_cep" label="CEP" placeholder="00000-000"
                                onblur="pesquisacepentrega(this.value);" onkeypress="mascara(this, '#####-###')"
                                size="10" maxlength="9"
                                value="{{ old('entrega_cep', $cliente->entrega_cep) }}" />
                            <x-input id="entrega_cidade" name="entrega_cidade" label="Cidade" readonly="readonly"
                                placeholder="Cidade" value="{{ old('entrega_cidade', $cliente->entrega_cidade) }}" />
                            <x-input id="entrega_estado" name="entrega_estado" label="Estado" placeholder="Estado"
                                readonly="readonly" value="{{ old('entrega_estado', $cliente->entrega_estado) }}" />
                            <x-input id="entrega_bairro" name="entrega_bairro" label="Bairro" placeholder="Bairro"
                                readonly="readonly" value="{{ old('entrega_bairro', $cliente->entrega_bairro) }}" />
                            <x-input id="entrega_numero" name="entrega_numero" label="Número" placeholder="N°"
                                value="{{ old('entrega_numero', $cliente->entrega_numero) }}" />
                            <x-input id="entrega_compl" name="entrega_compl" label="Complemento"
                                placeholder="Complemento - Apto, Bloco, etc."
                                value="{{ old('entrega_compl', $cliente->entrega_compl) }}" />
                        </div>
                        <x-input id="entrega_logradouro" name="entrega_logradouro" label="Logradouro"
                            placeholder="Rua, número, complemento" readonly="readonly"
                            value="{{ old('entrega_logradouro', $cliente->entrega_logradouro) }}" />
                    </div>
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                         <x-button type="submit" >Atualizar Cliente</x-button>
                        <x-button type="reset">Limpar Alterações</x-button>
                    </div>
                    <!-- Botões -->
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/valida.js') }}"></script>

    <script>
        let contatoIndex = {{ $contatoIndex ?? 1 }};

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
