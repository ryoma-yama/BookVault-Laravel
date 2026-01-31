<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'google_id' => fake()->optional()->regexify('[A-Za-z0-9]{12}'),
            'isbn' => fake()->optional()->isbn13(),
            'isbn_13' => fake()->optional()->isbn13(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'published_date' => fake()->optional()->date(),
            'description' => fake()->optional()->paragraph(),
            'image_url' => fake()->optional()->imageUrl(),
        ];
    }
}
