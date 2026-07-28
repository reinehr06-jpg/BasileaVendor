<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Notificação Push para o Basiléia Vendor OS.
 * 
 * Esta classe pode ser usada para disparar notificações tanto via e-mail
 * quanto via Web Push (quando o canal web-push estiver configurado).
 * 
 * Exemplo de uso no Controller:
 * ```php
 * $user->notify(new PushNotification(
 *     'Nova Venda Registrada!',
 *     'O cliente Igreja Vida Nova assinou o plano PRO.',
 *     '/dashboard'
 * ));
 * ```
 */
class PushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;
    public string $body;
    public string $url;

    public function __construct(string $title, string $body, string $url = '/')
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
    }

    /**
     * Canais de entrega desta notificação.
     * Quando o pacote laravel-notification-channels/webpush for instalado,
     * basta adicionar 'webpush' ao array abaixo.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Representação da notificação no banco de dados (canal 'database').
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => '/icons/icon-192x192.png',
        ];
    }

    /**
     * Representação para o canal Web Push (quando configurado).
     * Retorna o payload JSON que o Service Worker receberá.
     */
    public function toWebPush(object $notifiable, $notification): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => '/icons/icon-192x192.png',
            'badge' => '/icons/icon-72x72.png',
        ];
    }
}
