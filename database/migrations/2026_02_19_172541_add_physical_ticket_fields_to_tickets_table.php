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
        Schema::table('tickets', function (Blueprint $table) {
            // Ajouter le champ pour les QR codes physiques
            $table->string('physical_qr_id')->nullable()->unique()->after('reference');
            
            // Ajouter les relations si elles n'existent pas déjà
            if (!Schema::hasColumn('tickets', 'participant_id')) {
                $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('set null')->after('event_id');
            }
            
            if (!Schema::hasColumn('tickets', 'event_price_id')) {
                $table->foreignId('event_price_id')->nullable()->constrained('event_prices')->onDelete('set null')->after('participant_id');
            }
            
            // Ajouter un index pour améliorer les performances
            $table->index('physical_qr_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['physical_qr_id']);
            $table->dropColumn('physical_qr_id');
            
            if (Schema::hasColumn('tickets', 'participant_id')) {
                $table->dropForeign(['participant_id']);
                $table->dropColumn('participant_id');
            }
            
            if (Schema::hasColumn('tickets', 'event_price_id')) {
                $table->dropForeign(['event_price_id']);
                $table->dropColumn('event_price_id');
            }
        });
    }
};
