<?php

namespace Tests\Feature;

use App\Jobs\SendConsortiumMail;
use App\Jobs\SendOrientationMail;
use App\Models\Consortium\Notification;
use App\Models\Orientation\Notice;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Tests\OrientationTestCase;

/**
 * Les mails partent en deux versions (HTML et texte brut) : les filtres anti-spam
 * pénalisent les messages en HTML seul. SQLite en mémoire et transport « array » :
 * aucun e-mail réel n'est envoyé.
 */
class MailTextVersionTest extends OrientationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_10_31_000000_create_consortium_tables.php'])->assertSuccessful();
        Mail::purge('array');
    }

    private function lastEmail(): Email
    {
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);

        return $messages->first()->getOriginalMessage();
    }

    public function test_orientation_login_mail_has_html_and_text_versions(): void
    {
        $notice = Notice::create([
            'recipient' => 'famille@example.test',
            'title' => 'Votre lien de connexion',
            'body' => 'Utilisez ce lien pour ouvrir votre espace.',
            'action_url' => 'https://nlcrdc.org/espace-famille?jeton=abc123',
            'expires_at' => now()->addMinutes(30),
        ]);

        (new SendOrientationMail($notice->id))->handle();

        $email = $this->lastEmail();
        $this->assertStringContainsString('Ouvrir mon espace', (string) $email->getHtmlBody());
        $text = (string) $email->getTextBody();
        $this->assertStringContainsString('Votre lien de connexion', $text);
        $this->assertStringContainsString('Utilisez ce lien pour ouvrir votre espace.', $text);
        $this->assertStringContainsString('https://nlcrdc.org/espace-famille?jeton=abc123', $text);
        $this->assertStringContainsString('(Kinshasa)', $text);
        $this->assertStringNotContainsString('<', $text);
        $this->assertStringNotContainsString('&amp;', $text);
        $this->assertSame('sent', $notice->fresh()->email_status);
    }

    public function test_orientation_mail_without_link_keeps_a_clean_text_version(): void
    {
        $notice = Notice::create([
            'recipient' => 'famille@example.test',
            'title' => 'Message du secrétariat',
            'body' => "Bonjour,\nVotre dossier avance.",
        ]);

        (new SendOrientationMail($notice->id))->handle();

        $text = (string) $this->lastEmail()->getTextBody();
        $this->assertStringContainsString("Bonjour,\nVotre dossier avance.", $text);
        $this->assertStringNotContainsString('Ouvrir mon espace', $text);
        $this->assertStringNotContainsString('expire', $text);
    }

    public function test_consortium_mail_has_html_and_text_versions(): void
    {
        $notification = Notification::create([
            'recipient' => 'partenaire@example.test',
            'title' => 'Dossier reçu',
            'body' => 'Merci pour votre demande & vos documents.',
            'action_url' => 'https://nlcrdc.org/espace-partenaire?a=1&b=2',
        ]);

        (new SendConsortiumMail($notification->id))->handle();

        $email = $this->lastEmail();
        $this->assertStringContainsString('Ouvrir mon espace', (string) $email->getHtmlBody());
        $text = (string) $email->getTextBody();
        $this->assertStringContainsString('Dossier reçu', $text);
        $this->assertStringContainsString('Merci pour votre demande & vos documents.', $text);
        $this->assertStringContainsString('https://nlcrdc.org/espace-partenaire?a=1&b=2', $text);
        $this->assertStringNotContainsString('<', $text);
        $this->assertStringNotContainsString('&amp;', $text);
    }
}
