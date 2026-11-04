<?php

namespace Tests\Feature;

use App\Jobs\SendOrientationMail;
use App\Jobs\VerifyOrientationPayment;
use App\Models\Orientation\Notice;
use App\Models\Orientation\Payment;
use App\Services\Orientation\Access;
use App\Services\Orientation\Maintenance;
use App\Services\Orientation\Payments;
use App\Services\Orientation\Workflow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\OrientationTestCase;

class OrientationWorkflowTest extends OrientationTestCase
{
    public function test_expiring_or_cancelling_an_old_quote_does_not_overwrite_a_new_reservation(): void
    {
        $c = $this->submit($this->family());
        $p = $this->professional();
        $old = $this->proposal($c, $p);
        $this->travel(3)->days();
        $new = $this->proposal($c->fresh(), $p);
        app(Maintenance::class)->run();
        $this->assertSame('expired', $old->fresh()->status);
        $this->assertSame('awaiting_professional', $new->fresh()->status);
        $this->assertSame('attribution_en_attente', $c->fresh()->status);
        $c2 = $this->submit($this->family('b@example.test'));
        $p2 = $this->professional();
        $old2 = $this->proposal($c2, $p2);
        $this->travel(3)->days();
        $new2 = $this->proposal($c2->fresh(), $p2);
        app(Workflow::class)->cancel($old2, 'Ancienne proposition remplacée.', 'admin', 1);
        $this->assertSame('awaiting_professional', $new2->fresh()->status);
        $this->assertSame('attribution_en_attente', $c2->fresh()->status);
    }

