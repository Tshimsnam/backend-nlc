<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // quiz_responses : remplacer quiz_slug par event_id (on garde quiz_slug pour compatibilité)
        Schema::table('quiz_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });

        // quiz_questions
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });

        // colloque_evaluations
        Schema::table('colloque_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });

        // evaluation_questions
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['quiz_responses','quiz_questions','colloque_evaluations','evaluation_questions'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign([$t->getTable() . '_event_id_foreign']);
                $t->dropColumn('event_id');
            });
        }
    }
};
