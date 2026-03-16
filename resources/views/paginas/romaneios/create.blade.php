<x-layouts.app title="Novo Romaneio">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <x-button href="{{ route('romaneios.index') }}" variant="ghost" icon="chevron-left" />
            <div>
                <x-heading size="xl">Novo Romaneio</x-heading>
                <x-subheading>Crie um novo lote de entrega para agrupar seus pedidos.</x-subheading>
            </div>
        </div>

        <x-card class="p-6">
            <form method="POST" action="{{ route('romaneios.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <x-input name="descricao" label="Descrição / Nome da Carga" 
                            placeholder="Ex: Carga Segunda-feira - Rota Norte" 
                            required value="{{ old('descricao') }}" />
                    </div>

                    <x-input name="motorista" label="Motorista" 
                        placeholder="Nome do motorista" 
                        value="{{ old('motorista') }}" />

                    <x-input name="veiculo" label="Veículo / Placa" 
                        placeholder="Identificação do veículo" 
                        value="{{ old('veiculo') }}" />

                    <x-input type="date" name="data_entrega" label="Data Prevista de Entrega" 
                        required value="{{ old('data_entrega', now()->format('Y-m-d')) }}" />
                </div>

                <x-textarea name="observacoes" label="Observações">{{ old('observacoes') }}</x-textarea>

                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100">
                    <x-button href="{{ route('romaneios.index') }}" variant="ghost">Cancelar</x-button>
                    <x-button type="submit" variant="primary">Criar Romaneio</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
