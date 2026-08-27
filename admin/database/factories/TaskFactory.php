<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'assigned_to' => User::factory(),
            'due_date' => now()->addDays($this->faker->numberBetween(1, 14)),
            'priority' => 'normal',
            'status' => 'yapilacak',
        ];
    }
}
