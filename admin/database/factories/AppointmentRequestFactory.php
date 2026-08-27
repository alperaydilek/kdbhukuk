<?php

namespace Database\Factories;

use App\Models\AppointmentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppointmentRequest>
 */
class AppointmentRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'status' => 'bekliyor',
        ];
    }
}
