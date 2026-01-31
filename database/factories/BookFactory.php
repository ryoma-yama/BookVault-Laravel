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
            'google_id' => 'dummy-id-' . fake()->unique()->numberBetween(0, 99999),
            'isbn_13' => fake()->isbn13(),
            'title' => fake()->sentence(3),
            'publisher' => fake()->company(),
            'published_date' => fake()->date('Y-m-d'),
            'description' => fake()->paragraph(3),
        ];
    }
}
