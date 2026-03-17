<x-layouts.app :title="__('Faturamento de Rota — Orçamento #') . $orcamento->id">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <livewire:pagamento-rota :orcamentoId="$orcamento->id" />
    </div>
</x-layouts.app>
