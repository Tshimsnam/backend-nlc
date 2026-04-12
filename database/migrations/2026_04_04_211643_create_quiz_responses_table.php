<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 40)->index();
            $table->string('quiz_slug', 30)->default('gsa-2026');
            $table->unsignedTinyInteger('question_id');      // 1 à 12
            $table->enum('answer', ['vrai', 'faux', 'peut_etre']);
            $table->string('ip_hash', 64)->nullable();       // hash de l'IP, pas l'IP brute
            $table->timestamps();

            // Un token ne peut répondre qu'une fois par question
            $table->unique(['session_token', 'quiz_slug', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_responses');
    }
};
