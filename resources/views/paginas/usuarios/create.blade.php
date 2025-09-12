<x-layouts.app :title="__('Cadastrar usuário')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Cadastro de usuário
                </h2>
                <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <!-- Informações Pessoais -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
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
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                         <x-button type="submit" >Cadastrar Cliente</x-button>
                        <x-button type="reset">Limpar Formulário</x-button>
                    </div>
                    <!-- Botões -->
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
