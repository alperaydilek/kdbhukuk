<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_category_id' => BlogCategory::factory(),
            'title' => rtrim($this->faker->sentence(6), '.'),
            'excerpt' => $this->faker->paragraph(),
            'content' => '<p>'.implode('</p><p>', $this->faker->paragraphs(6)).'</p>',
            'status' => 'yayinda',
            'published_at' => now(),
        ];
    }
}
