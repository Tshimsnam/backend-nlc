<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPrice;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Payments\MaxiCashService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'app.key' => 'base64:'.base64_encode(str_repeat('e', 32)), 'events.timezone' => 'Africa/Kinshasa']);
        DB::purge('sqlite');
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2025_02_04_120000_create_events_table.php',
            'database/migrations/2025_02_04_130000_create_event_prices_table.php',
        ]])->assertSuccessful();
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('event_id');
            $t->unsignedBigInteger('event_price_id');
            foreach (['full_name', 'email', 'phone', 'category', 'currency', 'reference', 'pay_type', 'pay_sub_type', 'payment_status'] as $field) {
                $t->string($field)->nullable();
            }
            $t->integer('days')->default(1);
            $t->decimal('amount', 10, 2)->default(100);
            $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('password');
            $t->string('role')->default('admin');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('roles', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('role_user', function (Blueprint $t) { $t->unsignedBigInteger('role_id'); $t->unsignedBigInteger('user_id'); });
        $this->travelTo(CarbonImmutable::parse('2027-04-23 15:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
    }

    private function event(array $attributes = []): Event
    {
        return Event::create(array_merge(['title' => 'GSA', 'slug' => Str::uuid()->toString(), 'date' => '2027-04-21',
            'end_date' => '2027-04-23', 'time' => '08h00', 'end_time' => '16h00', 'location' => 'Gombe',
            'type' => 'conference', 'status' => 'upcoming'], $attributes));
    }

    private function price(Event $event): EventPrice
    {
        return EventPrice::create(['event_id' => $event->id, 'category' => 'parent', 'duration_type' => 'full_event', 'amount' => 100, 'currency' => 'USD']);
    }

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'test-password', 'role' => 'admin', 'is_active' => true]);
    }

    public function test_automatic_closure_respects_last_day_local_time_and_is_idempotent(): void
    {
        $ended = $this->event(); // 16:00 Kinshasa = 15:00 UTC, exactly now.
        $future = $this->event(['end_time' => '16:01']);
        $anotherDay = $this->event(['end_date' => '2027-04-24']);
        $past = $this->event(['status' => 'past']);
        $this->artisan('events:close-finished')->expectsOutput('1 événement(s) clôturé(s).')->assertSuccessful();
        $this->assertSame('past', $ended->fresh()->status);
        $this->assertSame('upcoming', $future->fresh()->status);
        $this->assertSame('upcoming', $anotherDay->fresh()->status);
        $this->assertSame('past', $past->fresh()->status);
        $this->artisan('events:close-finished')->expectsOutput('0 événement(s) clôturé(s).')->assertSuccessful();
    }

    public function test_missing_end_date_uses_start_date_and_missing_time_uses_end_of_day(): void
    {
        $event = $this->event(['date' => '2027-04-23', 'end_date' => null, 'end_time' => null]);
        $this->assertFalse($event->hasEnded());
        $this->travelTo(CarbonImmutable::parse('2027-04-23 23:00:00', 'UTC'));
        $this->assertTrue($event->hasEnded());
        $this->artisan('events:close-finished')->assertSuccessful();
        $this->assertSame('past', $event->fresh()->status);
    }

    public function test_dry_run_and_invalid_legacy_dates_do_not_change_records(): void
    {
        $ended = $this->event();
        $invalidDate = $this->event(['end_date' => '2027-02-30']);
        $invalidTime = $this->event(['end_time' => '99:00']);
        $this->artisan('events:close-finished', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame('upcoming', $ended->fresh()->status);
        $this->artisan('events:close-finished')->assertSuccessful();
        $this->assertSame('upcoming', $invalidDate->fresh()->status);
        $this->assertSame('upcoming', $invalidTime->fresh()->status);
    }

    public function test_finished_and_manually_closed_events_reject_registration_and_reservations(): void
    {
        foreach ([$this->event(), $this->event(['end_date' => '2027-04-24', 'status' => 'past'])] as $event) {
            $price = $this->price($event);
            $body = ['event_price_id' => $price->id, 'full_name' => 'Parent Test', 'email' => 'parent@example.test', 'phone' => '+243844338747', 'pay_type' => 'cash'];
            $this->postJson("/api/events/{$event->id}/register", $body)->assertStatus(409)->assertJsonPath('code', 'event_registration_closed');
            $this->postJson("/api/events/{$event->id}/reserve", ['event_price_id' => $price->id])->assertStatus(409);
        }
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_open_events_still_accept_cash_registration_and_reservation(): void
    {
        $event = $this->event(['end_date' => '2027-04-24']);
        $price = $this->price($event);
        $this->postJson("/api/events/{$event->id}/register", ['event_price_id' => $price->id, 'full_name' => 'Parent Test',
            'email' => 'parent@example.test', 'phone' => '+243844338747', 'pay_type' => 'cash'])->assertCreated();
        $this->postJson("/api/events/{$event->id}/reserve", ['event_price_id' => $price->id])->assertCreated();
        $this->assertDatabaseCount('tickets', 2);
    }

    public function test_reserved_ticket_cannot_be_completed_after_closure_and_is_preserved(): void
    {
        $event = $this->event();
        $price = $this->price($event);
        $ticket = Ticket::create(['event_id' => $event->id, 'event_price_id' => $price->id, 'reference' => 'RESERVED', 'payment_status' => 'reserved']);
        $this->mock(MaxiCashService::class)->shouldNotReceive('initiatePaymentForTicket');
        $this->postJson('/api/reservations/RESERVED/complete', [])->assertStatus(409);
        $this->assertSame('reserved', $ticket->fresh()->payment_status);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_physical_ticket_activation_is_blocked_for_finished_events(): void
    {
        $event = $this->event();
        $price = $this->price($event);
        Sanctum::actingAs($this->admin());
        $this->postJson('/api/tickets/physical', ['physical_qr_id' => 'PHY-TEST', 'event_id' => $event->id,
            'event_price_id' => $price->id, 'full_name' => 'Parent Test', 'email' => 'parent@example.test', 'phone' => '+243844338747'])
            ->assertStatus(409);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_admin_can_update_only_the_status_through_the_api(): void
    {
        $event = $this->event(['end_date' => '2027-04-24']);
        Sanctum::actingAs($this->admin());
        $this->putJson("/api/events/{$event->id}", ['status' => 'past'])->assertOk()->assertJsonPath('data.registration_open', false);
        $this->assertSame('past', $event->fresh()->status);
        $this->assertSame('GSA', $event->fresh()->title);
        $this->putJson("/api/events/{$event->id}", ['status' => 'completed'])->assertUnprocessable();
        $this->putJson("/api/events/{$event->id}", ['slug' => $event->slug])->assertOk();
    }

    public function test_admin_form_requires_a_session_and_saves_status_without_deleting_tickets(): void
    {
        $event = $this->event(['end_date' => '2027-04-24']);
        $this->put("/admin/events/{$event->id}/status", ['status' => 'past'])->assertRedirect(route('login'));
        $this->withSession(['admin_user' => $this->admin()])->put("/admin/events/{$event->id}/status", ['status' => 'past'])
            ->assertRedirect(route('admin.dashboard.view', ['tab' => 'events']))->assertSessionHas('success');
        $this->assertSame('past', $event->fresh()->status);
        $this->put("/admin/events/{$event->id}/status", ['status' => 'unknown'])->assertSessionHasErrors('status');
        $this->put("/admin/events/{$event->id}/status", ['status' => 'upcoming'])->assertSessionHas('success');
        $this->assertSame('upcoming', $event->fresh()->status);
        $event->update(['end_date' => '2027-04-22', 'status' => 'past']);
        $this->put("/admin/events/{$event->id}/status", ['status' => 'upcoming'])->assertSessionHas('error');
        $this->assertSame('past', $event->fresh()->status);
    }

    public function test_public_api_exposes_registration_closure_before_scheduler_runs(): void
    {
        $event = $this->event();
        $this->getJson('/api/events/'.$event->slug)->assertOk()->assertJsonPath('registration_open', false);
        $this->assertSame('upcoming', $event->fresh()->status);
    }

    public function test_event_administration_rejects_inactive_and_non_admin_users(): void
    {
        $event = $this->event();
        $admin = $this->admin();
        $this->withSession(['admin_user' => $admin]);
        $admin->update(['is_active' => false]);
        $this->put("/admin/events/{$event->id}/status", ['status' => 'past'])->assertForbidden();
        $admin->update(['is_active' => true, 'role' => 'parent']);
        $this->put("/admin/events/{$event->id}/status", ['status' => 'past'])->assertForbidden();
        $this->put("/admin/events/{$event->id}/update", ['status' => 'past'])->assertForbidden();
        $this->assertSame('upcoming', $event->fresh()->status);
    }

    public function test_admin_list_renders_the_status_form_and_closure_task_is_scheduled(): void
    {
        $event = $this->event();
        $html = view('admin.partials.events-list', ['events' => collect([$event]), 'eventsList' => Event::paginate(15), 'errors' => new \Illuminate\Support\ViewErrorBag])->render();
        $this->assertStringContainsString(route('admin.events.status', $event), $html);
        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('Terminé', $html);
        $scheduled = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($task) => str_contains($task->command ?? '', 'events:close-finished'));
        $this->assertNotNull($scheduled);
        $this->assertSame('* * * * *', $scheduled->expression);
    }

    public function test_api_rejects_invalid_dates_and_end_times_but_accepts_partial_rescheduling(): void
    {
        $event = $this->event();
        Sanctum::actingAs($this->admin());
        $this->putJson("/api/events/{$event->id}", ['end_date' => '2027-04-20'])->assertUnprocessable();
        $this->putJson("/api/events/{$event->id}", ['date' => '2027-04-25'])->assertUnprocessable();
        $this->putJson("/api/events/{$event->id}", ['end_time' => '25:00'])->assertUnprocessable();
        $this->putJson("/api/events/{$event->id}", ['end_date' => '2027-02-30'])->assertUnprocessable();
        $this->putJson("/api/events/{$event->id}", ['end_date' => '2027-04-24', 'end_time' => '17:30'])
            ->assertOk()->assertJsonPath('data.registration_open', true);
    }
}
