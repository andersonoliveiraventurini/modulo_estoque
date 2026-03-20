<?php

namespace App\Notifications;

use App\Models\Orcamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Support\Facades\Log;

class RouteBillingDeniedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Orcamento $orcamento,
        public string $reason,
        public string $deniedByName,
    ) {}

    public function via(object $notifiable): array
    {
        // Desabilitado temporariamente pois a tabela notifications não existe no banco
        return [];
    }

    public function toDatabase(object $notifiable): array
    {
        Log::info('Enviando notificação de negação de faturamento de Rota', [
            'orcamento_id' => $this->orcamento->id,
            'notifiable_id' => $notifiable->id,
        ]);

        return [
            'title'         => "Faturamento Negado — Pedido #{$this->orcamento->id}",
            'body'          => "O faturamento do pedido de rota #{$this->orcamento->id} foi negado por {$this->deniedByName}. Motivo: {$this->reason}",
            'orcamento_id'  => $this->orcamento->id,
            'event'         => 'route_billing_denied',
            'denied_by'     => $this->deniedByName,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
