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
        Schema::create('dossiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id')->unique();
            $table->foreign('child_id')->references('id')->on('children')->onDelete('cascade');
            $table->json('medical_history')->nullable();
            $table->json('allergies')->nullable();
            $table->json('medications')->nullable();
            $table->json('emergency_contacts')->nullable();
            $table->json('educational_goals')->nullable();
            $table->text('behavioral_notes')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
