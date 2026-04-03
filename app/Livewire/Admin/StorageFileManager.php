<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class StorageFileManager extends Component
{
    public string $currentFolder = '';
    public string $search = '';
    public ?string $previewingFile = null;
    public array $selectedFiles = [];
    public bool $selectAll = false;
    public ?string $deletingPath = null;

    protected $queryString = ['currentFolder'];

    public function mount(): void
    {
        // Inicia na raiz do disk public
    }

    public function navigateTo(string $folder): void
    {
        Log::info('Gerenciador de Arquivos: Navegou para pasta', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'folder' => $folder
        ]);

        $this->currentFolder  = $folder;
        $this->search         = '';
        $this->previewingFile = null;
        $this->selectedFiles  = [];
        $this->selectAll      = false;
    }

    public function navigateUp(): void
    {
        $parent = dirname($this->currentFolder);
        $this->navigateTo($parent === '.' ? '' : $parent);
    }

    public function togglePreview(string $path): void
    {
        if ($this->previewingFile !== $path) {
            Log::info('Gerenciador de Arquivos: Visualizou arquivo', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'file' => $path
            ]);
        }

        $this->previewingFile = $this->previewingFile === $path ? null : $path;
    }

    public function confirmDelete(string $path): void
    {
        $this->deletingPath = $path;
        $this->dispatch('open-modal', name: 'confirm-delete');
    }

    public function delete(): void
    {
        abort_if(! $this->deletingPath || str_contains($this->deletingPath, '..'), 403);
        
        // Somente usuários admin podem ter acesso a isso
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        Log::warning('Gerenciador de Arquivos: EXCLUIU arquivo', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'file' => $this->deletingPath
        ]);

        Storage::disk('public')->delete($this->deletingPath);

        $this->selectedFiles  = array_values(array_diff($this->selectedFiles, [$this->deletingPath]));
        $this->previewingFile = null;
        $this->deletingPath   = null;

        $this->dispatch('close-modal', name: 'confirm-delete');
        $this->dispatch('notify', type: 'success', message: 'Arquivo excluído com sucesso.');
    }

    public function updatedSearch(): void
    {
        $this->selectedFiles = [];
        $this->selectAll     = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedFiles = $value
            ? collect($this->getFiles())->pluck('relative_path')->toArray()
            : [];
    }

    public function toggleSelect(string $path): void
    {
        if (in_array($path, $this->selectedFiles)) {
            $this->selectedFiles = array_values(array_diff($this->selectedFiles, [$path]));
        } else {
            $this->selectedFiles[] = $path;
        }

        $this->selectAll = count($this->selectedFiles) === count($this->getFiles());
    }

    protected function getFiles(): array
    {
        $disk = Storage::disk('public');

        return collect($disk->files($this->currentFolder))
            ->filter(fn($f) => str_ends_with(strtolower($f), '.pdf'))
            ->when($this->search, fn($c) => $c->filter(
                fn($f) => str_contains(strtolower(basename($f)), strtolower($this->search))
            ))
            ->map(fn($f) => [
                'relative_path' => $f,
                'name'          => basename($f),
                'url'           => Storage::disk('public')->url($f),
                'size'          => $disk->size($f),
                'modified'      => $disk->lastModified($f),
            ])
            ->sortByDesc('modified')
            ->values()
            ->toArray();
    }

    protected function getFolders(): array
    {
        return collect(Storage::disk('public')->directories($this->currentFolder))
            ->map(fn($d) => [
                'path'  => $d,
                'name'  => basename($d),
                'count' => count(Storage::disk('public')->files($d)),
            ])
            ->values()
            ->toArray();
    }

    public function render()
    {
        // Somente usuários admin podem ter acesso a isso
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        if (! $this->search && ! $this->previewingFile) {
            Log::info('Gerenciador de Arquivos: Acessou visualização', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'current_folder' => $this->currentFolder
            ]);
        }

        return view('livewire.admin.storage-file-manager', [
            'files'   => $this->getFiles(),
            'folders' => $this->getFolders(),
        ])->layout('components.layouts.app');
    }
}
