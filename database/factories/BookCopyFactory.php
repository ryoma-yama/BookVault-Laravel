<?php

namespace Database\Factories;

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
            'book_id' => \App\Models\Book::factory(),
            'acquired_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'discarded_date' => null,
        ];
    }

    public function discarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'discarded_date' => fake()->dateTimeBetween('now', '+1 year'),
        ]);
    }
}
