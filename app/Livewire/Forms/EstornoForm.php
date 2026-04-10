<?php

namespace App\Livewire\Forms;

use Livewire\Attributes\Validate;
use Livewire\Form;

class EstornoForm extends Form
{
    #[Validate('required|exists:pagamentos,id')]
    public $pagamento_id = '';

    #[Validate('required|string|min:10')]
    public $motivo = '';

    #[Validate('required|string|in:debito,credito,pix,dinheiro,outro')]
    public $forma_estorno = '';

    #[Validate('required_if:forma_estorno,outro|string|nullable')]
    public $forma_estorno_detalhe = '';

    #[Validate('required|numeric|min:0.01')]
    public $valor = '';
}
