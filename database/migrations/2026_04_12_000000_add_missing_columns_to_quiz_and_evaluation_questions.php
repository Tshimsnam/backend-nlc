<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // quiz_questions — ajouter les colonnes manquantes
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->string('quiz_slug')->nullable()->after('event_id');
            $table->integer('order')->default(0)->after('quiz_slug');
            $table->text('text')->after('order');
            $table->string('correct_answer')->nullable()->after('text');
            $table->boolean('is_active')->default(true)->after('correct_answer');
        });

        // evaluation_questions — ajouter les colonnes manquantes
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->string('section')->default('tsa')->after('event_id');
            $table->integer('order')->default(0)->after('section');
            $table->text('text')->after('order');
            $table->json('options')->nullable()->after('text');
            $table->string('correct_answer')->nullable()->after('options');
            $table->boolean('is_active')->default(true)->after('correct_answer');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn(['event_id', 'quiz_slug', 'order', 'text', 'correct_answer', 'is_active']);
        });

        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropColumn(['event_id', 'section', 'order', 'text', 'options', 'correct_answer', 'is_active']);
        });
    }
};
