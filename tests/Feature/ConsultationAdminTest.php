<?php

namespace Tests\Feature;

use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Contact;
use App\Services\Orientation\Workflow;
use Tests\OrientationTestCase;

class ConsultationAdminTest extends OrientationTestCase
{
    public function test_correcting_pending_guardian_revokes_old_links_and_old_recipient_access(): void
    {
        $c = $this->submit($this->family(), ['parent_lien' => 'tiers', 'parent_lien_autre' => 'Tante', 'guardian_name' => 'Responsable', 'guardian_email' => 'wrong@example.test']);
        $oldGuardian = Contact::findOrFail($c->guardian_contact_id);
        $notice = \App\Models\Orientation\Notice::where('title', 'Autorisation du représentant légal')->firstOrFail();
        parse_str(parse_url($notice->action_url, PHP_URL_FRAGMENT), $oldToken);
        $this->adminUser();
        $body = ['action' => 'guardian_contact', 'guardian_name' => 'Responsable corrigé', 'guardian_email' => 'correct@example.test'];
        $this->put('/admin/consultations/'.$c->id, $body)->assertSessionHasErrors('verified');
        $this->put('/admin/consultations/'.$c->id, $body + ['verified' => 1])->assertSessionHas('success');
        $this->postJson('/api/orientation/access/consume', $oldToken)->assertUnprocessable();
        $this->loginActor($oldGuardian)->getJson('/api/orientation/requests/'.$c->id)->assertNotFound();
        $newGuardian = Contact::findOrFail($c->fresh()->guardian_contact_id);
        $this->loginActor($newGuardian)->getJson('/api/orientation/requests/'.$c->id)->assertJsonPath('can_authorize', true)->assertJsonPath('full_access', false);
    }

