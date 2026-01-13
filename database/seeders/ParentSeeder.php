<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // On récupère (ou crée) le rôle "parent"
        $parentRole = Role::firstOrCreate([
            'name' => 'parent',
        ]);

        // Parent 1
        $parent1 = User::firstOrCreate(
            ['email' => 'parent1@example.com'], // pour éviter les doublons si tu relances les seed
            [
                'name' => 'Parent Un',
                'password' => Hash::make('password123'), // à changer en prod
                'telephone' => '0600000001',
                'statut' => 'actif', // si tu as ce champ
            ]
        );

        // Parent 2
        $parent2 = User::firstOrCreate(
            ['email' => 'parent2@example.com'],
            [
                'name' => 'Parent Deux',
                'password' => Hash::make('password123'),
                'telephone' => '0600000002',
                'statut' => 'actif',
            ]
        );

        // Attacher le rôle "parent" aux deux utilisateurs
        $parent1->roles()->syncWithoutDetaching([$parentRole->id]);
        $parent2->roles()->syncWithoutDetaching([$parentRole->id]);

        $this->command->info('2 parents créés avec le rôle "parent".');
    }
}
