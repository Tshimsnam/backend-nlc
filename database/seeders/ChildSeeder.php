<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // On récupère les parents qui ont le rôle "parent"
        $parents = User::whereHas('roles', function ($q) {
            $q->where('name', 'parent');
        })->get();

        if ($parents->count() < 2) {
            $this->command->warn('Il faut au moins 2 parents avec le rôle "parent" avant de lancer ChildSeeder.');
            return;
        }

        $parent1 = $parents[0];
        $parent2 = $parents[1];

        // 👶 Enfant 1 : 1 seul parent
        $child1 = Child::create([
            'id' => Str::uuid(),
            'first_name' => 'Sophie',
            'last_name' => 'Martin',
            'date_of_birth' => '2018-05-15',
            'medical_info' => 'Aucune allergie connue',
            'special_needs' => 'Troubles du spectre autistique',
            'status' => 'active',
            'gender' => 'female', // si la colonne existe bien
        ]);

        $child1->parents()->attach($parent1->id, [
            'relationship' => 'mother',
            'is_primary' => true,
            'has_custody' => true,
            'emergency_contact_order' => 1,
        ]);

        // 👶 Enfant 2 : 2 parents
        $child2 = Child::create([
            'id' => Str::uuid(),
            'first_name' => 'Lucas',
            'last_name' => 'Dubois',
            'date_of_birth' => '2019-03-22',
            'medical_info' => 'Asthme léger',
            'special_needs' => 'Retard de langage',
            'status' => 'active',
            'gender' => 'male',
        ]);

        $child2->parents()->attach([
            $parent1->id => [
                'relationship' => 'mother',
                'is_primary' => true,
                'has_custody' => true,
                'emergency_contact_order' => 1,
            ],
            $parent2->id => [
                'relationship' => 'father',
                'is_primary' => false,
                'has_custody' => true,
                'emergency_contact_order' => 2,
            ],
        ]);

        $this->command->info('2 enfants créés : un avec 1 parent, un avec 2 parents.');
    }
}
