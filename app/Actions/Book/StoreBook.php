<?php

namespace App\Actions\Book;

use App\Concerns\SyncsRelatedEntities;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Support\Facades\DB;

class StoreBook
{
    use SyncsRelatedEntities;

    /**
     * Execute the action to store a new book and its initial copy.
     *
     * @param  array  $validated  The validated data from the request
     * @return Book The newly created book
     */
    public function execute(array $validated): Book
    {
        return DB::transaction(function () use ($validated) {
            // Create the book
            $book = Book::create([
                'google_id' => $validated['google_id'] ?? null,
                'isbn_13' => $validated['isbn_13'],
                'title' => $validated['title'],
                'publisher' => $validated['publisher'],
                'published_date' => $validated['published_date'],
                'description' => $validated['description'],
                'image_url' => $validated['image_url'] ?? null,
            ]);

            // Automatically create one BookCopy with current date as acquired_date
            BookCopy::create([
                'book_id' => $book->id,
                'acquired_date' => now(),
                'discarded_date' => null,
            ]);

            // Attach authors if provided
            if (! empty($validated['authors'])) {
                $this->attachAuthorsByName($book, $validated['authors']);
            }

            // Attach tags if provided
            if (! empty($validated['tags'])) {
                $this->attachTagsByName($book, $validated['tags']);
            }

            return $book;
        });
    }
}
