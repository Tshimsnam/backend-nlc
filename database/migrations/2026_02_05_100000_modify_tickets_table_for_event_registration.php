<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['participant_id']);
            $table->dropForeign(['payment_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['participant_id', 'payment_id', 'ticket_number', 'status']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('event_price_id')->after('event_id')->constrained('event_prices')->onDelete('cascade');
            $table->string('full_name')->after('event_price_id');
            $table->string('email')->after('full_name');
            $table->string('phone')->after('email');
            $table->string('category')->after('phone');
            $table->unsignedInteger('days')->default(1)->after('category');
            $table->decimal('amount', 10, 2)->after('days');
            $table->string('currency', 3)->default('USD')->after('amount');
            $table->string('reference')->unique()->after('currency');
            $table->string('pay_type')->after('reference');
            $table->string('pay_sub_type')->nullable()->after('pay_type');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['event_price_id']);
            $table->dropColumn([
                'event_price_id', 'full_name', 'email', 'phone', 'category',
                'days', 'amount', 'currency', 'reference', 'pay_type', 'pay_sub_type',
            ]);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->string('ticket_number')->unique();
            $table->string('status')->default('pending');
        });
    }
};
