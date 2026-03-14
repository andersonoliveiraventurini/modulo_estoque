<?php

namespace App\Mail;

use App\Models\InconsistenciaRecebimento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InconsistenciaRecebimentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inconsistencia;

    /**
     * Create a new message instance.
     */
    public function __construct(InconsistenciaRecebimento $inconsistencia)
    {
        $this->inconsistencia = $inconsistencia;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('ALERTA: Inconsistência no Recebimento de Mercadoria')
                    ->view('emails.inconsistencia_recebimento');
    }
}
