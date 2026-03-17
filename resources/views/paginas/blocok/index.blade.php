<x-layouts.app title="Bloco K (Fiscal)">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-white">Bloco K - SPED Fiscal</h1>
                <p class="text-neutral-500 dark:text-neutral-400">Geração de arquivos de controle de estoque e produção para o SPED.</p>
            </div>
            <div class="flex gap-2">
                <flux:button variant="ghost" icon="beaker" href="{{ route('blocok.insumos.index') }}">Insumos</flux:button>
                <flux:button variant="ghost" icon="trash" href="{{ route('blocok.descartes.index') }}">Descartes</flux:button>
            </div>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Componente de Geração --}}
            <div class="md:col-span-1">
                <livewire:blocok.generator />
            </div>

            {{-- Histórico de Geração --}}
            <div class="md:col-span-2">
                <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
                    <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700">
                        <h2 class="font-bold text-neutral-800 dark:text-white uppercase text-xs tracking-widest">Histórico de Arquivos</h2>
                    </div>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Data Ref</flux:table.column>
                            <flux:table.column>Gerado em</flux:table.column>
                            <flux:table.column align="end">Ações</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($historico as $item)
                                <flux:table.row>
                                    <flux:table.cell>{{ $item->k100 }}</flux:table.cell>
                                    <flux:table.cell>{{ $item->created_at->format('d/m/Y H:i') }}</flux:cell>
                                    <flux:table.cell align="end">
                                        <flux:button size="sm" variant="ghost" icon="document-arrow-down" href="{{ route('blocok.download', $item->id) }}">Baixar</flux:button>
                                        <form action="{{ route('blocok.destroy', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button size="sm" variant="ghost" icon="trash" color="red" type="submit" />
                                        </form>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="3" class="text-center py-8">Nenhum arquivo gerado ainda.</flux:cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>

                    <div class="p-4">
                        {{ $historico->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
