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
        Schema::create('detail_supplementaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enfant_id')->constrained()->onDelete('cascade');
            $table->string('contact_urgence')->nullable();
            $table->string('telephone_urgence')->nullable();
            $table->string('medecin_traitant')->nullable();
            $table->string('telephone_medecin')->nullable();
            $table->text('allergies_conditions')->nullable();
            $table->text('notes_additionnelles')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_supplementaires');
    }
};
