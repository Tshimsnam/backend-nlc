<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->onDelete('set null'); // Agent qui a scanné
            $table->string('scan_location')->nullable(); // Lieu du scan (entrée, sortie, etc.)
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();

            // Index pour les requêtes fréquentes
            $table->index(['ticket_id', 'scanned_at']);
            $table->index(['event_id', 'scanned_at']);
        });

        // Ajouter un compteur de scans dans la table tickets
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('scan_count')->default(0)->after('payment_status');
            $table->timestamp('first_scanned_at')->nullable()->after('scan_count');
            $table->timestamp('last_scanned_at')->nullable()->after('first_scanned_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['scan_count', 'first_scanned_at', 'last_scanned_at']);
        });

        Schema::dropIfExists('ticket_scans');
    }
};
