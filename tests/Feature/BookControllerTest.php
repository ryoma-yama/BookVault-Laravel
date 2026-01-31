<?php

use App\Models\Author;
use App\Models\Book;

use function Pest\Laravel\get;

test('books index page can be rendered', function () {
    $response = get('/books');

    $response->assertOk();
});

test('books index displays list of books', function () {
    $author = Author::create(['name' => 'Test Author']);
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);
    $book->authors()->attach($author);

    $response = get('/books');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/index')
        ->has('books.data', 1)
        ->where('books.data.0.title', 'Test Book')
    );
});

test('book show page can be rendered', function () {
    $author = Author::create(['name' => 'Test Author']);
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);
    $book->authors()->attach($author);

    $response = get("/books/{$book->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->where('book.title', 'Test Book')
        ->has('book.authors', 1)
    );
});
