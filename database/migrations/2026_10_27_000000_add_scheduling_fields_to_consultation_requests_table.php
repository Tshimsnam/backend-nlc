<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('duree_minutes')->nullable()->after('seance_programmee_at');
            $table->string('devise', 3)->default('USD')->after('prix');
            $table->timestamp('confirmation_envoyee_at')->nullable()->after('notes_internes');
        });
    }

    public function down(): void
    {
        Schema::table('consultation_requests', function (Blueprint $table) {
            $table->dropColumn(['duree_minutes', 'devise', 'confirmation_envoyee_at']);
        });
    }
};
