<?php

namespace Database\Factories;

use App\Models\SmsTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsTemplate>
 */
class SmsTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'body' => 'Sayın {{client_name}}, '.$this->faker->sentence(),
        ];
    }
}
