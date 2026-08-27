<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogCategory>
 */
class BlogCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Ceza Hukuku', 'Gayrimenkul', 'İş Hukuku', 'Ticaret Hukuku', 'Aile Hukuku',
            ]),
        ];
    }
}
