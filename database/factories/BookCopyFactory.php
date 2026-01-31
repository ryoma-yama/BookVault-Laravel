<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookCopy>
 */
class BookCopyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'acquired_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'discarded_date' => null,
        ];
    }

    /**
     * Indicate that the copy is discarded.
     */
    public function discarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'discarded_date' => fake()->dateTimeBetween($attributes['acquired_date'], 'now'),
        ]);
    }
}
