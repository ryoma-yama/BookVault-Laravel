<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\Tag;

use function Pest\Laravel\get;

test('guest can access book list', function () {
    get(route('books.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('books/index'));
});

test('book list displays all books', function () {
    $books = Book::factory()->count(3)->create();

    get(route('books.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 3)
        );
});

test('can search books by title using full-text search', function () {
    $book1 = Book::factory()->create(['title' => 'Laravel Programming']);
    $book2 = Book::factory()->create(['title' => 'React Development']);
    $book3 = Book::factory()->create(['title' => 'PHP Basics']);

    get(route('books.index', ['search' => 'Laravel']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.title', 'Laravel Programming')
        );
});

test('can search books by description using full-text search', function () {
    $book1 = Book::factory()->create([
        'title' => 'Book One',
        'description' => 'A comprehensive guide to Laravel framework',
    ]);
    $book2 = Book::factory()->create([
        'title' => 'Book Two',
        'description' => 'Learn React from scratch',
    ]);

    get(route('books.index', ['search' => 'Laravel framework']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );
});

test('can search books by author name using full-text search', function () {
    $author1 = Author::create(['name' => 'John Doe']);
    $author2 = Author::create(['name' => 'Jane Smith']);

    $book1 = Book::factory()->create(['title' => 'Book by John']);
    $book1->authors()->attach($author1);

    $book2 = Book::factory()->create(['title' => 'Book by Jane']);
    $book2->authors()->attach($author2);

    // Search should find books by author name in the searchable array
    get(route('books.index', ['search' => 'John']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );
});

test('can search books by ISBN using full-text search', function () {
    $book1 = Book::factory()->create([
        'title' => 'Book One',
        'isbn_13' => '9781234567890',
    ]);
    $book2 = Book::factory()->create([
        'title' => 'Book Two',
        'isbn_13' => '9780987654321',
    ]);

    get(route('books.index', ['search' => '9781234567890']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );
});

test('can search books with multiple keywords', function () {
    $book1 = Book::factory()->create([
        'title' => 'Advanced Laravel Programming',
        'description' => 'Master Laravel framework',
    ]);
    $book2 = Book::factory()->create([
        'title' => 'Basic PHP',
        'description' => 'Introduction to PHP',
    ]);

    get(route('books.index', ['search' => 'Laravel Programming']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );
});

test('can search books with Japanese text', function () {
    $book1 = Book::factory()->create([
        'title' => 'Laravelプログラミング入門',
        'description' => 'Laravel フレームワークの基礎',
    ]);
    $book2 = Book::factory()->create([
        'title' => 'React開発ガイド',
        'description' => 'Reactの基本',
    ]);

    get(route('books.index', ['search' => 'Laravel']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );
});

test('can filter by author without search query', function () {
    $author1 = Author::create(['name' => 'John Doe']);
    $author2 = Author::create(['name' => 'Jane Smith']);

    $book1 = Book::factory()->create(['title' => 'Book by John']);
    $book1->authors()->attach($author1);

    $book2 = Book::factory()->create(['title' => 'Book by Jane']);
    $book2->authors()->attach($author2);

    get(route('books.index', ['author' => 'John']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );
});

test('can search books by publisher', function () {
    $book1 = Book::factory()->create(['publisher' => 'O\'Reilly Media']);
    $book2 = Book::factory()->create(['publisher' => 'Packt Publishing']);

    get(route('books.index', ['publisher' => 'O\'Reilly']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.publisher', 'O\'Reilly Media')
        );
});

test('can search books by tag', function () {
    $tag1 = Tag::factory()->create(['name' => 'programming']);
    $tag2 = Tag::factory()->create(['name' => 'design']);

    $book1 = Book::factory()->create();
    $book1->tags()->attach($tag1);

    $book2 = Book::factory()->create();
    $book2->tags()->attach($tag2);

    get(route('books.index', ['tag' => 'programming']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );
});

test('book list is paginated', function () {
    Book::factory()->count(25)->create();

    get(route('books.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 15)
            ->where('books.per_page', 15)
        );
});

test('can sort books by title ascending', function () {
    Book::factory()->create(['title' => 'Zebra Book']);
    Book::factory()->create(['title' => 'Alpha Book']);

    get(route('books.index', ['sort' => 'title', 'direction' => 'asc']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->where('books.data.0.title', 'Alpha Book')
        );
});

test('can sort books by title descending', function () {
    Book::factory()->create(['title' => 'Zebra Book']);
    Book::factory()->create(['title' => 'Alpha Book']);

    get(route('books.index', ['sort' => 'title', 'direction' => 'desc']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->where('books.data.0.title', 'Zebra Book')
        );
});

test('can sort books by created_at', function () {
    $old = Book::factory()->create(['created_at' => now()->subDays(2)]);
    $new = Book::factory()->create(['created_at' => now()]);

    get(route('books.index', ['sort' => 'created_at', 'direction' => 'desc']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->where('books.data.0.id', $new->id)
        );
});

test('search returns empty result when no matches', function () {
    Book::factory()->create(['title' => 'Laravel Programming']);

    get(route('books.index', ['search' => 'NonExistentBook']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->has('books.data', 0)
        );
});
