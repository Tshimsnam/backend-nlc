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
        // Modifier la colonne payment_status pour permettre null (pour les réservations)
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('full_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('pay_type')->nullable()->change();
        });
        
        // Note: Le statut 'reserved' sera géré au niveau de l'application
        // payment_status peut être: 'reserved', 'pending', 'pending_cash', 'completed', 'failed', 'cancelled'
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('pay_type')->nullable(false)->change();
        });
    }
};
