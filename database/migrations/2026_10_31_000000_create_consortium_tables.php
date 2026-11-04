<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consortium_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('consortium_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->constrained('consortium_structures');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token', 64)->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('consortium_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('consortium_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('structure_id')->unique()->constrained('consortium_structures');
            $table->string('reference')->unique();
            $table->string('status')->default('draft')->index();
            $table->json('data');
            $table->json('submitted_data')->nullable();
            $table->json('submitted_document_ids')->nullable();
            $table->unsignedInteger('revision')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->string('assigned_to')->nullable();
            $table->text('decision_reason')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
        Schema::create('consortium_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('consortium_applications');
            $table->string('kind', 50);
            $table->unsignedInteger('version');
            $table->string('name');
            $table->string('path');
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');
            $table->string('status')->default('pending');
            $table->text('review_comment')->nullable();
            $table->timestamps();
            $table->unique(['application_id', 'kind', 'version']);
        });
        Schema::create('consortium_information_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('consortium_applications');
            $table->text('message');
            $table->json('fields');
            $table->json('document_kinds');
            $table->date('due_on')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();
        });
        Schema::create('consortium_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('consortium_applications');
            $table->string('author_type');
            $table->unsignedBigInteger('author_id');
            $table->string('author_name');
            $table->text('body');
            $table->timestamps();
        });
        Schema::create('consortium_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('consortium_applications');
            $table->string('type');
            $table->string('actor_name');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('consortium_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('consortium_accounts');
            $table->string('recipient');
            $table->string('title');
            $table->text('body');
            $table->text('action_url');
            $table->string('email_status')->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['consortium_notifications', 'consortium_events', 'consortium_messages', 'consortium_information_requests', 'consortium_documents', 'consortium_applications', 'consortium_password_reset_tokens', 'consortium_accounts', 'consortium_structures'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
