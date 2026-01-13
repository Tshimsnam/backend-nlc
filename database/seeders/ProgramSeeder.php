<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer des enfants
        $children = Child::all();

        if ($children->isEmpty()) {
            $this->command->info('Aucun enfant trouvé. Créez d\'abord des enfants.');
            return;
        }

        // Récupérer des super teachers
        $superTeachers = User::where('role', 'super-teacher')->get();

        if ($superTeachers->isEmpty()) {
            $this->command->info('Aucun Super Teacher trouvé. Création de programmes avec le premier utilisateur admin.');
            $superTeachers = User::where('role', 'admin')->get();
        }

        if ($superTeachers->isEmpty()) {
            $this->command->info('Aucun utilisateur approprié trouvé.');
            return;
        }

        $programs = [
            [
                'id' => Str::uuid(),
                'title' => 'Programme d\'apprentissage du langage',
                'description' => 'Programme intensif pour développer les compétences linguistiques',
                'child_id' => $children->first()->id,
                'created_by' => $superTeachers->first()->id,
                'status' => 'active',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(6)->format('Y-m-d'),
                'objectives' => [
                    'Améliorer la communication verbale',
                    'Augmenter le vocabulaire',
                    'Développer la compréhension',
                ],
            ],
            [
                'id' => Str::uuid(),
                'title' => 'Programme de développement social',
                'description' => 'Améliorer les interactions sociales et l\'autonomie',
                'child_id' => $children->skip(1)->first()->id ?? $children->first()->id,
                'created_by' => $superTeachers->first()->id,
                'status' => 'approved',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(4)->format('Y-m-d'),
                'objectives' => [
                    'Renforcer les compétences sociales',
                    'Développer l\'empathie',
                    'Améliorer le travail en groupe',
                ],
            ],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }

        $this->command->info('Programmes créés avec succès!');
    }
}
