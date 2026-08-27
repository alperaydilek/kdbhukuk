<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientDebt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientDebt>
 */
class ClientDebtFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'type' => 'ucret',
            'title' => 'Vekalet Ücreti',
            'amount' => $this->faker->randomFloat(2, 1000, 25000),
            'due_date' => now()->addDays($this->faker->numberBetween(7, 60)),
            'status' => 'bekliyor',
        ];
    }
}
