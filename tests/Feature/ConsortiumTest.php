<?php

namespace Tests\Feature;

use App\Jobs\SendConsortiumMail;
use App\Models\Consortium\Account;
use App\Models\Consortium\Application;
use App\Models\Consortium\Notification;
use App\Models\Consortium\Structure;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConsortiumTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Garde-fou : aucun migrate:fresh et aucune connexion à la base du développeur.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array', 'session.driver' => 'array', 'mail.default' => 'array', 'queue.default' => 'sync', 'app.key' => 'base64:'.base64_encode(str_repeat('t', 32))]);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_10_31_000000_create_consortium_tables.php'])->assertSuccessful();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
        });
        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('status');
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Queue::fake();
        Storage::fake('local');
    }

    private function partner(string $email = 'partner@example.test', bool $verified = true): Account
    {
        $structure = Structure::create(['name' => 'Centre partenaire']);
        $account = $structure->accounts()->create(['name' => 'Référent', 'email' => $email, 'password' => 'MotDePasse123!']);
        if ($verified) {
            $account->forceFill(['email_verified_at' => now()])->save();
        }
        $structure->application()->create(['data' => ['official_name' => $structure->name]]);

        return $account;
    }

    private function data(): array
    {
        return ['official_name' => 'Centre partenaire', 'legal_form' => 'asbl', 'structure_type' => 'centre', 'authorization_number' => 'AUT-123', 'created_on' => '2020-01-01', 'address' => '12 avenue Exemple', 'city' => 'Kinshasa', 'provinces' => ['Kinshasa'], 'official_phone' => '+243810000000', 'official_email' => 'centre@example.test', 'representative_name' => 'Grace Mbuyi', 'representative_function' => 'Directrice', 'representative_phone' => '+243810000001', 'representative_email' => 'grace@example.test', 'mission' => 'Accompagner les enfants et les familles dans leur quotidien.', 'expertise' => ['education', 'therapy'], 'beneficiaries_children' => 20, 'beneficiaries_adults' => 0, 'staff_count' => 5, 'motivation' => 'Partager nos compétences et construire des projets communs avec NLC.', 'needs' => ['training'], 'signatory_name' => 'Grace Mbuyi', 'signatory_function' => 'Directrice', 'accuracy_consent' => true, 'data_consent' => true, 'publicity_consent' => false];
    }

    private function ready(Account $account): Application
    {
        $application = $account->structure->application;
        $application->update(['data' => $this->data()]);
        foreach (config('consortium.required_documents') as $kind) {
            $path = "consortium/{$application->id}/$kind.pdf";
            Storage::disk('local')->put($path, '%PDF-1.4 test');
            $application->documents()->create(['kind' => $kind, 'version' => 1, 'name' => "$kind.pdf", 'path' => $path, 'mime' => 'application/pdf', 'size' => 10]);
        }

        return $application;
    }

    private function admin(): User
    {
        $admin = User::create(['name' => 'NLC Admin', 'email' => 'admin@example.test', 'password' => 'MotDePasse123!', 'role' => 'admin']);
        $this->withSession(['admin_user' => $admin]);

        return $admin;
    }

    public function test_registration_verification_and_login_use_an_isolated_guard(): void
    {
        $response = $this->postJson('/api/consortium/register', ['name' => 'Grace', 'structure_name' => 'Centre Soleil', 'email' => 'GRACE@example.test', 'password' => 'MotDePasse123!', 'password_confirmation' => 'MotDePasse123!']);
        $response->assertCreated()->assertJsonPath('account.email', 'grace@example.test')->assertJsonMissingPath('account.password');
        $account = Account::firstOrFail();
        $this->assertTrue(Hash::check('MotDePasse123!', $account->password));
        $this->assertSame(0, User::count());
        $this->getJson('/api/consortium/application')->assertForbidden();
        $notification = Notification::firstOrFail();
        parse_str(parse_url($notification->action_url, PHP_URL_QUERY), $query);
        $this->assertStringNotContainsString($query['token'], $notification->getRawOriginal('action_url'));
        $this->postJson('/api/consortium/verify-email', $query)->assertOk();
        $this->postJson('/api/consortium/verify-email', $query)->assertUnprocessable();
        $this->postJson('/api/consortium/logout')->assertOk();
        $this->postJson('/api/consortium/login', ['email' => 'grace@example.test', 'password' => 'incorrect'])->assertUnprocessable();
        $this->postJson('/api/consortium/login', ['email' => 'grace@example.test', 'password' => 'MotDePasse123!'])->assertOk();
        $this->getJson('/api/consortium/me')->assertOk()->assertJsonPath('account.name', 'Grace');
        Queue::assertPushed(SendConsortiumMail::class);
    }

    public function test_guest_and_unverified_accounts_cannot_access_files_or_dossiers(): void
    {
        $this->getJson('/api/consortium/application')->assertUnauthorized();
        $account = $this->partner(verified: false);
        $this->actingAs($account, 'consortium')->putJson('/api/consortium/application', ['data' => $this->data()])->assertForbidden();
        $this->postJson('/api/consortium/application/submit')->assertForbidden();
    }

    public function test_incomplete_drafts_are_saved_but_cannot_be_submitted(): void
    {
        $account = $this->partner();
        $this->actingAs($account, 'consortium')->putJson('/api/consortium/application', ['data' => ['official_name' => 'Nouveau nom', 'expertise' => [], 'provinces' => []]])->assertOk();
        $this->assertSame('Nouveau nom', $account->structure->fresh()->name);
        $this->postJson('/api/consortium/application/submit')->assertUnprocessable()->assertJsonValidationErrors(['mission', 'data_consent']);
        $this->assertSame('draft', $account->structure->application->fresh()->status);
    }

    public function test_documents_are_private_versioned_and_validated(): void
    {
        $account = $this->partner();
        $this->actingAs($account, 'consortium');
        $first = $this->post('/api/consortium/documents', ['kind' => 'statutes', 'file' => UploadedFile::fake()->create('statuts.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('version', 1)->assertJsonMissingPath('path');
        $this->post('/api/consortium/documents', ['kind' => 'statutes', 'file' => UploadedFile::fake()->create('statuts-v2.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('version', 2);
        $this->get('/api/consortium/documents/'.$first->json('id'))->assertOk()->assertDownload('statuts.pdf');
        $this->post('/api/consortium/documents', ['kind' => 'statutes', 'file' => UploadedFile::fake()->create('script.php', 10, 'text/x-php')], ['Accept' => 'application/json'])->assertUnprocessable();
        $this->post('/api/consortium/documents', ['kind' => 'statutes', 'file' => UploadedFile::fake()->create('gros.pdf', 10241, 'application/pdf')], ['Accept' => 'application/json'])->assertUnprocessable();
        $other = $this->partner('other@example.test');
        $this->flushSession();
        $this->actingAs($other, 'consortium')->getJson('/api/consortium/documents/'.$first->json('id'))->assertNotFound();
        $this->getJson('/api/consortium/application')->assertOk()->assertJsonCount(0, 'documents');
    }

    public function test_submission_freezes_a_snapshot_and_rejects_duplicate_submission(): void
    {
        $account = $this->partner();
        $application = $this->ready($account);
        $this->actingAs($account, 'consortium')->postJson('/api/consortium/application/submit')->assertOk()->assertJsonPath('status', 'submitted')->assertJsonPath('revision', 1);
        $this->putJson('/api/consortium/application', ['data' => ['official_name' => 'Modifié']])->assertConflict();
        $this->postJson('/api/consortium/application/submit')->assertUnprocessable();
        $this->assertSame(1, $application->events()->where('type', 'submitted')->count());
        $this->assertSame($this->data()['mission'], $application->fresh()->submitted_data['mission']);
        $this->assertSame(2, Notification::count());
    }

    public function test_required_documents_and_consent_are_checked_by_the_server(): void
    {
        $account = $this->partner();
        $application = $account->structure->application;
        $application->update(['data' => $this->data()]);
        $this->actingAs($account, 'consortium')->postJson('/api/consortium/application/submit')->assertUnprocessable()->assertJsonValidationErrors('documents');
        $application->update(['data' => array_replace($this->data(), ['accuracy_consent' => false])]);
        $this->postJson('/api/consortium/application/submit')->assertUnprocessable()->assertJsonValidationErrors('accuracy_consent');
    }

    public function test_only_actual_administrators_can_open_administration(): void
    {
        $this->get('/admin/consortium')->assertRedirect(route('login'));
        $this->withSession(['admin_user' => (object) ['id' => 999]])->get('/admin/consortium')->assertForbidden();
        $parent = User::create(['name' => 'Parent', 'email' => 'parent@example.test', 'password' => 'MotDePasse123!', 'role' => 'parent']);
        $this->withSession(['admin_user' => $parent])->get('/admin/consortium')->assertForbidden();
        $this->admin();
        $this->get('/admin/consortium')->assertOk()->assertSee('Adhésions au consortium');
    }

    public function test_admin_requests_complements_and_partner_must_upload_a_new_version(): void
    {
        $account = $this->partner();
        $application = $this->ready($account);
        $this->actingAs($account, 'consortium')->postJson('/api/consortium/application/submit')->assertOk();
        $this->admin();
        $this->put(route('admin.consortium.update', $application), ['action' => 'request_information', 'message' => 'Merci de transmettre une autorisation plus lisible.', 'document_kinds' => ['authorization'], 'fields' => ['mission']])->assertSessionHas('success');
        $this->assertSame('needs_information', $application->fresh()->status);
        $this->postJson('/api/consortium/application/submit', ['response' => 'Voici les éléments demandés.'])->assertUnprocessable()->assertJsonValidationErrors('documents');
        $this->post('/api/consortium/documents', ['kind' => 'authorization', 'file' => UploadedFile::fake()->create('autorisation.pdf', 10, 'application/pdf')], ['Accept' => 'application/json'])->assertCreated();
        $this->postJson('/api/consortium/application/submit', ['response' => 'Voici une copie lisible de notre autorisation.'])->assertOk()->assertJsonPath('status', 'under_review')->assertJsonPath('revision', 2);
        $this->assertNotNull($application->informationRequests()->first()->responded_at);
        $this->assertSame(1, $application->messages()->count());
    }

    public function test_approval_requires_reviewed_documents_including_signed_agreement(): void
    {
        $account = $this->partner();
        $application = $this->ready($account);
        $application->forceFill(['status' => 'under_review'])->save();
        $this->admin();
        $url = route('admin.consortium.update', $application);
        $this->put($url, ['action' => 'accept', 'message' => 'Votre dossier est accepté par le consortium.'])->assertSessionHasErrors('documents');
        $application->documents()->update(['status' => 'accepted']);
        $application->documents()->create(['kind' => 'signed_agreement', 'version' => 1, 'name' => 'accord.pdf', 'path' => 'consortium/accord.pdf', 'mime' => 'application/pdf', 'size' => 10, 'status' => 'accepted']);
        $this->put($url, ['action' => 'accept', 'message' => 'Votre adhésion au consortium est acceptée.'])->assertSessionHas('success');
        $this->assertSame('accepted', $application->fresh()->status);
        $this->put($url, ['action' => 'reject', 'message' => 'Une seconde décision ne doit pas être possible.'])->assertConflict();
        $this->assertNotNull($application->fresh()->decided_at);
    }

    public function test_private_notes_and_other_accounts_notifications_are_not_exposed(): void
    {
        $account = $this->partner();
        $application = $this->ready($account);
        $application->forceFill(['internal_notes' => 'Note strictement interne'])->save();
        $other = $this->partner('other@example.test');
        $notification = Notification::create(['account_id' => $other->id, 'recipient' => $other->email, 'title' => 'Secret autre compte', 'body' => 'Message privé', 'action_url' => 'https://example.test']);
        $this->actingAs($account, 'consortium')->getJson('/api/consortium/application')->assertOk()->assertJsonMissingPath('internal_notes')->assertDontSee('Note strictement interne');
        $this->getJson('/api/consortium/notifications')->assertJsonCount(0);
        $this->postJson('/api/consortium/notifications/'.$notification->id.'/read')->assertNotFound();
    }

    public function test_messages_and_withdrawal_are_kept_in_the_dossier(): void
    {
        $account = $this->partner();
        $application = $this->ready($account);
        $this->actingAs($account, 'consortium')->postJson('/api/consortium/messages', ['body' => 'Bonjour, comment compléter notre dossier ?'])->assertOk()->assertJsonCount(1, 'messages');
        $this->postJson('/api/consortium/application/withdraw')->assertOk()->assertJsonPath('status', 'withdrawn');
        $this->postJson('/api/consortium/application/submit')->assertUnprocessable();
        $this->assertSame(1, $application->events()->where('type', 'withdrawn')->count());
    }

    public function test_password_reset_uses_expiring_broker_tokens(): void
    {
        $account = $this->partner();
        $this->postJson('/api/consortium/forgot-password', ['email' => $account->email])->assertOk();
        parse_str(parse_url(Notification::firstOrFail()->action_url, PHP_URL_QUERY), $query);
        $this->postJson('/api/consortium/reset-password', $query + ['password' => 'NouveauPasse123!', 'password_confirmation' => 'NouveauPasse123!'])->assertOk();
        $this->assertTrue(Hash::check('NouveauPasse123!', $account->fresh()->password));
        $this->postJson('/api/consortium/reset-password', $query + ['password' => 'EncorePasse123!', 'password_confirmation' => 'EncorePasse123!'])->assertUnprocessable();
    }

    public function test_sensitive_account_changes_require_current_password_and_reverify_email(): void
    {
        $account = $this->partner();
        $this->actingAs($account, 'consortium')->patchJson('/api/consortium/account', ['name' => 'Nouveau référent', 'email' => 'nouveau@example.test'])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->patchJson('/api/consortium/account', ['name' => 'Nouveau référent', 'email' => 'nouveau@example.test', 'current_password' => 'MotDePasse123!'])->assertOk()->assertJsonPath('account.email_verified_at', null);
        $this->getJson('/api/consortium/application')->assertForbidden();
        $this->assertSame('nouveau@example.test', $account->fresh()->email);
    }

    public function test_expired_verification_links_and_emails_are_not_reused(): void
    {
        $account = $this->partner(verified: false);
        $account->sendVerification();
        $notification = Notification::firstOrFail();
        parse_str(parse_url($notification->action_url, PHP_URL_QUERY), $query);
        $this->travel(2)->hours();
        $this->postJson('/api/consortium/verify-email', $query)->assertUnprocessable();
        Mail::shouldReceive('send')->never();
        (new SendConsortiumMail($notification->id))->handle();
        $this->assertSame('expired', $notification->fresh()->email_status);
    }

    public function test_complement_reminders_are_not_repeated(): void
    {
        $account = $this->partner();
        $application = $this->ready($account);
        $application->forceFill(['status' => 'needs_information'])->save();
        $application->informationRequests()->create(['message' => 'Une pièce est attendue', 'fields' => [], 'document_kinds' => ['authorization'], 'due_on' => now()->addDay()->format('Y-m-d')]);
        $this->artisan('consortium:remind')->assertSuccessful();
        $this->artisan('consortium:remind')->assertSuccessful();
        $this->assertSame(1, Notification::count());
        $this->assertNotNull($application->informationRequests()->first()->reminded_at);
    }

    public function test_summary_is_downloadable_without_private_notes(): void
    {
        $account = $this->partner();
        $application = $this->ready($account);
        $application->forceFill(['internal_notes' => 'NE PAS EXPOSER'])->save();
        $response = $this->actingAs($account, 'consortium')->get('/api/consortium/application/summary')->assertOk()->assertDownload($application->reference.'.txt');
        $this->assertStringContainsString($application->reference, $response->streamedContent());
        $this->assertStringNotContainsString('NE PAS EXPOSER', $response->streamedContent());
    }

    public function test_inactive_administrator_cannot_instruct_applications(): void
    {
        $admin = $this->admin();
        $admin->forceFill(['is_active' => 0])->save();
        $this->get('/admin/consortium')->assertForbidden();
    }

    public function test_admin_login_returns_to_the_requested_consortium_page(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'MotDePasse123!', 'role' => 'admin']);
        $roleId = DB::table('roles')->insertGetId(['name' => 'admin']);
        DB::table('role_user')->insert(['user_id' => $admin->id, 'role_id' => $roleId]);
        $this->get('/admin/consortium')->assertRedirect(route('login'));
        $this->post('/login', ['email' => $admin->email, 'password' => 'MotDePasse123!'])->assertRedirect(route('admin.consortium.index'));
        $this->get('/admin/consortium')->assertOk();
    }

    public function test_switching_partner_accounts_after_logout_does_not_keep_old_password_session(): void
    {
        $first = $this->partner();
        $second = $this->partner('second@example.test');
        $this->postJson('/api/consortium/login', ['email' => $first->email, 'password' => 'MotDePasse123!'])->assertOk();
        $this->getJson('/api/consortium/me')->assertOk();
        $this->postJson('/api/consortium/logout')->assertOk();
        $this->postJson('/api/consortium/login', ['email' => $second->email, 'password' => 'MotDePasse123!'])->assertOk();
        $this->getJson('/api/consortium/application')->assertOk()->assertJsonPath('structure_id', $second->structure_id);
    }

    public function test_mail_outbox_marks_success_and_retains_failed_delivery(): void
    {
        $notification = Notification::create(['recipient' => 'partner@example.test', 'title' => 'Dossier reçu', 'body' => 'Merci pour votre demande.', 'action_url' => 'https://example.test']);
        Mail::shouldReceive('send')->once()->andReturnNull();
        $job = new SendConsortiumMail($notification->id);
        $job->handle();
        $job->handle();
        $this->assertSame('sent', $notification->fresh()->email_status);
        $this->assertNotNull($notification->fresh()->sent_at);
        $failed = Notification::create(['recipient' => 'partner@example.test', 'title' => 'À relancer', 'body' => 'Une alerte', 'action_url' => 'https://example.test']);
        (new SendConsortiumMail($failed->id))->failed(new \RuntimeException('Erreur SMTP'));
        $this->assertSame('failed', $failed->fresh()->email_status);
    }
}
