<?php

use App\Models\Author;
use App\Models\Book;

use function Pest\Laravel\get;

test('root route can be accessed by unauthenticated users', function () {
    $response = get('/');

    $response->assertOk();
});

test('root route displays books index component', function () {
    $author = Author::create(['name' => 'Test Author']);
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);
    $book->authors()->attach($author);
    
    // Add a valid book copy so the book appears in the list
    \App\Models\BookCopy::factory()->create([
        'book_id' => $book->id,
        'discarded_date' => null,
    ]);

    $response = get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/index')
        ->has('books.data', 1)
        ->where('books.data.0.title', 'Test Book')
    );
});

test('books route should not exist', function () {
    $response = get('/books');

    $response->assertNotFound();
});
