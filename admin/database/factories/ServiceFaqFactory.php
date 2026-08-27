<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceFaq>
 */
class ServiceFaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'order' => $this->faker->numberBetween(1, 5),
            'question' => $this->faker->sentence().'?',
            'answer' => $this->faker->paragraph(),
        ];
    }
}
