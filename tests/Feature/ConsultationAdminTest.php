<?php

namespace Tests\Feature;

use App\Mail\ConsultationScheduledMail;
use App\Models\ConsultationRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConsultationAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Les anciennes migrations du projet ne tournent pas sur SQLite : on ne charge que celles des consultations
        $this->artisan('migrate:fresh', ['--path' => [
            'database/migrations/2026_10_26_000000_create_consultation_requests_table.php',
            'database/migrations/2026_10_27_000000_add_scheduling_fields_to_consultation_requests_table.php',
        ]]);
    }

    private function admin(): static
    {
        return $this->withSession(['admin_user' => (object) ['name' => 'Secrétariat']]);
    }

    private function consultation(array $overrides = []): ConsultationRequest
    {
        return ConsultationRequest::create(array_merge([
            'parent_nom' => 'Mbuyi', 'parent_prenom' => 'Grace', 'parent_lien' => 'mere',
            'pays' => 'Canada', 'ville' => 'Montréal', 'fuseau_horaire' => 'America/Toronto',
            'email' => 'grace@example.com', 'whatsapp' => '+1 (514) 555-0101', 'langue_seance' => 'Français',
            'enfant_nom' => 'Mbuyi', 'enfant_prenom' => 'David', 'enfant_sexe' => 'M',
            'enfant_date_naissance' => '2020-03-14', 'motif' => 'Retard de langage.',
            'creneaux' => [['start' => '2030-06-10T09:00:00Z']], 'plateforme' => 'meet',
            'consentement_donnees' => true, 'engagement_avis_psychiatre' => true,
            'acceptation_conditions' => true, 'consented_at' => now(),
        ], $overrides));
    }

    public function test_pages_require_an_admin_session(): void
    {
        $c = $this->consultation();

        $this->get('/admin/consultations')->assertRedirect(route('login'));
        $this->get("/admin/consultations/{$c->id}")->assertRedirect(route('login'));
        $this->put("/admin/consultations/{$c->id}", ['status' => 'annulee'])->assertRedirect(route('login'));
        $this->assertSame('nouvelle', $c->fresh()->status);
    }

    public function test_list_filters_by_status_and_search(): void
    {
        $this->consultation();
        $this->consultation(['enfant_prenom' => 'Sarah', 'status' => 'rdv_programme']);

        $this->admin()->get('/admin/consultations')
            ->assertOk()->assertSee('David')->assertSee('Sarah');

        $this->admin()->get('/admin/consultations?status=rdv_programme')
            ->assertOk()->assertSee('Sarah')->assertDontSee('David Mbuyi');

        $this->admin()->get('/admin/consultations?search=Sarah')
            ->assertOk()->assertSee('Sarah')->assertDontSee('David Mbuyi');
    }

    public function test_detail_shows_slots_in_kinshasa_time_and_whatsapp_link(): void
    {
        $c = $this->consultation();

        $this->admin()->get("/admin/consultations/{$c->id}")
            ->assertOk()
            ->assertSee($c->reference)
            ->assertSee('10 juin 2030 à 10:00')         // 09:00 UTC = 10:00 à Kinshasa
            ->assertSee('10 juin à 05:00', false)       // = 05:00 à Montréal (UTC-4)
            ->assertSee('https://wa.me/15145550101', false);
    }

    public function test_secretariat_schedules_and_notifies_parent(): void
    {
        Mail::fake();
        $c = $this->consultation();

        $this->admin()->from("/admin/consultations/{$c->id}")->put("/admin/consultations/{$c->id}", [
            'action' => 'notify',
            'status' => 'nouvelle',
            'professionnel_assigne' => 'Dr Kabongo',
            'seance_programmee_at' => '2030-06-10T14:30',   // heure de Kinshasa
            'duree_minutes' => 45,
            'lien_visio' => 'https://meet.google.com/abc-defg-hij',
            'prix' => '25',
            'devise' => 'USD',
        ])->assertRedirect("/admin/consultations/{$c->id}")->assertSessionHas('success');

        $c->refresh();
        $this->assertSame('rdv_programme', $c->status);
        $this->assertSame('2030-06-10 13:30:00', $c->seance_programmee_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('en_attente', $c->statut_paiement);
        $this->assertNotNull($c->confirmation_envoyee_at);

        Mail::assertSent(ConsultationScheduledMail::class, function ($mail) {
            $html = $mail->render();

            return $mail->hasTo('grace@example.com')
                && str_contains($html, 'https://meet.google.com/abc-defg-hij')
                && str_contains($html, '14:30')   // Kinshasa
                && str_contains($html, '09:30')   // Montréal
                && str_contains($html, '25,00 USD');
        });
    }

    public function test_notify_requires_date_and_link_but_save_does_not(): void
    {
        Mail::fake();
        $c = $this->consultation();

        $this->admin()->put("/admin/consultations/{$c->id}", ['action' => 'notify', 'status' => 'nouvelle', 'devise' => 'USD'])
            ->assertSessionHasErrors(['seance_programmee_at', 'lien_visio']);
        Mail::assertNothingSent();

        $this->admin()->put("/admin/consultations/{$c->id}", [
            'action' => 'save', 'status' => 'dossier_incomplet', 'devise' => 'USD', 'notes_internes' => 'Relancer pour l\'avis du psychiatre',
        ])->assertSessionHas('success');

        $c->refresh();
        $this->assertSame('dossier_incomplet', $c->status);
        $this->assertSame('gratuit', $c->statut_paiement);
        $this->assertNull($c->confirmation_envoyee_at);
        Mail::assertNothingSent();
    }

    public function test_documents_are_downloadable_by_admin_only(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('consultations/TC-X/abc.pdf', 'contenu');
        $c = $this->consultation(['documents' => [['name' => 'bilan.pdf', 'path' => 'consultations/TC-X/abc.pdf', 'size' => 7, 'mime' => 'application/pdf']]]);

        $this->get("/admin/consultations/{$c->id}/documents/0")->assertRedirect(route('login'));
        $this->admin()->get("/admin/consultations/{$c->id}/documents/0")
            ->assertOk()->assertDownload('bilan.pdf');
        $this->admin()->get("/admin/consultations/{$c->id}/documents/5")->assertNotFound();
    }
}
