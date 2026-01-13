<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // On seed d'abord les rôles
        $this->call(RoleSeeder::class);
        $this->call(AdminSeeder::class);

        // Seeders pour le système NLC
        $this->call(ParentSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(ChildSeeder::class);
        $this->call(ProgramSeeder::class);

        // (Optionnel) autres seeders, ex : faker users de test
        // $this->call(AnotherSeeder::class);

        // Exemple de user de test classique (si tu veux garder)
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
