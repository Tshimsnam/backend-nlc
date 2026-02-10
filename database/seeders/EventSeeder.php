<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::create([
            'title' => 'Le trouble du spectre autistique et la scolarité',
            'slug' => Str::slug('Le trouble du spectre autistique et la scolarité'),
            'description' => 'Conférence et ateliers sur le trouble du spectre autistique et son impact sur la scolarité.',
            'full_description' => 'Une conférence complète sur le trouble du spectre autistique et son impact sur la scolarité.',
            'date' => '2025-04-03',
            'end_date' => '2025-04-04',
            'time' => '09h00',
            'end_time' => '17h00',
            'location' => 'Kitumaini, Paris',
            'type' => 'seminar',
            'status' => 'upcoming',
            'image' => '/galery/NLC images15.jpg',
            'agenda' => [
                ['day' => 'Jour 1 - 03 Avril 2025', 'time' => '09h00 - 17h00', 'activities' => 'Conférences plénières, ateliers pratiques'],
                ['day' => 'Jour 2 - 04 Avril 2025', 'time' => '09h00 - 17h00', 'activities' => 'Ateliers spécialisés, études de cas'],
            ],
            'price' => null,
            'capacity' => 200,
            'registered' => 0,
        ]);

        $prices = [
            ['category' => 'medecin', 'duration_type' => 'full_event', 'amount' => 50, 'label' => 'Médecin'],
            ['category' => 'etudiant', 'duration_type' => 'per_day', 'amount' => 15, 'label' => 'Étudiants', 'description' => '15$/jour'],
            ['category' => 'etudiant', 'duration_type' => 'full_event', 'amount' => 20, 'label' => 'Étudiants', 'description' => '20$ deux jours'],
            ['category' => 'parent', 'duration_type' => 'per_day', 'amount' => 15, 'label' => 'Parents', 'description' => '15$/jour'],
            ['category' => 'enseignant', 'duration_type' => 'per_day', 'amount' => 20, 'label' => 'Enseignants', 'description' => '20$/jour'],
        ];

        foreach ($prices as $p) {
            EventPrice::create([
                'event_id' => $event->id,
                'category' => $p['category'],
                'duration_type' => $p['duration_type'],
                'amount' => $p['amount'],
                'currency' => 'USD',
                'label' => $p['label'] ?? null,
                'description' => $p['description'] ?? null,
            ]);
        }

        $this->command->info('Événement et tarifs créés avec succès.');
    }
}
