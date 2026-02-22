<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Ajouter le champ pour les QR codes physiques
            if (!Schema::hasColumn('tickets', 'physical_qr_id')) {
                $table->string('physical_qr_id')->nullable()->after('reference');
            }
            
            // Ajouter les relations si elles n'existent pas déjà
            if (!Schema::hasColumn('tickets', 'participant_id')) {
                $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('set null')->after('event_id');
            }
            
            if (!Schema::hasColumn('tickets', 'event_price_id')) {
                $table->foreignId('event_price_id')->nullable()->constrained('event_prices')->onDelete('set null')->after('participant_id');
            }
        });
        
        // Ajouter l'index et unique constraint séparément
        if (Schema::hasColumn('tickets', 'physical_qr_id')) {
            try {
                DB::statement('ALTER TABLE tickets ADD UNIQUE INDEX tickets_physical_qr_id_unique (physical_qr_id)');
            } catch (\Exception $e) {
                // L'index existe déjà, on ignore l'erreur
            }
            
            try {
                DB::statement('ALTER TABLE tickets ADD INDEX tickets_physical_qr_id_index (physical_qr_id)');
            } catch (\Exception $e) {
                // L'index existe déjà, on ignore l'erreur
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'physical_qr_id')) {
                // Supprimer les index si ils existent
                try {
                    DB::statement('ALTER TABLE tickets DROP INDEX tickets_physical_qr_id_unique');
                } catch (\Exception $e) {
                    // L'index n'existe pas, on ignore
                }
                
                try {
                    DB::statement('ALTER TABLE tickets DROP INDEX tickets_physical_qr_id_index');
                } catch (\Exception $e) {
                    // L'index n'existe pas, on ignore
                }
                
                $table->dropColumn('physical_qr_id');
            }
            
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
