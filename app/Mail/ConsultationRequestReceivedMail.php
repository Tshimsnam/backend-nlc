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
 * Accusé de réception envoyé au parent après son inscription.
 */
class ConsultationRequestReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ConsultationRequest $consultation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@nlcrdc.org', 'Never Limit Children'),
            subject: 'Demande de consultation reçue - ' . $this->consultation->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consultation-received',
            with: [
                'consultation' => $this->consultation,
                'creneaux'     => $this->consultation->creneauxFormates(),
            ],
        );
    }
}
