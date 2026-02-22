<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('date');
            $table->string('end_date')->nullable();
            $table->string('time');
            $table->string('end_time')->nullable();
            $table->string('location');
            $table->enum('type', ['workshop', 'celebration', 'seminar', 'gala', 'conference']);
            $table->enum('status', ['upcoming', 'past'])->default('upcoming');
            $table->string('image')->nullable();
            $table->json('agenda')->nullable();
            $table->json('price')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('registered')->default(0);
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('venue_details')->nullable();
            $table->json('sponsors')->nullable();
            $table->string('organizer')->nullable();
            $table->date('registration_deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
