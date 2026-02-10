<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventPriceFactory extends Factory
{
    protected $model = EventPrice::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'category' => fake()->randomElement(['medecin', 'etudiant', 'parent', 'enseignant']),
            'duration_type' => fake()->randomElement(['per_day', 'full_event']),
            'amount' => fake()->randomFloat(2, 10, 200),
            'currency' => 'USD',
            'label' => null,
            'description' => null,
        ];
    }
}
