<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// php artisan make:migration add_extra_fields_to_children_table

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('date_of_birth');

            // toutes tes colonnes dès le début
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('medical_info')->nullable();
            $table->text('special_needs')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'transferred'])->default('active');

            // Infos médecin
            $table->string('doctor_name', 255)->nullable();
            $table->string('doctor_specialty', 255)->nullable();
            $table->string('doctor_phone', 20)->nullable();
            $table->text('doctor_address')->nullable();

            // Contacts d'urgence
            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_relation', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_phone2', 20)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};

