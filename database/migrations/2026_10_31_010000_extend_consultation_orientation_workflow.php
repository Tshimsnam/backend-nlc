<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orientation_contacts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('orientation_professionals', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('profession');
            $t->string('specialty')->nullable();
            $t->string('engagement')->default('internal');
            $t->json('languages')->nullable();
            $t->string('timezone')->default('Africa/Kinshasa');
            $t->boolean('active')->default(true);
            $t->timestamp('validated_at')->nullable();
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->text('credential_notes')->nullable();
            $t->unsignedInteger('default_fee_cents')->default(0);
            $t->timestamps();
        });
        Schema::table('consultation_requests', function (Blueprint $t) {
            $t->foreignId('orientation_contact_id')->nullable()->constrained('orientation_contacts');
            $t->foreignId('guardian_contact_id')->nullable()->constrained('orientation_contacts');
            $t->uuid('submission_key')->nullable()->unique();
            $t->string('guardian_name')->nullable();
            $t->string('guardian_email')->nullable();
            $t->string('authority_status')->default('legacy');
            $t->timestamp('guardian_approved_at')->nullable();
            $t->string('preferred_contact')->default('email');
            $t->string('consultation_mode')->default('either');
            $t->timestamp('processing_due_at')->nullable()->index();
            $t->timestamp('first_response_at')->nullable();
            $t->timestamp('sla_alerted_at')->nullable();
            $t->text('information_requested')->nullable();
            $t->timestamp('information_requested_at')->nullable();
        });
        Schema::create('orientation_drafts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('contact_id')->unique()->constrained('orientation_contacts');
            $t->longText('data');
            $t->timestamps();
        });
        Schema::create('orientation_appointments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('consultation_request_id')->constrained('consultation_requests');
            $t->foreignId('professional_id')->constrained('orientation_professionals');
            $t->string('status')->default('awaiting_professional')->index();
            $t->string('kind')->default('orientation');
            $t->timestamp('starts_at')->index();
            $t->timestamp('ends_at');
            $t->unsignedSmallInteger('duration_minutes');
            $t->timestamp('expires_at')->nullable();
            $t->string('mode');
            $t->string('location', 1000)->nullable();
            $t->text('meeting_url')->nullable();
            $t->unsignedInteger('fee_cents')->default(0);
            $t->string('currency', 3)->default('USD');
            $t->text('purpose');
            $t->timestamp('professional_accepted_at')->nullable();
            $t->timestamp('accepted_at')->nullable();
            $t->foreignId('accepted_by')->nullable()->constrained('orientation_contacts');
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('reminded_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->longText('clinical_notes')->nullable();
            $t->longText('shared_summary')->nullable();
            $t->text('cancellation_reason')->nullable();
            $t->string('refund_status')->default('none');
            $t->text('refund_note')->nullable();
            $t->timestamps();
        });
        Schema::create('orientation_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appointment_id')->constrained('orientation_appointments');
            $t->string('reference')->unique();
            $t->string('gateway');
            $t->string('status')->default('pending');
            $t->unsignedInteger('amount_cents');
            $t->string('currency', 3)->default('USD');
            $t->string('gateway_log_id')->nullable()->unique();
            $t->text('redirect_url')->nullable();
            $t->string('transaction_reference')->nullable()->unique();
            $t->text('review_note')->nullable();
            $t->unsignedBigInteger('verified_by')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamps();
        });
        Schema::create('orientation_access_links', function (Blueprint $t) {
            $t->id();
            $t->string('token_hash', 64)->unique();
            $t->string('email_hash', 64);
            $t->string('actor_type');
            $t->unsignedBigInteger('actor_id');
            $t->string('purpose')->default('login');
            $t->foreignId('consultation_request_id')->nullable()->constrained('consultation_requests');
            $t->timestamp('expires_at');
            $t->timestamp('used_at')->nullable();
            $t->timestamps();
        });
        Schema::create('orientation_notices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('appointment_id')->nullable()->constrained('orientation_appointments');
            $t->string('expected_status')->nullable();
            $t->string('actor_type')->nullable();
            $t->unsignedBigInteger('actor_id')->nullable();
            $t->string('recipient');
            $t->string('title');
            $t->text('body');
            $t->text('action_url')->nullable();
            $t->string('email_status')->default('pending')->index();
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
        Schema::create('orientation_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('consultation_request_id')->constrained('consultation_requests');
            $t->string('actor_type');
            $t->unsignedBigInteger('actor_id')->nullable();
            $t->string('label');
            $t->boolean('public')->default(true);
            $t->timestamps();
        });
        Schema::create('orientation_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('consultation_request_id')->constrained('consultation_requests');
            $t->string('actor_type');
            $t->unsignedBigInteger('actor_id')->nullable();
            $t->string('author');
            $t->longText('body');
            $t->timestamps();
        });
        Schema::create('orientation_settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['orientation_settings', 'orientation_messages', 'orientation_events', 'orientation_notices', 'orientation_access_links', 'orientation_payments', 'orientation_appointments', 'orientation_drafts'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('consultation_requests', function (Blueprint $t) {
            $t->dropConstrainedForeignId('orientation_contact_id');
            $t->dropConstrainedForeignId('guardian_contact_id');
            $t->dropColumn(['submission_key', 'guardian_name', 'guardian_email', 'authority_status', 'guardian_approved_at', 'preferred_contact', 'consultation_mode', 'processing_due_at', 'first_response_at', 'sla_alerted_at', 'information_requested', 'information_requested_at']);
        });
        Schema::dropIfExists('orientation_professionals');
        Schema::dropIfExists('orientation_contacts');
    }
};
