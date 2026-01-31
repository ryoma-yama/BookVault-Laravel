<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 books with varying numbers of copies
        Book::factory()
            ->count(5)
            ->create()
            ->each(function ($book) {
                // Create 2-5 copies for each book
                $copyCount = rand(2, 5);

                BookCopy::factory()
                    ->count($copyCount)
                    ->create(['book_id' => $book->id]);

                // Mark some copies as discarded (20% chance)
                $book->copies->random(min(1, $copyCount))->each(function ($copy) {
                    if (rand(1, 5) === 1) {
                        $copy->update([
                            'discarded_date' => now()->subDays(rand(1, 365)),
                        ]);
                    }
                });
            });
    }
}
