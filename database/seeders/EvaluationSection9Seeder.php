<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationQuestion;
use App\Models\Event;

class EvaluationSection9Seeder extends Seeder
{
    public function run(): void
    {
        // Lier au premier événement (ou celui avec le slug correspondant)
        $event = Event::where('slug', 'le-grand-salon-de-lautiste')->first()
            ?? Event::orderBy('id')->first();

        $eventId = $event?->id;

        // Supprimer les anciennes pour éviter les doublons
        EvaluationQuestion::where('section', 'tsa')->delete();

        $questions = [
            [
                'order'          => 1,
                'text'           => 'Les personnes avec un TSA évitent le contact visuel :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'B',
            ],
            [
                'order'          => 2,
                'text'           => 'Les personnes avec un TSA présentent une déficience intellectuelle :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'B',
            ],
            [
                'order'          => 3,
                'text'           => 'Les personnes avec un TSA ont des comportements, intérêts ou activités restreints et répétitifs :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'A',
            ],
            [
                'order'          => 4,
                'text'           => 'Les personnes avec un TSA présentent des troubles du comportement :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'B',
            ],
            [
                'order'          => 5,
                'text'           => 'Le diagnostic de TSA est posé de façon :',
                'options'        => json_encode([
                    'A' => 'Biologique (test sanguin)',
                    'B' => 'Génétique (caryotype)',
                    'C' => 'Clinique (observation de l\'absence ou de la présence de certains comportements)',
                    'D' => 'Je ne sais pas',
                ]),
                'correct_answer' => 'C',
            ],
        ];

        $rows = array_map(fn($q) => array_merge($q, [
            'section'    => 'tsa',
            'is_active'  => true,
            'event_id'   => $eventId,
            'created_at' => now(),
            'updated_at' => now(),
        ]), $questions);

        EvaluationQuestion::insert($rows);

        $this->command->info("Questions TSA (section 9) seedées — liées à l'événement ID: {$eventId}");
    }
}
