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

            // Sync book copies if provided
            if (isset($validated['book_copies'])) {
                $this->syncBookCopies($book, $validated['book_copies']);
            }

            return $book;
        });
    }

    /**
     * Sync book copies for the book.
     * - Keeps existing copies that are in the request
     * - Creates new copies with current date as acquired_date
     * - Marks removed copies as discarded with current date
     *
     * @param  Book  $book
     * @param  array  $copies  Array of copy data with 'id' key (null for new copies)
     * @return void
     */
    private function syncBookCopies(Book $book, array $copies): void
    {
        $submittedIds = collect($copies)
            ->pluck('id')
            ->filter()
            ->all();

        // Get all active copies for this book
        $activeCopies = $book->copies()->active()->get();

        // Mark copies as discarded if they are not in the submitted list
        $activeCopies->each(function ($copy) use ($submittedIds) {
            if (! in_array($copy->id, $submittedIds)) {
                $copy->update(['discarded_date' => now()]);
            }
        });

        // Create new copies (where id is null)
        $newCopiesCount = collect($copies)
            ->filter(fn ($copy) => is_null($copy['id']))
            ->count();

        for ($i = 0; $i < $newCopiesCount; $i++) {
            $book->copies()->create([
                'acquired_date' => now(),
                'discarded_date' => null,
            ]);
        }
    }
}
