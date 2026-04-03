<div class="space-y-6 p-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                Gerenciador de Arquivos
            </h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 font-mono mt-0.5">
                /{{ $currentFolder ?: 'raiz' }}
            </p>
        </div>

        @if(count($selectedFiles) > 0)
            <form method="POST" action="/admin/storage/zip">
                @csrf
                @foreach($selectedFiles as $path)
                    <input type="hidden" name="paths[]" value="{{ $path }}">
                @endforeach
                <flux:button type="submit" variant="primary" icon="arrow-down-tray">
                    Baixar ZIP ({{ count($selectedFiles) }} arquivos)
                </flux:button>
            </form>
        @endif
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3">
        @if($currentFolder)
            <flux:button wire:click="navigateUp" variant="ghost" size="sm" icon="arrow-left">
                Voltar
            </flux:button>
            <span class="text-zinc-300 dark:text-zinc-600">|</span>
        @endif

        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Buscar arquivos..."
            icon="magnifying-glass"
            clearable
            size="sm"
            class="w-64"
        />

        @if($search)
            <span class="text-sm text-neutral-500 dark:text-neutral-400">
                {{ count($files) }} resultado(s)
            </span>
        @endif
    </div>

    {{-- Pastas (só exibe quando não está buscando) --}}
    @if(! $search && count($folders) > 0)
        <div>
            <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-3">
                Pastas
            </p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($folders as $folder)
                    <button
                        wire:click="navigateTo('{{ $folder['path'] }}')"
                        class="group flex items-center gap-3 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 hover:border-accent hover:bg-accent/5 transition-all text-left"
                    >
                        <flux:icon.folder class="size-5 text-accent shrink-0" />
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                {{ $folder['name'] }}
                            </p>
                            <p class="text-xs text-neutral-500">{{ $folder['count'] }} PDF(s)</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Tabela de arquivos --}}
    @if(count($files) > 0)
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">

            {{-- Cabeçalho da tabela --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-zinc-50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                <input
                    type="checkbox"
                    wire:model.live="selectAll"
                    class="rounded border-zinc-300 dark:border-zinc-600 text-accent"
                >
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                    Arquivo
                </span>
                <span class="ml-auto text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider hidden sm:block">
                    Tamanho
                </span>
                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider hidden md:block w-36 text-right">
                    Modificado
                </span>
                <span class="w-24"></span>
            </div>

            {{-- Linhas --}}
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($files as $file)
                    <div class="group">
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors">
                            <input
                                type="checkbox"
                                wire:click="toggleSelect('{{ $file['relative_path'] }}')"
                                @checked(in_array($file['relative_path'], $selectedFiles))
                                class="rounded border-zinc-300 dark:border-zinc-600 text-accent"
                            >

                            <flux:icon.document-text class="size-4 text-red-500 shrink-0" />

                            <span
                                class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate flex-1 cursor-pointer hover:text-accent"
                                wire:click="togglePreview('{{ $file['relative_path'] }}')"
                            >
                                {{ $file['name'] }}
                            </span>

                            <span class="text-xs text-neutral-500 hidden sm:block">
                                {{ number_format($file['size'] / 1024, 1) }} KB
                            </span>

                            <span class="text-xs text-neutral-500 hidden md:block w-36 text-right">
                                {{ \Carbon\Carbon::createFromTimestamp($file['modified'])->format('d/m/Y H:i') }}
                            </span>

                            <div class="flex items-center gap-1 w-24 justify-end">
                                <flux:button
                                    wire:click="togglePreview('{{ $file['relative_path'] }}')"
                                    variant="ghost"
                                    size="sm"
                                    icon="{{ $previewingFile === $file['relative_path'] ? 'eye-slash' : 'eye' }}"
                                    title="Visualizar"
                                />
                                <a href="{{ $file['url'] }}" download target="_blank">
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-down-tray"
                                        title="Baixar"
                                    />
                                </a>
                                <flux:button
                                    wire:click="confirmDelete('{{ $file['relative_path'] }}')"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    title="Excluir"
                                    class="text-red-500 hover:text-red-600"
                                />
                            </div>
                        </div>

                        {{-- Preview inline --}}
                        @if($previewingFile === $file['relative_path'])
                            <div class="px-4 pb-4 bg-zinc-50 dark:bg-zinc-800/40">
                                <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                    <iframe
                                        src="{{ $file['url'] }}#toolbar=1&navpanes=0"
                                        class="w-full"
                                        style="height: 600px;"
                                        title="{{ $file['name'] }}"
                                    ></iframe>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-700">
            <flux:icon.document class="size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
            <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                {{ $search ? 'Nenhum PDF encontrado para "' . $search . '"' : 'Nenhum PDF nesta pasta' }}
            </p>
        </div>
    @endif

    {{-- Modal confirmação de exclusão --}}
    <flux:modal name="confirm-delete" class="max-w-sm">
        <flux:heading>Confirmar exclusão</flux:heading>
        <flux:text class="mt-2 text-sm text-neutral-500">
            <span class="font-mono font-medium text-neutral-700 dark:text-neutral-300">
                {{ basename($deletingPath ?? '') }}
            </span>
            será removido permanentemente.
        </flux:text>
        <div class="flex gap-2 mt-6">
            <flux:button variant="danger" wire:click="delete">Excluir</flux:button>
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

</div>
