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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('capacity');
            }
            if (!Schema::hasColumn('events', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('contact_phone');
            }
            if (!Schema::hasColumn('events', 'venue_details')) {
                $table->string('venue_details')->nullable()->after('location');
            }
            if (!Schema::hasColumn('events', 'sponsors')) {
                $table->json('sponsors')->nullable()->after('agenda');
            }
            if (!Schema::hasColumn('events', 'organizer')) {
                $table->string('organizer')->nullable()->after('contact_email');
            }
            if (!Schema::hasColumn('events', 'registration_deadline')) {
                $table->date('registration_deadline')->nullable()->after('organizer');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'contact_phone',
                'contact_email',
                'venue_details',
                'sponsors',
                'organizer',
                'registration_deadline',
            ]);
        });
    }
};
