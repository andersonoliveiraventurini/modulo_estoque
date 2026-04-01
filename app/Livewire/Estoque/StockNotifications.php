<?php

namespace App\Livewire\Estoque;

use App\Models\SystemAlert;
use Livewire\Component;
use Livewire\WithPagination;

class StockNotifications extends Component
{
    use WithPagination;

    public function markAsRead($id)
    {
        $alert = SystemAlert::findOrFail($id);
        $alert->update(['lida' => true]);
    }

    public function markAllAsRead()
    {
        SystemAlert::where('lida', false)->update(['lida' => true]);
    }

    public function render()
    {
        $alerts = SystemAlert::with(['produto', 'orcamento'])
            ->orderBy('lida', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.estoque.stock-notifications', [
            'alerts' => $alerts
        ]);
    }
}
