<?php

namespace Database\Factories;

use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactSubmission>
 */
class ContactSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'subject' => $this->faker->randomElement(['gayrimenkul', 'is', 'aile', 'ceza']),
            'message' => $this->faker->paragraph(),
            'kvkk_consent' => true,
            'status' => 'yeni',
        ];
    }
}
