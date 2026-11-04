<?php

namespace App\Mail;

use App\Models\ConsultationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Alerte envoyée au secrétariat pour chaque nouvelle demande de consultation.
 * Les documents ne sont pas joints : ils restent sur le serveur (données de santé).
 */
class ConsultationRequestNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ConsultationRequest $consultation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@nlcrdc.org', 'Never Limit Children'),
            replyTo: [new Address($this->consultation->email, $this->consultation->parent_nom_complet)],
            subject: 'Nouvelle demande de consultation - ' . $this->consultation->enfant_nom_complet
                . ' (' . $this->consultation->reference . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consultation-notification',
            with: [
                'c'        => $this->consultation,
                'creneaux' => $this->consultation->creneauxFormates(),
            ],
        );
    }
}
