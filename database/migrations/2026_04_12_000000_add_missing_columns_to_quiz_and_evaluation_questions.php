<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // quiz_questions — ajouter uniquement les colonnes manquantes (event_id déjà présent)
        Schema::table('quiz_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_questions', 'quiz_slug')) {
                $table->string('quiz_slug')->nullable()->after('event_id');
            }
            if (!Schema::hasColumn('quiz_questions', 'order')) {
                $table->integer('order')->default(0);
            }
            if (!Schema::hasColumn('quiz_questions', 'text')) {
                $table->text('text');
            }
            if (!Schema::hasColumn('quiz_questions', 'correct_answer')) {
                $table->string('correct_answer')->nullable();
            }
            if (!Schema::hasColumn('quiz_questions', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        // evaluation_questions — ajouter uniquement les colonnes manquantes (event_id déjà présent)
        Schema::table('evaluation_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('evaluation_questions', 'section')) {
                $table->string('section')->default('tsa');
            }
            if (!Schema::hasColumn('evaluation_questions', 'order')) {
                $table->integer('order')->default(0);
            }
            if (!Schema::hasColumn('evaluation_questions', 'text')) {
                $table->text('text');
            }
            if (!Schema::hasColumn('evaluation_questions', 'options')) {
                $table->json('options')->nullable();
            }
            if (!Schema::hasColumn('evaluation_questions', 'correct_answer')) {
                $table->string('correct_answer')->nullable();
            }
            if (!Schema::hasColumn('evaluation_questions', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['quiz_slug', 'order', 'text', 'correct_answer', 'is_active'],
                fn($col) => Schema::hasColumn('quiz_questions', $col)
            ));
        });

        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['section', 'order', 'text', 'options', 'correct_answer', 'is_active'],
                fn($col) => Schema::hasColumn('evaluation_questions', $col)
            ));
        });
    }
};
