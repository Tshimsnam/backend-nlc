<?php

namespace Database\Factories;

use App\Models\EventPrice;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        $price = EventPrice::factory()->create();

        return [
            'event_id' => $price->event_id,
            'event_price_id' => $price->id,
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'category' => $price->category,
            'days' => 1,
            'amount' => $price->amount,
            'currency' => $price->currency,
            'reference' => strtoupper(Str::random(10)),
            'pay_type' => 'maxicash',
            'pay_sub_type' => null,
        ];
    }
}
