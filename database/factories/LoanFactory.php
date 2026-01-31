<?php

namespace Database\Factories;

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
        return [
            'book_copy_id' => \App\Models\BookCopy::factory(),
            'user_id' => \App\Models\User::factory(),
            'borrowed_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'returned_date' => null,
        ];
    }

    public function returned(): static
    {
        return $this->state(function (array $attributes) {
            $borrowedDate = $attributes['borrowed_date'];

            return [
                'returned_date' => fake()->dateTimeBetween($borrowedDate, 'now'),
            ];
        });
    }
}
