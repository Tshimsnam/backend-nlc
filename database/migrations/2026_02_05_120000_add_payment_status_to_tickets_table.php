<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('pay_sub_type'); // pending, completed, failed, cancelled
            $table->string('gateway_log_id')->nullable()->after('payment_status'); // MaxiCash LogID
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'gateway_log_id']);
        });
    }
};
