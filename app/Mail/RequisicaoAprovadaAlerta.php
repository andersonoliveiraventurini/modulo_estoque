<?php

namespace App\Mail;

use App\Models\RequisicaoCompra;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequisicaoAprovadaAlerta extends Mailable
{
    use Queueable, SerializesModels;

    public $requisicao;

    /**
     * Create a new message instance.
     */
    public function __construct(RequisicaoCompra $requisicao)
    {
        $this->requisicao = $requisicao;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Requisição de Compra #{$this->requisicao->id} APROVADA",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.requisicoes.aprovada',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
