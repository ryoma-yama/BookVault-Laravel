<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $borrowedAt = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'borrowed_at' => $borrowedAt,
            'due_date' => fake()->dateTimeBetween($borrowedAt, '+30 days'),
            'returned_at' => null,
        ];
    }

    /**
     * Indicate that the loan has been returned.
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => fake()->dateTimeBetween($attributes['borrowed_at'], 'now'),
        ]);
    }
}
