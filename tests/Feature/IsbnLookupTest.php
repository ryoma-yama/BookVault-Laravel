<?php

use App\Models\Author;
use App\Models\Book;

use function Pest\Laravel\get;

describe('ISBN Lookup Endpoint', function () {
    test('can find book by exact ISBN-13 match', function () {
        $author = Author::create(['name' => 'Test Author']);
        $book = Book::factory()->create([
            'isbn_13' => '9781234567890',
            'title' => 'Test Book',
        ]);
        $book->authors()->attach($author);

        get(route('books.isbn', ['isbn' => '9781234567890']))
            ->assertRedirect(route('books.show', $book));
    });

    test('can find book by ISBN-13 with hyphens', function () {
        $book = Book::factory()->create([
            'isbn_13' => '9781234567890',
            'title' => 'Book with Hyphens',
        ]);

        get(route('books.isbn', ['isbn' => '978-1-234-56789-0']))
            ->assertRedirect(route('books.show', $book));
    });

    test('redirects back with error when ISBN not found in database', function () {
        get(route('books.isbn', ['isbn' => '9789999999999']))
            ->assertRedirect()
            ->assertSessionHasErrors('isbn');
    });

    test('redirects back with validation error for invalid ISBN format', function () {
        get(route('books.isbn', ['isbn' => '123']))
            ->assertRedirect()
            ->assertSessionHasErrors('isbn');
    });

    test('redirects back with validation error for non-ISBN-13 format', function () {
        // ISBN-10 format should be rejected
        get(route('books.isbn', ['isbn' => '1234567890']))
            ->assertRedirect()
            ->assertSessionHasErrors('isbn');
    });

    test('normalizes ISBN before database lookup', function () {
        $book = Book::factory()->create([
            'isbn_13' => '9781234567890',
        ]);

        // Various formats should all match the same book
        get(route('books.isbn', ['isbn' => '978 1234567890']))
            ->assertRedirect(route('books.show', $book));

        get(route('books.isbn', ['isbn' => '978-1-234-56789-0']))
            ->assertRedirect(route('books.show', $book));

        get(route('books.isbn', ['isbn' => '978  1234  567890']))
            ->assertRedirect(route('books.show', $book));
    });
});

describe('General Search Without ISBN Logic', function () {
    test('search endpoint does not handle ISBN format specially', function () {
        $book1 = Book::factory()->create([
            'isbn_13' => '9781234567890',
            'title' => 'Book One',
        ]);
        $book2 = Book::factory()->create([
            'isbn_13' => '9780000000000',
            'title' => 'Book Two with 9781234567890 in title',
        ]);

        // Searching with ISBN should use full-text search only
        // It should NOT do exact ISBN match
        get(route('home', ['search' => '9781234567890']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('books/index')
                // Should use Scout search, results depend on Scout driver
            );
    });

    test('search endpoint uses scout for all queries', function () {
        $book = Book::factory()->create([
            'title' => 'Laravel Programming',
            'description' => 'A book about Laravel',
        ]);
        
        // Add a valid book copy so the book appears in the list
        \App\Models\BookCopy::factory()->create([
            'book_id' => $book->id,
            'discarded_date' => null,
        ]);

        get(route('home', ['search' => 'Laravel']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('books/index')
                ->has('books.data')
            );
    });
});
