<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientPayment>
 */
class ClientPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'method' => 'nakit',
            'amount' => $this->faker->randomFloat(2, 500, 15000),
            'payment_date' => now(),
            'is_postdated' => false,
        ];
    }
}
