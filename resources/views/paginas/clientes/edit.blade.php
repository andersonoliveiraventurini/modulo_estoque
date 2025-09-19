<x-layouts.app :title="__('Editar cliente')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->
                <h2 class="text-xl font-semibold mb-6">
                    Editar Cliente
                </h2>

                <form action="{{ route('clientes.update', $cliente) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <x-tabs default="basico">
                        <!-- Aba Básico -->
                        <x-tab name="basico" label="Básico">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-input name="cnpj" label="CNPJ" readonly
                                    value="{{ old('cnpj', $cliente->cnpj) }}" />
                                <x-input name="razao_social" label="Razão Social"
                                    value="{{ old('razao_social', $cliente->razao_social) }}" />
                                <x-input name="nome_fantasia" label="Nome Fantasia"
                                    value="{{ old('nome_fantasia', $cliente->nome_fantasia) }}" />
                                <x-input name="tratamento" label="Tratamento"
                                    value="{{ old('tratamento', $cliente->tratamento) }}" />
                                <x-input name="inscricao_estadual" label="Inscrição Estadual"
                                    value="{{ old('inscricao_estadual', $cliente->inscricao_estadual) }}" />
                                <x-input name="inscricao_municipal" label="Inscrição Municipal"
                                    value="{{ old('inscricao_municipal', $cliente->inscricao_municipal) }}" />
                                <x-input type="date" name="data_abertura" label="Data de Abertura"
                                    value="{{ old('data_abertura', $cliente->data_abertura?->format('Y-m-d')) }}" />

                                <x-input name="cnae" label="CNAE" value="{{ old('cnae', $cliente->cnae) }}" />
                                <x-input name="regime_tributario" label="Regime Tributário"
                                    value="{{ old('regime_tributario', $cliente->regime_tributario) }}" />
                                <x-input name="classificacao" label="Classificação"
                                    value="{{ old('classificacao', $cliente->classificacao) }}" />
                                <x-input name="canal_origem" label="Canal de Origem"
                                    value="{{ old('canal_origem', $cliente->canal_origem) }}" />

                                <div class="col-span-3">
                                    <label class="block text-sm font-medium mb-1">Certidões Negativas</label>
                                    @if ($cliente->certidoesNegativas->count())
                                        <ul>
                                            @foreach ($cliente->certidoesNegativas as $doc)
                                                <li>
                                                    <a href="{{ asset('storage/' . $doc->caminho_arquivo) }}"
                                                        target="_blank">
                                                        📄 {{ $doc->descricao ?? 'Arquivo' }}
                                                    </a>
                                                    <label>
                                                        <input type="checkbox" name="delete_documents[]"
                                                            value="{{ $doc->id }}">
                                                        Remover
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <input type="file" name="certidoes_negativas" accept=".pdf,image/*" />
                                </div>
                                <x-input name="desconto" label="Desconto (%)"
                                    value="{{ old('desconto', $cliente->desconto) }}" />
                                <x-select name="negociar_titulos" label="Negociar títulos">
                                    <option value="0" @selected(!$cliente->negociar_titulos)>Não</option>
                                    <option value="1" @selected($cliente->negociar_titulos)>Sim</option>
                                </x-select>
                                <x-input name="inativar_apos" label="Inativar após (meses)"
                                    value="{{ old('inativar_apos', $cliente->inativar_apos) }}" />
                            </div>
                        </x-tab>

                        <!-- Aba Responsável -->
                        <x-tab name="responsavel" label="Responsável">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-input name="cpf_responsavel" label="CPF"
                                    value="{{ old('cpf_responsavel', $cliente->cpf) }}" />
                                <x-input name="nome" label="Nome" value="{{ old('nome', $cliente->nome) }}" />
                                <x-input type="date" name="data_nascimento" label="Data de Nascimento"
                                    value="{{ old('data_nascimento', $cliente->data_nascimento?->format('Y-m-d')) }}" />
                                <x-input name="suframa" label="SUFRAMA"
                                    value="{{ old('suframa', $cliente->suframa) }}" />
                            </div>
                        </x-tab>

                        <!-- Aba Endereço Comercial -->
                        <x-tab name="endereco" label="Endereço Comercial">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-input name="endereco_cep" label="CEP"
                                    value="{{ old('endereco_cep', $cliente->enderecoComercial?->cep) }}" />
                                <x-input name="endereco_cidade" readonly label="Cidade"
                                    value="{{ old('endereco_cidade', $cliente->enderecoComercial?->cidade) }}" />
                                <x-input name="endereco_estado" readonly label="Estado"
                                    value="{{ old('endereco_estado', $cliente->enderecoComercial?->estado) }}" />
                                <x-input name="endereco_bairro" readonly label="Bairro"
                                    value="{{ old('endereco_bairro', $cliente->enderecoComercial?->bairro) }}" />
                                <x-input name="endereco_numero" label="Número"
                                    value="{{ old('endereco_numero', $cliente->enderecoComercial?->numero) }}" />
                                <x-input name="endereco_compl" label="Complemento"
                                    value="{{ old('endereco_compl', $cliente->enderecoComercial?->complemento) }}" />
                                <x-input name="endereco_logradouro" readonly class="md:col-span-3" label="Logradouro"
                                    value="{{ old('endereco_logradouro', $cliente->enderecoComercial?->logradouro) }}" />
                            </div>
                        </x-tab>

                        <!-- Aba Endereço de Entrega -->
                        <x-tab name="entrega" label="Endereço de Entrega">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-input name="entrega_cep" label="CEP"
                                    value="{{ old('entrega_cep', $cliente->enderecoEntrega?->cep) }}" />
                                <x-input name="entrega_cidade" readonly label="Cidade"
                                    value="{{ old('entrega_cidade', $cliente->enderecoEntrega?->cidade) }}" />
                                <x-input name="entrega_estado" readonly label="Estado"
                                    value="{{ old('entrega_estado', $cliente->enderecoEntrega?->estado) }}" />
                                <x-input name="entrega_bairro" readonly label="Bairro"
                                    value="{{ old('entrega_bairro', $cliente->enderecoEntrega?->bairro) }}" />
                                <x-input name="entrega_numero" readonly label="Número"
                                    value="{{ old('entrega_numero', $cliente->enderecoEntrega?->numero) }}" />
                                <x-input name="entrega_compl" label="Complemento"
                                    value="{{ old('entrega_compl', $cliente->enderecoEntrega?->complemento) }}" />
                                <x-input name="entrega_logradouro" readonly class="md:col-span-3" label="Logradouro"
                                    value="{{ old('entrega_logradouro', $cliente->enderecoEntrega?->logradouro) }}" />
                            </div>
                        </x-tab>
                    </x-tabs>

                    <!-- Botões -->
                    <div class="flex gap-4 mt-6">
                        <x-button type="submit">Salvar</x-button>
                        <x-link href="{{ route('clientes.show', $cliente) }}">Cancelar</x-link>
                    </div>
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
