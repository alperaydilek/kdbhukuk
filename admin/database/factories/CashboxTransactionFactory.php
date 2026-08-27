<?php

namespace Database\Factories;

use App\Models\CashboxTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashboxTransaction>
 */
class CashboxTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => 'gelir',
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'description' => $this->faker->sentence(),
            'date' => now(),
            'source' => 'manuel',
        ];
    }
}
