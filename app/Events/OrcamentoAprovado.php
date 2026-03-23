<?php

namespace App\Events;

use App\Models\Orcamento;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrcamentoAprovado
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Orcamento $orcamento) {}
}
