<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordSetNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $frontendUrl = env('FRONTEND_NLC');
        $url = "{$frontendUrl}/set-password?token={$this->token}";
        return (new MailMessage)
            ->subject('Définir votre mot de passe')
            ->line('Bienvenue ! Cliquez sur le bouton ci-dessous pour définir votre mot de passe. Ce lien expire dans 7 jours.')
            ->action('Définir le mot de passe', $url)
            ->line('Si vous n\'êtes pas à l\'origine de cette inscription, ignorez ce mail.');
    }
}
