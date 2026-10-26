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
 * Confirmation du rendez-vous envoyée au parent par le secrétariat (date + lien Zoom/Meet).
 */
class ConsultationScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ConsultationRequest $consultation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('info@nlcrdc.org', 'Never Limit Children'),
            replyTo: [new Address(config('services.nlc.secretariat_email'), 'Secrétariat NLC')],
            subject: 'Votre rendez-vous est confirmé - ' . $this->consultation->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consultation-scheduled',
            with: [
                'c'      => $this->consultation,
                'seance' => $this->consultation->seanceFormatee(),
            ],
        );
    }
}
