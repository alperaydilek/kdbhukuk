<?php

namespace Database\Factories;

use App\Models\MailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailTemplate>
 */
class MailTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'subject' => $this->faker->sentence(4),
            'body' => '<p>Sayın {{client_name}},</p><p>'.$this->faker->paragraph().'</p>',
        ];
    }
}