    public function test_stale_confirmation_mail_and_stale_tab_actions_are_discarded(): void
    {
        $family = $this->family();
        $c = $this->submit($family);
        $p = $this->professional();
        $a = $this->proposal($c, $p);
        app(Workflow::class)->professionalDecision($a, $p, true);
        app(Workflow::class)->accept($a->fresh(), $family);
        $notice = Notice::where('actor_type', 'family')->where('title', 'Rendez-vous confirmé')->firstOrFail();
        app(Workflow::class)->cancel($a->fresh(), 'Report demandé pour la démonstration.', 'admin', 1);
        Mail::shouldReceive('send')->never();
        (new SendOrientationMail($notice->id))->handle();
        $this->assertSame('obsolete', $notice->fresh()->email_status);
        $this->loginActor($family)->withHeader('X-Orientation-Actor', 'professional:'.$p->id)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'accept', 'consent' => true])->assertStatus(409);
    }

    private function paidProposal(): array
    {
        config(['orientation.manual_payment_instructions' => 'Paiement électronique sur le compte de test NLC.']);
        $family = $this->family();
        $c = $this->submit($family);
        $p = $this->professional();
        $a = $this->proposal($c, $p, 25);
        app(Workflow::class)->professionalDecision($a, $p, true);
        app(Workflow::class)->accept($a->fresh(), $family);

        return [$family, $c, $p, $a->fresh()];
    }

    public function test_paid_appointment_is_confirmed_only_after_verified_receipt_and_is_idempotent(): void
    {
        [$family,$c,$p,$a] = $this->paidProposal();
        $this->loginActor($family);
        $r = $this->postJson('/api/orientation/appointments/'.$a->id.'/pay', ['gateway' => 'manual'])->assertOk();
        $payment = Payment::findOrFail($r->json('payment_id'));
        $this->postJson('/api/orientation/appointments/'.$a->id.'/pay', ['gateway' => 'manual'])->assertJsonPath('payment_id', $payment->id);
        $this->assertSame(1, Payment::count());
        $this->postJson('/api/orientation/payments/'.$payment->id.'/report', ['transaction_reference' => 'TX-EXEMPLE-123'])->assertOk();
        $this->assertSame('awaiting_payment', $a->fresh()->status);
        $this->adminUser();
        $body = ['action' => 'payment_verify', 'payment_id' => $payment->id, 'transaction_reference' => 'TX-EXEMPLE-123', 'review_note' => 'Règlement constaté sur le compte marchand de démonstration.'];
        $this->put('/admin/consultations/'.$c->id, $body)->assertSessionHasErrors('verified');
        $this->put('/admin/consultations/'.$c->id, $body + ['verified' => 1])->assertSessionHas('success');
        $this->assertSame('confirmed', $a->fresh()->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paye', $c->fresh()->statut_paiement);
        $count = Notice::count();
        app(Workflow::class)->paid($payment->fresh(), 1, 'TX-EXEMPLE-123');
        $this->assertSame($count, Notice::count());
    }

    public function test_late_payment_is_recorded_for_regularization_without_reclaiming_the_slot(): void
    {
        [$family,$c,$p,$a] = $this->paidProposal();
        $result = app(Payments::class)->start($a, 'manual');
        $payment = Payment::findOrFail($result['payment_id']);
        $this->travel(3)->days();
        app(Maintenance::class)->run();
        $this->assertSame('expired', $a->fresh()->status);
        app(Workflow::class)->paid($payment, 1, 'LATE-TRANSACTION');
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('expired', $a->fresh()->status);
        $this->assertSame('review', $a->fresh()->refund_status);
        $this->assertNull($a->fresh()->confirmed_at);
    }

    public function test_unassigned_professionals_and_pending_assignees_cannot_access_medical_data(): void
    {
        $c = $this->submit($this->family());
        $p = $this->professional();
        $a = $this->proposal($c, $p);
        $this->loginActor($p)->getJson('/api/orientation/requests/'.$c->id)->assertOk()->assertJsonPath('full_access', false)->assertJsonMissingPath('data.motif');
        $this->getJson('/api/orientation/requests/'.$c->id.'/documents/0')->assertNotFound();
        $other = $this->professional();
        $this->loginActor($other)->getJson('/api/orientation/requests/'.$c->id)->assertNotFound();
        $this->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'accept'])->assertNotFound();
        app(Workflow::class)->professionalDecision($a, $p, true);
        $this->loginActor($p)->getJson('/api/orientation/requests/'.$c->id)->assertJsonPath('full_access', true);
    }

    public function test_clinical_notes_are_encrypted_and_private_and_summary_is_published_explicitly(): void
    {
        $family = $this->family();
        $c = $this->submit($family);
        $p = $this->professional();
        $a = $this->proposal($c, $p);
        app(Workflow::class)->professionalDecision($a, $p, true);
        app(Workflow::class)->accept($a->fresh(), $family);
        $this->loginActor($p)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'notes', 'clinical_notes' => ['concerns' => 'NOTE CLINIQUE PRIVÉE'], 'shared_summary' => 'Ne pas publier ce brouillon.'])->assertOk();
        $this->assertNull($a->fresh()->shared_summary);
        $this->assertStringNotContainsString('NOTE CLINIQUE', $a->fresh()->getRawOriginal('clinical_notes'));
        $this->loginActor($family)->getJson('/api/orientation/requests/'.$c->id)->assertDontSee('NOTE CLINIQUE')->assertDontSee('Ne pas publier');
        $this->loginActor($p)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'complete', 'clinical_notes' => [], 'shared_summary' => 'Les prochaines étapes sont présentées à la famille.'])->assertUnprocessable();
        $this->travel(6)->days();
        $this->loginActor($p)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'complete', 'clinical_notes' => ['concerns' => 'NOTE CLINIQUE PRIVÉE'], 'shared_summary' => 'Les prochaines étapes sont présentées à la famille.'])->assertOk();
        $this->loginActor($family)->getJson('/api/orientation/requests/'.$c->id)->assertJsonPath('appointments.0.status', 'completed')->assertJsonPath('appointments.0.shared_summary', 'Les prochaines étapes sont présentées à la famille.')->assertDontSee('NOTE CLINIQUE');
    }

    public function test_disabled_or_changed_professional_identity_invalidates_session_and_old_access_links(): void
    {
        $p = $this->professional();
        app(Access::class)->issue('professional', $p);
        $notice = Notice::latest('id')->first();
        parse_str(parse_url($notice->action_url, PHP_URL_FRAGMENT), $query);
        $this->loginActor($p);
        $p->update(['email' => 'new@example.test']);
        $this->getJson('/api/orientation/me')->assertUnauthorized();
        $this->postJson('/api/orientation/access/consume', $query)->assertForbidden();
        $p->update(['active' => false]);
        $this->loginActor($p)->getJson('/api/orientation/me')->assertUnauthorized();
    }

    public function test_cancelled_paid_session_requires_financial_review_and_new_quote_acceptance(): void
    {
        [$family,$c,$p,$a] = $this->paidProposal();
        $result = app(Payments::class)->start($a, 'manual');
        $payment = Payment::findOrFail($result['payment_id']);
        app(Workflow::class)->paid($payment, 1, 'PAID-TX-123');
        $this->loginActor($family)->postJson('/api/orientation/appointments/'.$a->id, ['action' => 'cancel', 'reason' => 'Empêchement de la famille, un report est demandé.'])->assertOk();
        $this->assertSame('review', $a->fresh()->refund_status);
        $new = $this->proposal($c->fresh(), $p, 25);
        $this->assertNull($new->accepted_at);
        $this->assertSame(0, $new->payments()->count());
    }

    public function test_guardian_refusal_blocks_assignment_and_detailed_access(): void
    {
        $requester = $this->family();
        $c = $this->submit($requester, ['parent_lien' => 'tiers', 'parent_lien_autre' => 'Tante', 'guardian_name' => 'Responsable', 'guardian_email' => 'guardian@example.test']);
        $guardian = \App\Models\Orientation\Contact::findOrFail($c->guardian_contact_id);
        $this->loginActor($guardian)->postJson('/api/orientation/requests/'.$c->id.'/guardian', ['approve' => false, 'authority_consent' => true])->assertOk();
        $this->loginActor($requester)->getJson('/api/orientation/requests/'.$c->id)->assertJsonPath('full_access', false)->assertJsonPath('status', 'autorisation_refusee');
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->proposal($c->fresh(), $this->professional());
    }

    public function test_sla_alerts_and_appointment_reminders_are_not_duplicated(): void
    {
        $c = $this->submit($this->family());
        $this->travel(73)->hours();
        app(Maintenance::class)->run();
        $count = Notice::where('title', 'Délai de 72 heures dépassé')->count();
        $this->assertSame(1, $count);
        app(Maintenance::class)->run();
        $this->assertSame($count, Notice::where('title', 'Délai de 72 heures dépassé')->count());
        $family = \App\Models\Orientation\Contact::find($c->orientation_contact_id);
        $p = $this->professional();
        $a = $this->proposal($c->fresh(), $p);
        app(Workflow::class)->professionalDecision($a, $p, true);
        app(Workflow::class)->accept($a->fresh(), $family);
        $this->travel(4)->days();
        app(Maintenance::class)->run();
        $count = Notice::where('title', 'Rappel de votre rendez-vous')->count();
        $this->assertSame(1, $count);
        app(Maintenance::class)->run();
        $this->assertSame($count, Notice::where('title', 'Rappel de votre rendez-vous')->count());
    }

    public function test_callback_cannot_assert_payment_success_and_incomplete_provider_proof_is_not_paid(): void
    {
        [$family,$c,$p,$a] = $this->paidProposal();
        config(['orientation.maxicash_enabled' => true, 'services.maxicash.merchant_id' => 'test-id', 'services.maxicash.merchant_password' => 'test-password']);
        Http::fake(['*/Integration/PayEntryWeb' => Http::response(['ResponseStatus' => 'success', 'LogID' => '12345']), '*/Integration/CheckPaymentStatusByReference' => Http::response(['ResponseStatus' => 'success', 'ResponseData' => 'pending'])]);
        $result = app(Payments::class)->start($a, 'maxicash');
        $payment = Payment::findOrFail($result['payment_id']);
        $this->assertStringContainsString('logid=12345', $result['redirect_url']);
        $this->postJson('/api/orientation/payment/callback', ['Reference' => $payment->reference, 'Status' => 'paid', 'Amount' => 2500])->assertOk();
        Queue::assertPushed(VerifyOrientationPayment::class);
        $this->assertSame('pending', $payment->fresh()->status);
        app(Payments::class)->verify($payment);
        $this->assertSame('verification_required', $payment->fresh()->status);
        $this->assertSame('awaiting_payment', $a->fresh()->status);
        Http::assertSent(fn ($req) => str_ends_with($req->url(), '/PayEntryWeb') && $req['Amount'] === '2500' && $req['Currency'] === 'maxiDollar' && ! isset($req['FirstName']));
    }

    public function test_provider_proof_must_match_reference_amount_currency_and_transaction(): void
    {
        [$family,$c,$p,$a] = $this->paidProposal();
        config(['orientation.maxicash_enabled' => true, 'services.maxicash.merchant_id' => 'test-id', 'services.maxicash.merchant_password' => 'test-password']);
        $payment = Payment::create(['appointment_id' => $a->id, 'reference' => 'ORP-ABCDEFGHIJKLMNOPQRST', 'gateway' => 'maxicash', 'amount_cents' => 2500]);
        Http::fakeSequence()->push(['ResponseStatus' => 'success', 'ResponseData' => json_encode(['Reference' => $payment->reference, 'Amount' => '2499', 'Currency' => 'maxiDollar', 'Status' => 'paid', 'TransactionID' => 'PROVIDER-TX-123'])])->push(['ResponseStatus' => 'success', 'ResponseData' => json_encode(['Reference' => $payment->reference, 'Amount' => '2500', 'Currency' => 'maxiDollar', 'Status' => 'paid', 'TransactionID' => 'PROVIDER-TX-123'])]);
        app(Payments::class)->verify($payment);
        $this->assertNotSame('paid', $payment->fresh()->status);
        app(Payments::class)->verify($payment->fresh());
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('confirmed', $a->fresh()->status);
    }

    public function test_expired_login_mail_is_not_sent(): void
    {
        $notice = Notice::create(['recipient' => 'fake@example.test', 'title' => 'Test', 'body' => 'Notification de test', 'expires_at' => now()->subMinute()]);
        Mail::shouldReceive('send')->never();
        (new SendOrientationMail($notice->id))->handle();
        $this->assertSame('expired', $notice->fresh()->email_status);
    }

    public function test_paid_quote_is_unavailable_without_a_configured_electronic_payment(): void
    {
        config(['orientation.maxicash_enabled' => false, 'orientation.manual_payment_instructions' => null]);
        $c = $this->submit($this->family());
        $p = $this->professional();
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->proposal($c, $p, 25);
    }
}
