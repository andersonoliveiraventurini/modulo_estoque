<?php

namespace App\Notifications;

use App\Models\Estorno;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EstornoSolicitadoNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Estorno $estorno
    ) {}

    public function via(object $notifiable): array
    {
        // Mantém o padrão do projeto: retorna [] temporariamente 
        // caso a tabela notifications ainda não exista no banco
        return [];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'event'         => 'estorno_solicitado',
            'estorno_id'    => $this->estorno->id,
            'pagamento_id'  => $this->estorno->pagamento_id,
            'solicitante'   => $this->estorno->solicitante->name,
            'valor'         => $this->estorno->valor,
            'motivo'        => $this->estorno->motivo,
            'forma_estorno' => $this->estorno->forma_estorno,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
