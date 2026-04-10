<?php

namespace App\Notifications;

use App\Models\Estorno;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstornoDecididoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Estorno $estorno
    ) {}

    public function via(object $notifiable): array
    {
        // Mantém o padrão do projeto em relação à ausência da tabela 'notifications'
        return [];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event'                => 'estorno_decidido',
            'estorno_id'           => $this->estorno->id,
            'status'               => $this->estorno->status, // 'aprovado' ou 'rejeitado'
            'observacao_aprovador' => $this->estorno->observacao_aprovador,
            'aprovador'            => $this->estorno->aprovador->name,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
