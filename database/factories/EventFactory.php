<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'full_description' => fake()->paragraphs(3, true),
            'date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'end_date' => null,
            'time' => '09h00',
            'end_time' => '17h00',
            'location' => fake()->city(),
            'type' => fake()->randomElement(['workshop', 'celebration', 'seminar', 'gala', 'conference']),
            'status' => 'upcoming',
            'image' => null,
            'agenda' => null,
            'capacity' => fake()->numberBetween(50, 300),
        ];
    }
}