    public function test_html_form_string_values_create_a_proposal_in_the_selected_time_zone(): void
    {
        $c = $this->submit($this->family());
        $p = $this->professional();
        $this->adminUser();
        $this->from('/admin/consultations/'.$c->id)->put('/admin/consultations/'.$c->id, ['action' => 'propose', 'professional_id' => (string) $p->id, 'starts_at' => '2030-06-10T14:30', 'expires_at' => '2030-06-09T14:30', 'timezone' => 'Africa/Kinshasa', 'duration_minutes' => '45', 'fee_usd' => '0', 'mode' => 'centre', 'location' => 'Adresse du centre de démonstration', 'purpose' => 'Premier entretien avec la famille.', 'kind' => 'orientation'])->assertRedirect('/admin/consultations/'.$c->id)->assertSessionHas('success');
        $a = Appointment::firstOrFail();
        $this->assertSame('2030-06-10 13:30:00', $a->starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2030-06-10 14:15:00', $a->ends_at->utc()->format('Y-m-d H:i:s'));
    }

    public function test_pages_require_a_real_active_administrator(): void
    {
        $this->get('/admin/consultations')->assertRedirect(route('login'));
        $this->withSession(['admin_user' => (object) ['name' => 'Faux admin']])->get('/admin/consultations')->assertRedirect(route('login'));
        $a = $this->adminUser();
        $a->update(['is_active' => false]);
        $this->get('/admin/consultations')->assertForbidden();
        $a->update(['is_active' => true, 'role' => 'parent']);
        $this->get('/admin/consultations')->assertForbidden();
    }

    public function test_list_detail_documents_and_professional_forms_render_with_filters(): void
    {
        $a = $this->family();
        $c = $this->submit($a);
        $other = $this->submit($a, ['enfant_prenom' => 'Sarah']);
        $this->adminUser();
        $this->get('/admin/consultations')->assertOk()->assertSee('David')->assertSee('Sarah');
        $this->get('/admin/consultations?search=Sarah')->assertOk()->assertSee('Sarah')->assertDontSee('David Mbuyi');
        $this->get('/admin/consultations/'.$c->id)->assertOk()->assertSee($c->reference)->assertSee('Préparer un rendez-vous');
        $this->get('/admin/consultations/professionnels')->assertOk();
        $this->get('/admin/consultations/configuration')->assertOk();
    }

    public function test_professionals_need_verified_credentials_and_can_receive_an_invitation(): void
    {
        $this->adminUser();
        $data = ['name' => 'Dr Exemple', 'email' => 'PRO@example.test', 'profession' => 'Psychiatre', 'engagement' => 'consultant', 'timezone' => 'Africa/Kinshasa', 'default_fee_usd' => 25, 'active' => 1, 'invite' => 1];
        $this->post('/admin/consultations/professionnels', $data)->assertSessionHasErrors('validated');
        $this->post('/admin/consultations/professionnels', $data + ['validated' => 1])->assertSessionHas('success');
        $p = \App\Models\Orientation\Professional::firstOrFail();
        $this->assertSame('pro@example.test', $p->email);
        $this->assertNotNull($p->validated_at);
        $this->assertSame(1, \App\Models\Orientation\AccessLink::where('actor_type', 'professional')->count());
    }

    public function test_proposal_requires_valid_location_and_cannot_confirm_before_professional_and_family_agree(): void
    {
        $family = $this->family();
        $c = $this->submit($family);
        $p = $this->professional();
        $a = $this->proposal($c, $p);
        $this->assertSame('awaiting_professional', $a->status);
        $this->loginActor($family)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'accept', 'consent' => true])->assertUnprocessable();
        $this->loginActor($p)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'accept'])->assertOk();
        $this->assertSame('proposed', $a->fresh()->status);
        $this->loginActor($family)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'accept', 'consent' => true])->assertOk();
        $this->assertSame('confirmed', $a->fresh()->status);
        $this->assertSame('rdv_programme', $c->fresh()->status);
        $this->assertSame('gratuit', $c->fresh()->statut_paiement);
        $this->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'accept', 'consent' => true])->assertUnprocessable();
    }

    public function test_overlapping_reservations_are_blocked_and_cancelled_slots_can_be_reused(): void
    {
        $c = $this->submit($this->family());
        $other = $this->submit($this->family('b@example.test'));
        $p = $this->professional();
        $a = $this->proposal($c, $p);
        try {
            $this->proposal($other, $p);
            $this->fail('Le conflit doit être refusé.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('starts_at', $e->errors());
        }
        app(Workflow::class)->cancel($a, 'Report demandé par la famille.', 'admin', 1);
        $this->assertSame('awaiting_professional', $this->proposal($other, $p)->status);
    }

    public function test_information_requests_open_supplements_and_keep_admin_notes_private(): void
    {
        $family = $this->family();
        $c = $this->submit($family);
        $this->adminUser();
        $this->put('/admin/consultations/'.$c->id, ['action' => 'notes', 'notes_internes' => 'NOTE CONFIDENTIELLE'])->assertSessionHas('success');
        $this->put('/admin/consultations/'.$c->id, ['action' => 'information', 'body' => 'Merci de préciser les difficultés rencontrées à l’école.'])->assertSessionHas('success');
        $this->assertNotNull($c->fresh()->first_response_at);
        $this->loginActor($family)->getJson('/api/orientation/requests/'.$c->id)->assertJsonPath('status', 'dossier_incomplet')->assertDontSee('NOTE CONFIDENTIELLE');
        $this->postJson('/api/orientation/requests/'.$c->id.'/supplement', ['data' => ['motif' => 'Informations précisées sur les difficultés rencontrées à l’école.', 'email' => 'interdit@example.test'], 'response' => 'Nous avons précisé les difficultés à l’école.'])->assertOk();
        $this->assertSame('nouvelle', $c->fresh()->status);
        $this->assertSame($family->email, $c->fresh()->email);
    }

    public function test_legacy_dossiers_require_explicit_authority_review_before_portal_activation(): void
    {
        $c = new ConsultationRequest;
        $c->fill($this->payload() + ['email' => 'legacy@example.test', 'whatsapp' => '', 'enfant_sexe' => 'M', 'plateforme' => 'meet']);
        $c->save();
        $this->adminUser();
        $this->put('/admin/consultations/'.$c->id, ['action' => 'adopt'])->assertSessionHasErrors('verified');
        $this->put('/admin/consultations/'.$c->id, ['action' => 'adopt', 'verified' => 1])->assertSessionHas('success');
        $this->assertNotNull($c->fresh()->orientation_contact_id);
        $this->assertSame('declared', $c->fresh()->authority_status);
    }
}
