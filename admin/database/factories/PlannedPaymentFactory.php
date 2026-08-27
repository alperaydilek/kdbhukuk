<?php

namespace Database\Factories;

use App\Models\PlannedPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlannedPayment>
 */
class PlannedPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'amount' => $this->faker->randomFloat(2, 500, 20000),
            'due_date' => now()->addDays($this->faker->numberBetween(1, 30)),
            'status' => 'bekliyor',
        ];
    }
}
