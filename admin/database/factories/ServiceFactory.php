<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->unique()->randomElement([
            'Ceza Hukuku', 'Gayrimenkul Hukuku', 'Ticaret Hukuku', 'İş Hukuku',
            'Aile Hukuku', 'Miras Hukuku', 'İcra ve İflas Hukuku', 'Sözleşmeler Hukuku',
            'Tazminat Hukuku', 'Hukuki Danışmanlık',
        ]);

        return [
            'order' => $this->faker->numberBetween(1, 10),
            'title' => $title,
            'short_description' => $this->faker->sentence(12),
            'description' => $this->faker->paragraphs(2, true),
            'covers' => $this->faker->paragraphs(2, true),
            'scope' => $this->faker->paragraphs(2, true),
            'process_steps' => [
                ['title' => 'Ön Görüşme', 'description' => $this->faker->sentence()],
                ['title' => 'Değerlendirme', 'description' => $this->faker->sentence()],
            ],
            'why_important' => $this->faker->paragraph(),
            'is_published' => true,
        ];
    }
}
