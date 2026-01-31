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
            'google_id' => fake()->unique()->regexify('[A-Za-z0-9]{12}'),
            'isbn_13' => fake()->unique()->isbn13(),
            'title' => fake()->sentence(3),
            'publisher' => fake()->company(),
            'published_date' => fake()->date(),
            'description' => fake()->paragraph(),
        ];
    }
}
