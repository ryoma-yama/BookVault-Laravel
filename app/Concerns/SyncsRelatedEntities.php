<?php

namespace App\Concerns;

use App\Models\Author;
use App\Models\Book;
use App\Models\Tag;

/**
 * Trait for synchronizing related entities (authors, tags) with books.
 *
 * Provides domain-focused methods for attaching authors and tags to books,
 * using first-or-create pattern to avoid duplicates.
 */
trait SyncsRelatedEntities
{
    /**
     * Attach authors to a book by name.
     * Creates new authors if they don't exist.
     */
    protected function attachAuthorsByName(Book $book, array $authorNames): void
    {
        $authorIds = collect($authorNames)
            ->filter(fn (string $name) => trim($name) !== '')
            ->map(fn (string $name) => Author::firstOrCreate(['name' => $name])->id)
            ->all();

        $book->authors()->sync($authorIds);
    }

    /**
     * Attach tags to a book by name.
     * Creates new tags if they don't exist.
     */
    protected function attachTagsByName(Book $book, array $tagNames): void
    {
        $tagIds = collect($tagNames)
            ->filter(fn (string $name) => trim($name) !== '')
            ->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id)
            ->all();

        $book->tags()->sync($tagIds);
    }
}
