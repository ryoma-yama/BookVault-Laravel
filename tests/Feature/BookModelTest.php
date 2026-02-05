<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Tag;

describe('Book Model', function () {
    it('can create a book using factory', function () {
        $book = Book::factory()->create();

        expect($book)->toBeInstanceOf(Book::class)
            ->and($book->google_id)->toBeString()
            ->and($book->title)->toBeString();
    });

    it('has required fillable attributes', function () {
        $book = Book::factory()->create([
            'title' => 'Test Book',
            'publisher' => 'Test Publisher',
        ]);

        expect($book->title)->toBe('Test Book')
            ->and($book->publisher)->toBe('Test Publisher');
    });

    it('can retrieve books from database', function () {
        Book::factory()->count(3)->create();

        expect(Book::count())->toBe(3);
    });
});

describe('Book Searchable Array', function () {
    it('includes basic fields in searchable array', function () {
        $book = Book::factory()->make([
            'id' => 1,
            'title' => 'Laravel Guide',
            'publisher' => 'Tech Publisher',
            'description' => 'A comprehensive guide',
            'isbn_13' => '9781234567890',
        ]);

        $searchableArray = $book->toSearchableArray();

        expect($searchableArray)->toHaveKey('id')
            ->and($searchableArray)->toHaveKey('title')
            ->and($searchableArray)->toHaveKey('publisher')
            ->and($searchableArray)->toHaveKey('description')
            ->and($searchableArray)->not->toHaveKey('isbn_13') // ISBN should not be in searchable array
            ->and($searchableArray['title'])->toBe('Laravel Guide')
            ->and($searchableArray['publisher'])->toBe('Tech Publisher');
    });

    it('includes authors as comma-separated string in searchable array for non-database drivers', function () {
        // Temporarily set driver to meilisearch for this test only
        $originalDriver = config('scout.driver');
        config(['scout.driver' => 'meilisearch']);

        Book::withoutSyncingToSearch(function () {
            $book = Book::factory()->create();
            $author1 = Author::create(['name' => 'John Doe']);
            $author2 = Author::create(['name' => 'Jane Smith']);
            $book->authors()->attach([$author1->id, $author2->id]);

            // Load the relations before getting searchable array
            $book->load(['authors', 'tags']);
            $searchableArray = $book->toSearchableArray();

            expect($searchableArray)->toHaveKey('authors')
                ->and($searchableArray['authors'])->toBe('John Doe, Jane Smith');
        });

        // Restore original driver
        config(['scout.driver' => $originalDriver]);
    });

    it('includes tags as comma-separated string in searchable array for non-database drivers', function () {
        // Temporarily set driver to meilisearch for this test only
        $originalDriver = config('scout.driver');
        config(['scout.driver' => 'meilisearch']);

        Book::withoutSyncingToSearch(function () {
            $book = Book::factory()->create();
            $tag1 = Tag::create(['name' => 'programming']);
            $tag2 = Tag::create(['name' => 'web-development']);
            $tag3 = Tag::create(['name' => 'php']);
            $book->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

            // Load the relations before getting searchable array
            $book->load(['authors', 'tags']);
            $searchableArray = $book->toSearchableArray();

            expect($searchableArray)->toHaveKey('tags')
                ->and($searchableArray['tags'])->toBe('programming, web-development, php');
        });

        // Restore original driver
        config(['scout.driver' => $originalDriver]);
    });

    it('does not include authors and tags for database driver', function () {
        Book::withoutSyncingToSearch(function () {
            $book = Book::factory()->create();
            $author = Author::create(['name' => 'John Doe']);
            $tag = Tag::create(['name' => 'programming']);
            $book->authors()->attach($author->id);
            $book->tags()->attach($tag->id);

            $book->load(['authors', 'tags']);
            $searchableArray = $book->toSearchableArray();

            expect($searchableArray)->not->toHaveKey('authors')
                ->and($searchableArray)->not->toHaveKey('tags');
        });
    });

    it('handles empty authors and tags gracefully', function () {
        // Temporarily set driver to meilisearch for this test only
        $originalDriver = config('scout.driver');
        config(['scout.driver' => 'meilisearch']);

        Book::withoutSyncingToSearch(function () {
            $book = Book::factory()->create();
            $book->load(['authors', 'tags']);
            $searchableArray = $book->toSearchableArray();

            expect($searchableArray)->toHaveKey('authors')
                ->and($searchableArray['authors'])->toBe('')
                ->and($searchableArray)->toHaveKey('tags')
                ->and($searchableArray['tags'])->toBe('');
        });

        // Restore original driver
        config(['scout.driver' => $originalDriver]);
    });
});
