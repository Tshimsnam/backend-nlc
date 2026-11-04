<?php

namespace Tests\Feature;

use App\Models\ConsultationRequest;
use App\Models\Orientation\AccessLink;
use App\Models\Orientation\Draft;
use App\Models\Orientation\Notice;
use App\Services\Orientation\Access;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\OrientationTestCase;

class ConsultationRequestTest extends OrientationTestCase
{
    public function test_verified_contact_is_required_even_on_legacy_submission_url(): void
    {
        $this->postJson('/api/consultations', ['payload' => json_encode($this->payload())])->assertUnauthorized();
        $this->assertSame(0, ConsultationRequest::count());
    }

    public function test_access_links_are_hashed_single_use_and_expire_without_automatic_get_login(): void
    {
        $this->postJson('/api/orientation/access/request', ['email' => 'GRACE@example.test', 'name' => 'Grace', 'type' => 'family'])->assertOk();
        $notice = Notice::firstOrFail();
        parse_str(parse_url($notice->action_url, PHP_URL_FRAGMENT), $query);
        $this->assertNotSame($query['token'], AccessLink::first()->token_hash);
        $this->assertStringNotContainsString($query['token'], $notice->getRawOriginal('action_url'));
        $this->getJson('/api/orientation/me')->assertUnauthorized();
        $this->postJson('/api/orientation/access/consume', $query)->assertOk()->assertJsonPath('actor.email', 'grace@example.test');
        $this->postJson('/api/orientation/access/consume', $query)->assertUnprocessable();
        $this->postJson('/api/orientation/logout')->assertOk();
        app(Access::class)->issue('family', \App\Models\Orientation\Contact::first());
        parse_str(parse_url(Notice::latest('id')->first()->action_url, PHP_URL_FRAGMENT), $query);
        $this->travel(31)->minutes();
        $this->postJson('/api/orientation/access/consume', $query)->assertUnprocessable();
    }

    public function test_parent_can_submit_without_psychiatric_opinion_with_private_documents(): void
    {
        $actor = $this->family();
        $this->loginActor($actor);
        $this->post('/api/consultations', ['payload' => json_encode($this->payload(['email' => 'spoof@example.test'])), 'submission_key' => (string) Str::uuid(), 'documents' => [UploadedFile::fake()->create('bilan.pdf', 10, 'application/pdf')]], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('full_access', true);
        $c = ConsultationRequest::firstOrFail();
        $this->assertSame($actor->email, $c->email);
        $this->assertFalse($c->engagement_avis_psychiatre);
        $this->assertEquals(72, $c->created_at->diffInHours($c->processing_due_at));
        Storage::disk('local')->assertExists($c->documents[0]['path']);
        $this->getJson('/api/orientation/requests/'.$c->id)->assertJsonMissingPath('notes_internes')->assertJsonMissingPath('access_token')->assertJsonMissingPath('documents.0.path');
        foreach (Notice::all() as $n) {
            $this->assertStringNotContainsString($c->motif, $n->body);
            $this->assertStringNotContainsString('David', $n->body);
        }
    }

    public function test_consents_minority_and_future_availability_are_enforced(): void
    {
        $actor = $this->family();
        $this->loginActor($actor);
        $this->postJson('/api/orientation/requests', ['payload' => json_encode($this->payload(['authority_consent' => false, 'consentement_donnees' => false, 'enfant_date_naissance' => now()->subYears(18)->toDateString(), 'creneaux' => [['start' => now()->subDay()->toIso8601String()]]])), 'submission_key' => (string) Str::uuid()])->assertUnprocessable()->assertJsonValidationErrors(['authority_consent', 'consentement_donnees', 'enfant_date_naissance', 'creneaux.0.start']);
        $this->assertSame(0, ConsultationRequest::count());
    }

    public function test_draft_is_encrypted_and_isolated_between_families(): void
    {
        $a = $this->family();
        $b = $this->family('other@example.test');
        $this->loginActor($a)->putJson('/api/orientation/draft', ['data' => ['motif' => 'CONFIDENTIEL', 'inconnu' => 'ne doit pas être conservé']])->assertOk();
        $raw = Draft::firstOrFail()->getRawOriginal('data');
        $this->assertStringNotContainsString('CONFIDENTIEL', $raw);
        $this->getJson('/api/orientation/draft')->assertJsonPath('data.motif', 'CONFIDENTIEL')->assertJsonMissingPath('data.inconnu');
        $this->loginActor($b)->getJson('/api/orientation/draft')->assertJsonPath('data', []);
    }

    public function test_third_party_needs_distinct_guardian_and_cannot_read_documents_before_approval(): void
    {
        $a = $this->family();
        $this->loginActor($a);
        $extra = ['parent_lien' => 'tiers', 'parent_lien_autre' => 'Tante', 'guardian_name' => 'Responsable légal', 'guardian_email' => strtoupper($a->email)];
        $this->postJson('/api/orientation/requests', ['payload' => json_encode($this->payload($extra)), 'submission_key' => (string) Str::uuid()])->assertUnprocessable()->assertJsonValidationErrors('guardian_email');
        $extra['guardian_email'] = 'guardian@example.test';
        $c = $this->submit($a, $extra);
        $this->getJson('/api/orientation/requests/'.$c->id)->assertOk()->assertJsonPath('full_access', false)->assertJsonMissingPath('data.motif');
        $this->getJson('/api/orientation/requests/'.$c->id.'/documents/0')->assertNotFound();
        $this->postJson('/api/orientation/requests/'.$c->id.'/guardian', ['approve' => true, 'authority_consent' => true])->assertNotFound();
        $guardian = \App\Models\Orientation\Contact::findOrFail($c->guardian_contact_id);
        $this->loginActor($guardian)->postJson('/api/orientation/requests/'.$c->id.'/guardian', ['approve' => true, 'authority_consent' => true])->assertOk();
        $this->loginActor($a)->getJson('/api/orientation/requests/'.$c->id)->assertJsonPath('full_access', true);
    }

    public function test_duplicate_submission_does_not_create_duplicate_dossiers_or_notifications(): void
    {
        $a = $this->family();
        $this->loginActor($a);
        $body = ['payload' => json_encode($this->payload()), 'submission_key' => (string) Str::uuid()];
        $first = $this->postJson('/api/orientation/requests', $body)->assertCreated()->json('id');
        $count = Notice::count();
        $this->postJson('/api/orientation/requests', $body)->assertCreated()->assertJsonPath('id', $first);
        $this->assertSame(1, ConsultationRequest::count());
        $this->assertSame($count, Notice::count());
    }

    public function test_files_and_notifications_are_scoped_to_the_authorized_family(): void
    {
        $a = $this->family();
        $c = $this->submit($a);
        $this->post('/api/orientation/requests/'.$c->id.'/documents', ['file' => UploadedFile::fake()->create('bilan.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertCreated();
        $this->post('/api/orientation/requests/'.$c->id.'/documents', ['file' => UploadedFile::fake()->create('script.php', 10, 'text/x-php')], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->get('/api/orientation/requests/'.$c->id.'/documents/0')->assertOk()->assertDownload('bilan.pdf');
        $notice = Notice::where('actor_id', $a->id)->firstOrFail();
        $this->getJson('/api/orientation/notifications')->assertJsonMissingPath('0.action_url');
        $this->loginActor($this->family('other@example.test'))->getJson('/api/orientation/requests/'.$c->id)->assertNotFound();
        $this->getJson('/api/orientation/requests/'.$c->id.'/documents/0')->assertNotFound();
        $this->postJson('/api/orientation/notifications/'.$notice->id.'/read')->assertNotFound();
    }
}
