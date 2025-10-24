<x-layouts.app :title="__('Visualizar fornecedor')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho com divisão -->
                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <x-heroicon-o-truck class="w-5 h-5 text-primary-600" />

                            Dados do Fornecedor
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Aqui estão as informações completas do fornecedor selecionado.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                        <a href="{{ route('fornecedores.precos', $fornecedor->id ?? null) }}"
                            class="text-primary-600 hover:underline text-sm font-medium">
                            Tabela de preços
                        </a>
                        <a href="{{ route('fornecedores.classificar', $fornecedor->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Classificar
                        </a>
                    </div>
                </div>


                <!-- Abas -->
                <x-tabs default="basico">
                    <!-- Dados Básicos -->
                    <x-tab name="basico" label="Básico">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="CNPJ" :value="$fornecedor->cnpj_formatado" />
                            <x-show-field label="Razão Social" :value="$fornecedor->razao_social" />
                            <x-show-field label="Nome Fantasia" :value="$fornecedor->nome_fantasia" />
                            <x-show-field label="Tratamento" :value="$fornecedor->tratamento" />
                            <x-show-field label="Inscrição Estadual" :value="$fornecedor->inscricao_estadual" />
                            <x-show-field label="Inscrição Municipal" :value="$fornecedor->inscricao_municipal" />
                            <x-show-field label="Data de Abertura" :value="$fornecedor->dataaberturaformatada" />
                            <x-show-field label="CNAE Principal" :value="$fornecedor->cnae_principal" />
                            <x-show-field label="Regime Tributário" :value="ucfirst($fornecedor->regime_tributario)" />
                            <x-show-field label="Benefício" :value="$fornecedor->beneficio" />
                            <x-show-field label="Status" :value="ucfirst($fornecedor->status)" />
                        </div>
                    </x-tab>

                    <!-- Documentos -->
                    <x-tab name="documentos" label="Documentos">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Certidões Negativas -->
                            <div>
                                <h4 class="font-medium">Certidões Negativas</h4>
                                @forelse($fornecedor->certidoesNegativas as $doc)
                                    <p>
                                        <a href="{{ asset('storage/' . $doc->caminho_arquivo) }}" target="_blank"
                                            class="text-blue-600 hover:underline">
                                            {{ $doc->descricao ?? 'Ver arquivo' }}
                                        </a>
                                    </p>
                                @empty
                                    <p class="text-gray-500">Nenhuma certidão cadastrada</p>
                                @endforelse
                            </div>

                            <!-- Certificações de Qualidade -->
                            <div>
                                <h4 class="font-medium">Certificações de Qualidade</h4>
                                @forelse($fornecedor->certificacoesQualidade as $doc)
                                    <p>
                                        <a href="{{ asset('storage/' . $doc->caminho_arquivo) }}" target="_blank"
                                            class="text-blue-600 hover:underline">
                                            {{ $doc->descricao ?? 'Ver arquivo' }}
                                        </a>
                                    </p>
                                @empty
                                    <p class="text-gray-500">Nenhuma certificação cadastrada</p>
                                @endforelse
                            </div>

                        </div>
                    </x-tab>

                    <!-- Contatos -->
                    <x-tab name="contatos" label="Contatos">
                        <div class="space-y-2">
                            @forelse($contatos as $contato)
                                <div class="p-4 border rounded-xl dark:border-neutral-700">
                                    <p><strong>Nome:</strong> {{ $contato->nome }}</p>
                                    <p><strong>Telefone:</strong> {{ $contato->telefone }}</p>
                                    <p><strong>E-mail:</strong> {{ $contato->email }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-neutral-500">Nenhum contato informado.</p>
                            @endforelse
                        </div>
                    </x-tab>

                    <!-- Endereço -->
                    <x-tab name="endereco" label="Endereço">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="CEP" :value="$fornecedor->endereco->cep ?? ''" />
                            <x-show-field label="Cidade" :value="$fornecedor->endereco->cidade ?? ''" />
                            <x-show-field label="Estado" :value="$fornecedor->endereco->estado ?? ''" />
                            <x-show-field label="Bairro" :value="$fornecedor->endereco->bairro ?? ''" />
                            <x-show-field label="Número" :value="$fornecedor->endereco->numero ?? ''" />
                            <x-show-field label="Complemento" :value="$fornecedor->endereco->complemento ?? ''" />
                            <x-show-field label="Logradouro" :value="$fornecedor->endereco->logradouro ?? ''" class="md:col-span-3" />
                        </div>
                    </x-tab>
                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <a href="{{ route('fornecedores.edit', $fornecedor->id) }}">
                        <x-button size="sm" variant="secondary">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </x-button>
                    </a>
                    <a href="{{ route('fornecedores.index') }}">
                        <x-button size="sm" variant="primary">
                            <x-heroicon-o-arrow-uturn-left class="w-4 h-4" />
                            Voltar para a lista
                        </x-button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
