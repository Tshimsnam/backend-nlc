<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuizQuestion;
use App\Models\Event;

class QuizGSA2026Seeder extends Seeder
{
    public function run(): void
    {
        // Lier au premier événement (ou celui avec le slug correspondant)
        $event = Event::where('slug', 'le-grand-salon-de-lautiste')->first()
            ?? Event::orderBy('id')->first();

        $eventId = $event?->id;

        // Supprimer les anciennes questions pour éviter les doublons
        QuizQuestion::where('quiz_slug', 'gsa-2026')->delete();

        $questions = [
            ['order' => 1,  'text' => 'Un retrait de l\'environnement immédiat ?',                    'correct_answer' => 'faux'],
            ['order' => 2,  'text' => 'Une maladie physique ?',                                        'correct_answer' => 'faux'],
            ['order' => 3,  'text' => 'Transmis d\'une génération à l\'autre ?',                       'correct_answer' => 'peut_etre'],
            ['order' => 4,  'text' => 'Une maladie mentale ?',                                         'correct_answer' => 'faux'],
            ['order' => 5,  'text' => 'Associé à l\'épilepsie ?',                                      'correct_answer' => 'peut_etre'],
            ['order' => 6,  'text' => 'Lié à une mauvaise éducation parentale ?',                      'correct_answer' => 'faux'],
            ['order' => 7,  'text' => 'Observé plus souvent chez les garçons que chez les filles ?',   'correct_answer' => 'vrai'],
            ['order' => 8,  'text' => 'Diagnostiqué par des analyses de sang ?',                       'correct_answer' => 'faux'],
            ['order' => 9,  'text' => 'Est-ce curable ?',                                              'correct_answer' => 'faux'],
            ['order' => 10, 'text' => 'A un comportement difficile ?',                                 'correct_answer' => 'peut_etre'],
            ['order' => 11, 'text' => 'A des difficultés à communiquer ?',                             'correct_answer' => 'vrai'],
        ];

        $rows = array_map(fn($q) => array_merge($q, [
            'quiz_slug'  => 'gsa-2026',
            'is_active'  => true,
            'event_id'   => $eventId,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $questions);

        QuizQuestion::insert($rows);

        $this->command->info("Quiz GSA 2026 seedé — lié à l'événement ID: {$eventId}");
    }
}
