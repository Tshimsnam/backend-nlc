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
        Schema::create('parent_child', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('child_id')->constrained('children')->onDelete('cascade');
            $table->enum('relationship', ['mother', 'father', 'guardian', 'grandparent', 'other'])->default('guardian');
            $table->boolean('is_primary')->default(false);
            $table->boolean('has_custody')->default(true);
            $table->unsignedTinyInteger('emergency_contact_order')->nullable(); // <-- int
            $table->timestamps();
        
            $table->unique(['parent_id', 'child_id']);
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_child');
    }
};
