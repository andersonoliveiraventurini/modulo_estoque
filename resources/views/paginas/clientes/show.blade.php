<x-layouts.app :title="__('Visualizar cliente')">
    <div class="flex  w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">

            <div
                class="bg-white p-6 shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <!-- Cabeçalho -->

                <div class="grid grid-cols-12 items-center mb-6">
                    <!-- 60% (esquerda) -->
                    <div class="col-span-12 md:col-span-8">
                        <h2 class="text-xl font-semibold flex items-center gap-2">
                            <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                            Dados do Cliente
                        </h2>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Aqui estão as informações completas do cliente selecionado.
                        </p>
                    </div>

                    <!-- 40% (direita com links) -->
                    <div class="col-span-12 md:col-span-4 flex justify-end gap-6">
                        <a href="{{ route('bloqueios.mostrar', $cliente->id ?? null) }}"
                            class="text-primary-600 hover:underline text-sm font-medium">
                            Bloqueios
                        </a>
                        <a href="{{ route('analise_creditos.mostrar', $cliente->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Análises de crédito
                        </a>
                        <a href="{{ route('orcamentos.cliente', $cliente->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Orçamentos
                        </a>  
                        <a href="{{ route('orcamentos.criar', $cliente->id ?? null) }}"
                            class="text-secondary-600 hover:underline text-sm font-medium">
                            Criar Orçamento
                        </a>  
                    </div>
                </div>

                <!-- Abas -->
                <x-tabs default="basico">
                    <x-tab name="basico" label="Básico">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="CNPJ" :value="$cliente->cnpj_formatado" />
                            <x-show-field label="Razão Social" :value="$cliente->razao_social" />
                            <x-show-field label="Nome Fantasia" :value="$cliente->nome_fantasia" />
                            <x-show-field label="Tratamento" :value="$cliente->tratamento" />
                            <x-show-field label="Inscrição Estadual" :value="$cliente->inscricao_estadual" />
                            <x-show-field label="Inscrição Municipal" :value="$cliente->inscricao_municipal" />
                            <x-show-field label="Data de Abertura" :value="$cliente->dataaberturaformatada" />

                            {{-- Campos que não estavam sendo exibidos --}}
                            <x-show-field label="CNAE" :value="$cliente->cnae" />
                            <x-show-field label="Regime Tributário" :value="$cliente->regime_tributario" />
                            <x-show-field label="Classificação" :value="$cliente->classificacao" />
                            <x-show-field label="Canal de Origem" :value="$cliente->canal_origem" />



                        </div>
                    </x-tab>


                    <x-tab name="responsavel" label="Responsável">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="CPF" :value="$cliente->cpf" />
                            <x-show-field label="Nome" :value="$cliente->nome" /> <x-show-field label="Data de Nascimento"
                                :value="$cliente->datanascimentoformatada" />

                            <x-show-field label="SUFRAMA" :value="$cliente->suframa" />
                        </div>
                    </x-tab>

                    <x-tab name="classificacao" label="Classificação">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Classificação" :value="$cliente->classificacao" />
                            <x-show-field label="Canal de Origem" :value="$cliente->canal_origem" />
                        </div>
                    </x-tab>

                    <x-tab name="credito" label="Crédito">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="Vendedor Interno" :value="$cliente->vendedor?->nome" />
                            <x-show-field label="Vendedor Externo" :value="$cliente->vendedorExterno?->nome" />
                            <x-show-field label="Desconto (%)" :value="$cliente->desconto" />
                            <x-show-field label="Bloqueado" :value="$cliente->bloqueado ? 'Sim' : 'Não'" />
                            <x-show-field label="Negociar títulos" :value="$cliente->negociar_titulos ? 'Sim' : 'Não'" />
                            <x-show-field label="Limite Boleto (R$)" :value="number_format($cliente->limite_boleto, 2, ',', '.')" />
                            <x-show-field label="Limite Carteira (R$)" :value="number_format($cliente->limite_carteira, 2, ',', '.')" />
                            <x-show-field label="Inativar após (meses)" :value="$cliente->inativar_apos" />
                            {{-- Exibição do arquivo --}}
                            <div>
                                <h4 class="font-medium">Certidões Negativas</h4>
                                @forelse($cliente->certidoesNegativas as $doc)
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

                        </div>
                    </x-tab>

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
                    <x-tab name="endereco" label="Endereço">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="CEP" :value="$cliente->enderecoComercial?->cep" />
                            <x-show-field label="Cidade" :value="$cliente->enderecoComercial?->cidade" />
                            <x-show-field label="Estado" :value="$cliente->enderecoComercial?->estado" />
                            <x-show-field label="Bairro" :value="$cliente->enderecoComercial?->bairro" />
                            <x-show-field label="Número" :value="$cliente->enderecoComercial?->numero" />
                            <x-show-field label="Complemento" :value="$cliente->enderecoComercial?->complemento" />
                            <x-show-field label="Logradouro" :value="$cliente->enderecoComercial?->logradouro" class="md:col-span-3" />
                        </div>
                    </x-tab>

                    <x-tab name="entrega" label="Endereço de entrega">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-show-field label="CEP" :value="$cliente->enderecoEntrega?->cep" />
                            <x-show-field label="Cidade" :value="$cliente->enderecoEntrega?->cidade" />
                            <x-show-field label="Estado" :value="$cliente->enderecoEntrega?->estado" />
                            <x-show-field label="Bairro" :value="$cliente->enderecoEntrega?->bairro" />
                            <x-show-field label="Número" :value="$cliente->enderecoEntrega?->numero" />
                            <x-show-field label="Complemento" :value="$cliente->enderecoEntrega?->complemento" />
                            <x-show-field label="Logradouro" :value="$cliente->enderecoEntrega?->logradouro" class="md:col-span-3" />
                        </div>
                    </x-tab>

                </x-tabs>

                <!-- Botões -->
                <div class="flex gap-4 mt-6">
                    <a href="{{ route('clientes.edit', $cliente) }}">
                        <x-button size="sm" variant="secondary">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </x-button>
                    </a>
                    <a href="{{ route('clientes.index') }}">
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
