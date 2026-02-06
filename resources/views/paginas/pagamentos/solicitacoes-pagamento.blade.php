<x-layouts.app :title="__('Aprovação de Meio de Pagamento - Orçamento #' . $orcamento_id)">
    <div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
           {{-- ✅ PASSAR O PARÂMETRO CORRETAMENTE PARA O LIVEWIRE --}}
           <livewire:confirmar-solicitacao-pagamento :orcamento-id="$orcamento_id" />
        </div>
    </div>
</x-layouts.app>