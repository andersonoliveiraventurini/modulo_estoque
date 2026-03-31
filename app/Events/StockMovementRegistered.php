<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockMovementRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @param array $data ['produto_id', 'posicao_id', 'tipo_movimentacao', 'quantidade', 'colaborador_id', 'observacao']
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
