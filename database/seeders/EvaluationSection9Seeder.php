<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaluationQuestion;

class EvaluationSection9Seeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'section'        => 'tsa',
                'order'          => 1,
                'text'           => 'Les personnes avec un TSA évitent le contact visuel :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'B',
                'is_active'      => true,
                'event_id'       => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'section'        => 'tsa',
                'order'          => 2,
                'text'           => 'Les personnes avec un TSA présentent une déficience intellectuelle :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'B',
                'is_active'      => true,
                'event_id'       => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'section'        => 'tsa',
                'order'          => 3,
                'text'           => 'Les personnes avec un TSA ont des comportements, intérêts ou activités restreints et répétitifs :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'A',
                'is_active'      => true,
                'event_id'       => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'section'        => 'tsa',
                'order'          => 4,
                'text'           => 'Les personnes avec un TSA présentent des troubles du comportement :',
                'options'        => json_encode(['A' => 'Toujours', 'B' => 'Parfois', 'C' => 'Jamais', 'D' => 'Je ne sais pas']),
                'correct_answer' => 'B',
                'is_active'      => true,
                'event_id'       => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'section'        => 'tsa',
                'order'          => 5,
                'text'           => 'Le diagnostic de TSA est posé de façon :',
                'options'        => json_encode([
                    'A' => 'Biologique (test sanguin)',
                    'B' => 'Génétique (caryotype)',
                    'C' => 'Clinique (observation de l\'absence ou de la présence de certains comportements)',
                    'D' => 'Je ne sais pas',
                ]),
                'correct_answer' => 'C',
                'is_active'      => true,
                'event_id'       => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ];

        EvaluationQuestion::insert($questions);
    }
}
