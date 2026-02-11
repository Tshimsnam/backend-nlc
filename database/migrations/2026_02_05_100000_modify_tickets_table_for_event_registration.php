<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer les clés étrangères si elles existent
        if (Schema::hasColumn('tickets', 'participant_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['participant_id']);
            });
        }
        
        if (Schema::hasColumn('tickets', 'payment_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['payment_id']);
            });
        }

        // Supprimer les colonnes si elles existent
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'participant_id')) {
                $table->dropColumn('participant_id');
            }
            if (Schema::hasColumn('tickets', 'payment_id')) {
                $table->dropColumn('payment_id');
            }
            if (Schema::hasColumn('tickets', 'ticket_number')) {
                $table->dropColumn('ticket_number');
            }
            if (Schema::hasColumn('tickets', 'status')) {
                $table->dropColumn('status');
            }
        });

        // Ajouter les nouvelles colonnes si elles n'existent pas
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'event_price_id')) {
                $table->foreignId('event_price_id')->after('event_id')->constrained('event_prices')->onDelete('cascade');
            }
            if (!Schema::hasColumn('tickets', 'full_name')) {
                $table->string('full_name')->after('event_price_id');
            }
            if (!Schema::hasColumn('tickets', 'email')) {
                $table->string('email')->after('full_name');
            }
            if (!Schema::hasColumn('tickets', 'phone')) {
                $table->string('phone')->after('email');
            }
            if (!Schema::hasColumn('tickets', 'category')) {
                $table->string('category')->after('phone');
            }
            if (!Schema::hasColumn('tickets', 'days')) {
                $table->unsignedInteger('days')->default(1)->after('category');
            }
            if (!Schema::hasColumn('tickets', 'amount')) {
                $table->decimal('amount', 10, 2)->after('days');
            }
            if (!Schema::hasColumn('tickets', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('amount');
            }
            if (!Schema::hasColumn('tickets', 'reference')) {
                $table->string('reference')->unique()->after('currency');
            }
            if (!Schema::hasColumn('tickets', 'pay_type')) {
                $table->string('pay_type')->after('reference');
            }
            if (!Schema::hasColumn('tickets', 'pay_sub_type')) {
                $table->string('pay_sub_type')->nullable()->after('pay_type');
            }
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
