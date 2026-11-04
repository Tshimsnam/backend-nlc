<?php

namespace Tests;

use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Contact;
use App\Models\Orientation\Professional;
use App\Models\User;
use App\Services\Orientation\Workflow;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class OrientationTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array', 'session.driver' => 'array', 'mail.default' => 'array', 'queue.default' => 'sync', 'app.key' => 'base64:'.base64_encode(str_repeat('o', 32))]);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2026_10_26_000000_create_consultation_requests_table.php',
            'database/migrations/2026_10_27_000000_add_scheduling_fields_to_consultation_requests_table.php',
            'database/migrations/2026_10_31_010000_extend_consultation_orientation_workflow.php',
        ]])->assertSuccessful();
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->string('role')->default('admin');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
        });
        Schema::create('role_user', function (Blueprint $t) {
            $t->unsignedBigInteger('role_id');
            $t->unsignedBigInteger('user_id');
        });
        Queue::fake();
        Storage::fake('local');
    }

    protected function family(string $email = 'famille@example.test'): Contact
    {
        return Contact::create(['name' => 'Grace Mbuyi', 'email' => $email]);
    }

    protected function loginActor(Contact|Professional $actor): static
    {
        return $this->withSession(['orientation_actor' => ['type' => $actor instanceof Professional ? 'professional' : 'family', 'id' => $actor->id, 'email' => $actor->email, 'expires' => now()->addHours(8)->timestamp]]);
    }

    protected function adminUser(): User
    {
        $a = User::firstOrCreate(['email' => 'admin@example.test'], ['name' => 'Secrétariat', 'password' => 'MotDePasse123!', 'role' => 'admin']);
        $this->withSession(['admin_user' => $a]);

        return $a;
    }

    protected function professional(): Professional
    {
        return Professional::create(['name' => 'Dr Exemple', 'email' => 'pro-'.Str::random(8).'@example.test', 'profession' => 'Psychiatre', 'engagement' => 'consultant', 'validated_at' => now(), 'active' => true]);
    }

    protected function payload(array $extra = []): array
    {
        return array_merge(['parent_nom' => 'Mbuyi', 'parent_prenom' => 'Grace', 'parent_lien' => 'mere', 'telephone' => '+243810000000', 'preferred_contact' => 'email', 'pays' => 'RD Congo', 'ville' => 'Kinshasa', 'fuseau_horaire' => 'Africa/Kinshasa', 'langue_seance' => 'Français', 'consultation_mode' => 'either', 'enfant_nom' => 'Mbuyi', 'enfant_prenom' => 'David', 'enfant_date_naissance' => now()->subYears(6)->toDateString(), 'motif' => 'Notre enfant rencontre des difficultés de communication au quotidien.', 'creneaux' => [['start' => now()->addDays(5)->toIso8601String()]], 'authority_consent' => true, 'consentement_donnees' => true, 'acceptation_conditions' => true], $extra);
    }

    protected function submit(Contact $actor, array $extra = []): ConsultationRequest
    {
        $this->loginActor($actor)->postJson('/api/orientation/requests', ['payload' => json_encode($this->payload($extra)), 'submission_key' => (string) Str::uuid()])->assertCreated();

        return ConsultationRequest::latest('id')->firstOrFail();
    }

    protected function proposal(ConsultationRequest $c, Professional $p, int $fee = 0): Appointment
    {
        return app(Workflow::class)->propose($c, ['professional_id' => $p->id, 'starts_at' => now()->addDays(5)->format('Y-m-d H:i:s'), 'timezone' => 'UTC', 'expires_at' => now()->addDays(2)->format('Y-m-d H:i:s'), 'duration_minutes' => 45, 'fee_usd' => $fee, 'mode' => 'remote', 'meeting_url' => 'https://meet.google.com/test-session', 'location' => null, 'kind' => 'orientation', 'purpose' => 'Échange pour comprendre les besoins de la famille.'], 1);
    }
}
