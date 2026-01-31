<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = \App\Models\Author::factory()->count(10)->create();
        
        $books = \App\Models\Book::factory()->count(20)->create();

        // Attach authors to books
        $books->each(function ($book) use ($authors) {
            $book->authors()->attach(
                $authors->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        // Create book copies
        $books->each(function ($book) {
            \App\Models\BookCopy::factory()->count(rand(1, 5))->create([
                'book_id' => $book->id,
            ]);
        });
    }
}
