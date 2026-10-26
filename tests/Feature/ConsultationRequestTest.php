<?php

namespace Tests\Feature;

use App\Mail\ConsultationRequestNotificationMail;
use App\Mail\ConsultationRequestReceivedMail;
use App\Models\ConsultationRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConsultationRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Les anciennes migrations du projet ne tournent pas sur SQLite : on ne charge que celle-ci
        $this->artisan('migrate:fresh', ['--path' => [
            'database/migrations/2026_10_26_000000_create_consultation_requests_table.php',
            'database/migrations/2026_10_27_000000_add_scheduling_fields_to_consultation_requests_table.php',
        ]]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'parent_nom' => 'Mbuyi', 'parent_prenom' => 'Grace', 'parent_lien' => 'mere',
            'pays' => 'France', 'ville' => 'Paris', 'fuseau_horaire' => 'Europe/Paris',
            'email' => 'grace@example.com', 'whatsapp' => '+33612345678', 'langue_seance' => 'francais',
            'enfant_nom' => 'Mbuyi', 'enfant_prenom' => 'David', 'enfant_sexe' => 'M',
            'enfant_date_naissance' => '2020-03-14',
            'motif' => 'Retard de langage et difficultés à jouer avec les autres enfants.',
            'observations' => ['regard' => 'parfois', 'inconnu' => 'souvent'],
            'creneaux' => [['start' => now()->addDays(3)->toIso8601String()]],
            'plateforme' => 'meet',
            'consentement_donnees' => true, 'engagement_avis_psychiatre' => true, 'acceptation_conditions' => true,
        ], $overrides);
    }

    public function test_parent_can_submit_a_consultation_request_with_documents(): void
    {
        Mail::fake();
        Storage::fake('local');

        $response = $this->post('/api/consultations', [
            'payload' => json_encode($this->payload()),
            'documents' => [UploadedFile::fake()->create('bilan.pdf', 500, 'application/pdf')],
        ], ['Accept' => 'application/json']);

        $response->assertCreated()->assertJsonPath('success', true);

        $consultation = ConsultationRequest::firstOrFail();
        $this->assertSame($response->json('reference'), $consultation->reference);
        $this->assertSame('nouvelle', $consultation->status);
        $this->assertSame(['regard' => 'parfois'], $consultation->observations);
        $this->assertNotNull($consultation->consented_at);
        $this->assertCount(1, $consultation->documents);
        Storage::disk('local')->assertExists($consultation->documents[0]['path']);

        Mail::assertSent(ConsultationRequestReceivedMail::class, fn ($mail) => $mail->hasTo('grace@example.com'));
        Mail::assertSent(ConsultationRequestNotificationMail::class, fn ($mail) => $mail->hasTo(config('services.nlc.secretariat_email')));
    }

    public function test_emails_render_both_time_zones(): void
    {
        $consultation = ConsultationRequest::create($this->payload([
            'creneaux' => [['start' => '2030-06-10T09:00:00Z']],
            'consented_at' => now(),
        ]));

        $html = (new ConsultationRequestNotificationMail($consultation))->render();
        $this->assertStringContainsString('10:00', $html); // Kinshasa = UTC+1
        $this->assertStringContainsString('11:00', $html); // Paris l'été = UTC+2

        $this->assertStringContainsString($consultation->reference, (new ConsultationRequestReceivedMail($consultation))->render());
    }

    public function test_consents_and_required_fields_are_enforced(): void
    {
        $response = $this->postJson('/api/consultations', [
            'payload' => json_encode($this->payload([
                'consentement_donnees' => false,
                'motif' => '',
                'creneaux' => [['start' => now()->subDay()->toIso8601String()]],
            ])),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['consentement_donnees', 'motif', 'creneaux.0.start']);
        $this->assertSame(0, ConsultationRequest::count());
    }

    public function test_access_token_is_never_exposed(): void
    {
        $consultation = ConsultationRequest::create($this->payload(['consented_at' => now()]));

        $this->assertSame(64, strlen($consultation->access_token));
        $this->assertArrayNotHasKey('access_token', $consultation->toArray());
    }
}
