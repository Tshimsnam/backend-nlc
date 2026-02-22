<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // ID du QR code physique (ex: PHY-123456)
            $table->string('physical_qr_id')->nullable()->unique()->after('reference');
            
            // Relation avec le participant (nullable car les anciens tickets n'en ont pas)
            $table->foreignId('participant_id')->nullable()->after('event_id')->constrained('participants')->onDelete('set null');
            
            // Champ qr_data pour stocker les données JSON du QR code
            if (!Schema::hasColumn('tickets', 'qr_data')) {
                $table->text('qr_data')->nullable()->after('pay_sub_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('physical_qr_id');
            $table->dropForeign(['participant_id']);
            $table->dropColumn('participant_id');
            
            if (Schema::hasColumn('tickets', 'qr_data')) {
                $table->dropColumn('qr_data');
            }
        });
    }
};
