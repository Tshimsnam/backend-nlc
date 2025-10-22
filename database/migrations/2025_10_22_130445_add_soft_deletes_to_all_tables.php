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
        // Ajouter deleted_at à toutes les tables principales
        $tables = [
            'users',
            'children', 
            'programs',
            'courses',
            'appointments',
            'messages',
            'reports',
            'notifications',
            'dossiers',
            'settings',
            'roles'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer deleted_at de toutes les tables
        $tables = [
            'users',
            'children', 
            'programs',
            'courses',
            'appointments',
            'messages',
            'reports',
            'notifications',
            'dossiers',
            'settings',
            'roles'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
