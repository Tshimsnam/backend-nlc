<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Str;

class ChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer des parents (utilisateurs avec le rôle parent)
        $parents = User::where('role', 'parent')->get();

        if ($parents->isEmpty()) {
            $this->command->info('Aucun parent trouvé. Créez d\'abord des utilisateurs avec le rôle parent.');
            return;
        }

        $children = [
            [
                'id' => Str::uuid(),
                'first_name' => 'Sophie',
                'last_name' => 'Martin',
                'date_of_birth' => '2018-05-15',
                'parent_id' => $parents->first()->id,
                'medical_info' => 'Aucune allergie connue',
                'special_needs' => 'Troubles du spectre autistique',
                'status' => 'active',
            ],
            [
                'id' => Str::uuid(),
                'first_name' => 'Lucas',
                'last_name' => 'Dubois',
                'date_of_birth' => '2019-03-22',
                'parent_id' => $parents->skip(1)->first()->id ?? $parents->first()->id,
                'medical_info' => 'Asthme léger',
                'special_needs' => 'Retard de langage',
                'status' => 'active',
            ],
            [
                'id' => Str::uuid(),
                'first_name' => 'Emma',
                'last_name' => 'Bernard',
                'date_of_birth' => '2017-11-08',
                'parent_id' => $parents->skip(2)->first()->id ?? $parents->first()->id,
                'medical_info' => 'Allergies alimentaires (arachides)',
                'special_needs' => 'Dyslexie',
                'status' => 'active',
            ],
        ];

        foreach ($children as $child) {
            Child::create($child);
        }

        $this->command->info('Enfants créés avec succès!');
    }
}
