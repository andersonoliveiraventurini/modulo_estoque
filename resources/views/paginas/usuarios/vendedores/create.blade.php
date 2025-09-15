<x-layouts.app :title="__('Cadastrar vendedor')">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <!-- Informações Básicas -->
            <div
                class="bg-white p-6  shadow rounded-2xl border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-xl font-semibold flex items-center gap-2 mb-4">
                    <x-heroicon-o-user class="w-5 h-5 text-primary-600" />
                    Cadastro de vendedor
                </h2>
                <form action="{{ route('clientes.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                            <x-select name="user_id" label="Vendedor">
                                <option value="">Selecione quem será o vendedor</option>
                                @foreach ($usuarios as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </x-select>
                            <x-select name="externo" label="Externo">
                                <option value="">Selecione</option>
                                <option value="1">Sim</option>
                                <option value="0">Não</option>
                            </x-select>
                            <x-input name="desconto" label="% Desconto autorizado" placeholder="5" min="0" max="30"
                            value="0"/>
                        </div>
                    </div>
                    
                    <br />
                    <!-- Ações -->
                    <div class="flex gap-4">
                         <x-button type="submit" >Cadastrar vendedor</x-button>
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
