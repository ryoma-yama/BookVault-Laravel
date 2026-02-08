<?php

namespace App\Actions\Book;

use App\Concerns\SyncsRelatedEntities;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class UpdateBook
{
    use SyncsRelatedEntities;

    /**
     * Execute the action to update an existing book.
     *
     * @param  Book  $book  The book to update
     * @param  array  $validated  The validated data from the request
     * @return Book The updated book
     */
    public function execute(Book $book, array $validated): Book
    {
        return DB::transaction(function () use ($book, $validated) {
            // Update the book
            $book->update([
                'google_id' => $validated['google_id'] ?? null,
                'isbn_13' => $validated['isbn_13'],
                'title' => $validated['title'],
                'publisher' => $validated['publisher'],
                'published_date' => $validated['published_date'],
                'description' => $validated['description'],
                'image_url' => $validated['image_url'] ?? null,
            ]);

            // Sync authors if provided
            if (isset($validated['authors'])) {
                $this->attachAuthorsByName($book, $validated['authors']);
            }

            // Sync tags if provided
            if (isset($validated['tags'])) {
                $this->attachTagsByName($book, $validated['tags']);
            }

            return $book;
        });
    }
}
