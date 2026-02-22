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
            'title' => 'Le Grand Salon de l\'Autiste',
            'slug' => Str::slug('Le Grand Salon de l\'Autiste'),
            'description' => 'Conférence et ateliers sur le trouble du spectre autistique et son impact sur la scolarité.',
            'full_description' => 'Une conférence complète sur le trouble du spectre autistique et son impact sur la scolarité.',
            'date' => '2026-04-15',
            'end_date' => '2026-04-16',
            'time' => '08h00',
            'end_time' => '16h00',
            'location' => 'Kinshasa',
            'venue_details' => 'Fleuve Congo Hôtel Kinshasa',
            'type' => 'seminar',
            'status' => 'upcoming',
            'image' => '/galery/gsa_events.jpeg',
            'agenda' => [
                ['day' => 'Jour 1 - 15 Avril 2026', 'time' => '08h00 - 16h00', 'activities' => 'Conférences plénières, ateliers pratiques'],
                ['day' => 'Jour 2 - 16 Avril 2026', 'time' => '08h00 - 16h00', 'activities' => 'Ateliers spécialisés, études de cas'],
            ],
            'price' => null,
            'capacity' => 200,
            'registered' => 0,
            'contact_phone' => '+243 844 338 747',
            'contact_email' => 'info@nlcrdc.org',
            'organizer' => 'Never Limit Children',
            'registration_deadline' => '2026-04-10',
            'sponsors' => [
                'AGEPE',
                'SOFIBANQUE',
                'TIJE',
                'Fondation Denise Nyakeru Tshisekedi',
                'Vodacom',
                'Ecobank',
                'Calugi EL',
                'Socomerg sarl',
                'CANAL+',
                'UNITED',
            ],
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
