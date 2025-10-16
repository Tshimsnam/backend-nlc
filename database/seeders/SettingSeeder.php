<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use Illuminate\Support\Str;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'id' => Str::uuid(),
                'key' => 'app_name',
                'value' => ['name' => 'NLC - Neuro Learning Center'],
                'category' => 'general',
                'description' => 'Nom de l\'application',
                'is_public' => true,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'default_appointment_duration',
                'value' => ['duration' => 60],
                'category' => 'general',
                'description' => 'Durée par défaut des rendez-vous en minutes',
                'is_public' => false,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'notification_email_enabled',
                'value' => ['enabled' => true],
                'category' => 'notifications',
                'description' => 'Activer les notifications par email',
                'is_public' => false,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'max_login_attempts',
                'value' => ['attempts' => 5],
                'category' => 'security',
                'description' => 'Nombre maximum de tentatives de connexion',
                'is_public' => false,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'backup_frequency',
                'value' => ['frequency' => 'daily', 'time' => '02:00'],
                'category' => 'backup',
                'description' => 'Fréquence des sauvegardes automatiques',
                'is_public' => false,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'organization_address',
                'value' => [
                    'street' => '123 Rue de l\'Éducation',
                    'city' => 'Paris',
                    'postal_code' => '75001',
                    'country' => 'France',
                ],
                'category' => 'organization',
                'description' => 'Adresse de l\'organisation',
                'is_public' => true,
            ],
            [
                'id' => Str::uuid(),
                'key' => 'organization_contact',
                'value' => [
                    'phone' => '+33 1 23 45 67 89',
                    'email' => 'contact@nlc.fr',
                ],
                'category' => 'organization',
                'description' => 'Coordonnées de contact',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        $this->command->info('Paramètres créés avec succès!');
    }
}
