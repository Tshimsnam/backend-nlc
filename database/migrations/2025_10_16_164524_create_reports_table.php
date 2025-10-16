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
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('child_id');
            $table->foreign('child_id')->references('id')->on('children')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->enum('report_type', ['progress', 'incident', 'evaluation', 'medical', 'behavioral', 'academic']);
            $table->string('title', 200);
            $table->text('content');
            $table->json('observations')->nullable();
            $table->text('recommendations')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
