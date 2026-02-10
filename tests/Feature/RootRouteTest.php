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

test('root route returns 24 items per page', function () {
    $author = Author::create(['name' => 'Test Author']);
    
    // Create 30 books to test pagination
    for ($i = 1; $i <= 30; $i++) {
        $book = Book::create([
            'isbn_13' => '978412345678' . str_pad($i, 1, '0', STR_PAD_LEFT),
            'title' => "Test Book {$i}",
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
    }

    $response = get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/index')
        ->has('books.data', 24)
        ->where('books.per_page', 24)
        ->where('books.current_page', 1)
        ->where('books.total', 30)
    );
});

test('root route page 2 returns items from 25th onwards', function () {
    $author = Author::create(['name' => 'Test Author']);
    
    // Create 30 books to test pagination
    for ($i = 1; $i <= 30; $i++) {
        $book = Book::create([
            'isbn_13' => '978412345678' . str_pad($i, 1, '0', STR_PAD_LEFT),
            'title' => "Test Book {$i}",
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
    }

    $response = get('/?page=2');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/index')
        ->has('books.data', 6) // 30 total - 24 on page 1 = 6 on page 2
        ->where('books.per_page', 24)
        ->where('books.current_page', 2)
        ->where('books.total', 30)
    );
});

test('root route includes pagination metadata', function () {
    $author = Author::create(['name' => 'Test Author']);
    
    // Create 50 books to test pagination metadata
    for ($i = 1; $i <= 50; $i++) {
        $book = Book::create([
            'isbn_13' => '978412345678' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'title' => "Test Book {$i}",
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
    }

    $response = get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/index')
        ->has('books.current_page')
        ->has('books.last_page')
        ->has('books.per_page')
        ->has('books.total')
        ->where('books.current_page', 1)
        ->where('books.last_page', 3) // ceil(50 / 24) = 3
        ->where('books.per_page', 24)
        ->where('books.total', 50)
    );
});
