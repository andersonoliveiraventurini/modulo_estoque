<?php

namespace App\Events;

use App\Models\Orcamento;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrcamentoPago
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $orcamento;

    /**
     * Create a new event instance.
     */
    public function __construct(Orcamento $orcamento)
    {
        $this->orcamento = $orcamento;
    }
}
