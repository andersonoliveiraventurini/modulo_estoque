<x-layouts.app :title="__('Editar Fornecedor')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <!-- Card Principal -->
            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">

                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-truck class="w-5 h-5 text-primary-600" />
                    Editar Fornecedor
                </h2>

                <form action="/fornecedores/{{ $fornecedor->id }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="fornecedor_id" value="{{ $fornecedor->id }}" />
                    <!-- Dados Básicos -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-clipboard class="w-5 h-5 text-primary-600" />
                            Dados Básicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-input id="cnpj" type="text" name="cnpj" label="CNPJ"
                                value="{{ old('cnpj', $fornecedor->cnpj) }}" readonly />
                            <x-input name="inscricao_estadual" label="Inscrição Estadual"
                                value="{{ old('inscricao_estadual', $fornecedor->inscricao_estadual) }}" />
                            <x-input name="inscricao_municipal" label="Inscrição Municipal"
                                value="{{ old('inscricao_municipal', $fornecedor->inscricao_municipal) }}" />
                            <x-input name="razao_social" label="Razão Social *"
                                value="{{ old('razao_social', $fornecedor->razao_social) }}" />
                            <x-input name="nome_fantasia" label="Nome Fantasia"
                                value="{{ old('nome_fantasia', $fornecedor->nome_fantasia) }}" />
                            <x-input name="tratamento" label="Tratamento"
                                value="{{ old('tratamento', $fornecedor->tratamento) }}" />
                            <x-input type="date" name="data_abertura" label="Data de Abertura da Empresa"
                                value="{{ old('data_abertura', $fornecedor->data_abertura?->format('Y-m-d')) }}" />
                            <x-input name="cnae_principal" label="CNAE Principal"
                                value="{{ old('cnae_principal', $fornecedor->cnae_principal) }}" />
                            <x-select name="regime_tributario" label="Regime Tributário">
                                <option value="">Selecione</option>
                                <option value="simples" @selected(old('regime_tributario', $fornecedor->regime_tributario) == 'simples')>Simples Nacional</option>
                                <option value="lucro_presumido" @selected(old('regime_tributario', $fornecedor->regime_tributario) == 'lucro_presumido')>Lucro Presumido</option>
                                <option value="lucro_real" @selected(old('regime_tributario', $fornecedor->regime_tributario) == 'lucro_real')>Lucro Real</option>
                            </x-select>
                            <x-input name="beneficio" label="Benefício"
                                value="{{ old('beneficio', $fornecedor->beneficio) }}" />

                            <!-- Certidões -->
                            <div class="col-span-3">
                                <label class="block text-sm font-medium mb-1">Certidões Negativas</label>
                                @if ($fornecedor->certidoes_negativas)
                                    <p class="mb-2">
                                        <a href="{{ asset('storage/' . $fornecedor->certidoes_negativas) }}"
                                            target="_blank" class="text-blue-600 hover:underline">Ver arquivo atual</a>
                                    </p>
                                @endif
                                <input type="file" name="certidoes_negativas"
                                    class="block w-full border rounded-lg px-3 py-2" />
                            </div>

                            <!-- Certificações -->
                            <div class="col-span-3">
                                <label class="block text-sm font-medium mb-1">Certificações de Qualidade</label>
                                @if ($fornecedor->certificacoes_qualidade)
                                    <p class="mb-2">
                                        <a href="{{ asset('storage/' . $fornecedor->certificacoes_qualidade) }}"
                                            target="_blank" class="text-blue-600 hover:underline">Ver arquivo atual</a>
                                    </p>
                                @endif
                                <input type="file" name="certificacoes_qualidade"
                                    class="block w-full border rounded-lg px-3 py-2" />
                            </div>

                            <x-select name="status" label="Status do Fornecedor">
                                <option value="ativo" @selected(old('status', $fornecedor->status) == 'ativo')>Ativo</option>
                                <option value="inativo" @selected(old('status', $fornecedor->status) == 'inativo')>Inativo</option>
                                <option value="bloqueado" @selected(old('status', $fornecedor->status) == 'bloqueado')>Bloqueado</option>
                            </x-select>
                        </div>
                    </div>

                    <!-- Contatos -->
                    <div class="space-y-4"><br />
                        <hr /><br />
                        <h3 class="text-lg font-medium flex items-center gap-2">
                            <x-heroicon-o-users class="w-5 h-5 text-primary-600" />
                            Contatos
                        </h3>

                        <div id="contatos-wrapper" class="space-y-4">
                            @forelse($fornecedor->contatos as $i => $contato)
                                <div
                                    class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative">
                                    <input type="hidden" name="contatos[{{ $i }}][id]"
                                        value="{{ $contato->id }}" />
                                    <x-input name="contatos[{{ $i }}][nome]" label="Nome"
                                        value="{{ old('contatos.' . $i . '.nome', $contato->nome) }}" />

                                    <x-input name="contatos[{{ $i }}][telefone]" label="Telefone"
                                        value="{{ old('contatos.' . $i . '.telefone', $contato->telefone) }}" />

                                    <x-input name="contatos[{{ $i }}][email]" label="E-mail"
                                        value="{{ old('contatos.' . $i . '.email', $contato->email) }}" />

                                    <button type="button" onclick="removeContato(this)"
                                        class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            @empty
                                <div
                                    class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative">
                                    <x-input name="contatos[0][nome]" label="Nome" placeholder="Nome da pessoa" />
                                    <x-input name="contatos[0][telefone]" label="Telefone"
                                        placeholder="(11) 99999-9999" />
                                    <x-input name="contatos[0][email]" label="E-mail"
                                        placeholder="contato@empresa.com" />
                                </div>
                            @endforelse
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
                            <x-input id="endereco_cep" name="endereco_cep" label="CEP"
                                value="{{ old('endereco_cep', $fornecedor->endereco?->cep) }}"
                                onblur="pesquisacep(this.value);" placeholder="00000-000"
                                onkeypress="mascara(this, '#####-###')" size="10" maxlength="9" />
                        </div>

                        <div id="endereco-wrapper">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <input type="hidden" id="endereco_id" name="endereco_id"
                                    value="{{ $fornecedor->endereco?->id }}" />
                                <x-input id="endereco_cidade" name="endereco_cidade" label="Cidade" readonly
                                    value="{{ old('endereco_cidade', $fornecedor->endereco?->cidade) }}" />
                                <x-input id="endereco_estado" name="endereco_estado" label="Estado" readonly
                                    value="{{ old('endereco_estado', $fornecedor->endereco?->estado) }}" />
                                <x-input id="endereco_bairro" name="endereco_bairro" label="Bairro" readonly
                                    value="{{ old('endereco_bairro', $fornecedor->endereco?->bairro) }}" />
                                <x-input id="endereco_logradouro" name="endereco_logradouro" label="Logradouro"
                                    readonly
                                    value="{{ old('endereco_logradouro', $fornecedor->endereco?->logradouro) }}" />
                                <x-input id="endereco_numero" name="endereco_numero" label="Número"
                                    value="{{ old('endereco_numero', $fornecedor->endereco?->numero) }}" />
                                <x-input id="endereco_compl" name="endereco_compl" label="Complemento"
                                    value="{{ old('endereco_compl', $fornecedor->endereco?->complemento) }}" />
                            </div>
                        </div>
                    </div>

                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                        <x-button type="submit">Salvar Alterações</x-button>
                        <x-link href="/fornecedores/{{ $fornecedor->id }}">Cancelar</x-link>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/valida.js') }}"></script>

    <script>
        let contatoIndex = {{ $fornecedor->contatos->count() ?? 1 }};

        function addContato() {
            const wrapper = document.getElementById('contatos-wrapper');
            const div = document.createElement('div');
            div.classList = "grid grid-cols-1 md:grid-cols-3 gap-4 p-4 border rounded-xl dark:border-neutral-700 relative";
            div.innerHTML = `
            <x-input name="contatos[\${contatoIndex}][nome]" label="Nome" placeholder="Nome da pessoa" />
            <x-input name="contatos[\${contatoIndex}][telefone]" label="Telefone" placeholder="(11) 99999-9999" />
            <x-input name="contatos[\${contatoIndex}][email]" label="E-mail" placeholder="contato@empresa.com" />

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
