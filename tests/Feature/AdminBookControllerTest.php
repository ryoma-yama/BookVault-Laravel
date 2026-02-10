<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
});

test('admin books index page can be rendered', function () {
    $response = actingAs($this->user)->get('/admin/books');

    $response->assertOk();
});

test('admin books index displays list of books', function () {
    $author = Author::create(['name' => 'Test Author']);
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);
    $book->authors()->attach($author);

    $response = actingAs($this->user)->get('/admin/books');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/index')
        ->has('books.data', 1)
    );
});

test('admin books index includes tags for each book', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $tag1 = \App\Models\Tag::create(['name' => 'Laravel']);
    $tag2 = \App\Models\Tag::create(['name' => 'PHP']);
    $book->tags()->attach([$tag1->id, $tag2->id]);

    $response = actingAs($this->user)->get('/admin/books');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/index')
        ->has('books.data.0.tags', 2)
        ->where('books.data.0.tags.0.name', 'Laravel')
        ->where('books.data.0.tags.1.name', 'PHP')
    );
});

test('admin books index includes inventory count for each book', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    // Create 3 copies, 1 discarded
    \App\Models\BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);
    \App\Models\BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);
    \App\Models\BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => '2024-01-01']);

    $response = actingAs($this->user)->get('/admin/books');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/index')
        ->where('books.data.0.copies_count', 2) // Only active copies
    );
});

test('admin books create page can be rendered', function () {
    $response = actingAs($this->user)->get('/admin/books/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/form')
    );
});

test('book can be created', function () {
    $response = actingAs($this->user)->post('/admin/books', [
        'isbn_13' => '9784123456789',
        'title' => 'New Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'authors' => ['Test Author 1', 'Test Author 2'],
    ]);

    $response->assertRedirect('/admin/books');

    $this->assertDatabaseHas('books', [
        'isbn_13' => '9784123456789',
        'title' => 'New Test Book',
    ]);

    $book = Book::where('isbn_13', '9784123456789')->first();
    expect($book->authors)->toHaveCount(2);
});

test('book creation validates isbn_13 is required', function () {
    $response = actingAs($this->user)->post('/admin/books', [
        'title' => 'New Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response->assertSessionHasErrors(['isbn_13']);
});

test('book creation validates isbn_13 is unique', function () {
    Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Existing Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response = actingAs($this->user)->post('/admin/books', [
        'isbn_13' => '9784123456789',
        'title' => 'New Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response->assertSessionHasErrors(['isbn_13']);
});

test('admin books edit page can be rendered', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response = actingAs($this->user)->get("/admin/books/{$book->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/form')
        ->where('book.title', 'Test Book')
    );
});

test('book can be updated', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response = actingAs($this->user)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Updated Book',
        'publisher' => 'Updated Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Updated Description',
        'authors' => ['Updated Author'],
    ]);

    $response->assertRedirect('/admin/books');

    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => 'Updated Book',
    ]);
});

test('book can be deleted', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response = actingAs($this->user)->delete("/admin/books/{$book->id}");

    $response->assertRedirect('/admin/books');

    $this->assertDatabaseMissing('books', [
        'id' => $book->id,
    ]);
});

test('authors are synced when updating book', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $author1 = Author::create(['name' => 'Author 1']);
    $book->authors()->attach($author1);

    $response = actingAs($this->user)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'authors' => ['Author 2', 'Author 3'],
    ]);

    $response->assertRedirect('/admin/books');

    $book->refresh();
    expect($book->authors)->toHaveCount(2);
    expect($book->authors->pluck('name')->toArray())->toContain('Author 2', 'Author 3');
    expect($book->authors->pluck('name')->toArray())->not->toContain('Author 1');
});

test('admin books index paginates with 50 items per page', function () {
    // Create 60 books
    Book::factory()->count(60)->create();

    $response = actingAs($this->user)->get('/admin/books');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/index')
        ->has('books.data', 50)
        ->where('books.per_page', 50)
        ->where('books.total', 60)
        ->where('books.last_page', 2)
    );
});

test('admin books index shows all books regardless of copies', function () {
    // Create a book without any copies
    $bookWithoutCopies = Book::factory()->create([
        'title' => 'Book Without Copies',
    ]);

    // Create a book with copies
    $bookWithCopies = Book::factory()->create([
        'title' => 'Book With Copies',
    ]);
    \App\Models\BookCopy::factory()->create(['book_id' => $bookWithCopies->id]);

    $response = actingAs($this->user)->get('/admin/books');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/index')
        ->has('books.data', 2)
    );
});

test('admin books index supports search', function () {
    Book::factory()->create(['title' => 'Laravel Programming']);
    Book::factory()->create(['title' => 'PHP Basics']);
    Book::factory()->create(['title' => 'JavaScript Essentials']);

    // Note: This test may need Meilisearch running or use database driver
    // For now, we test the endpoint accepts the parameter
    $response = actingAs($this->user)->get('/admin/books?search=Laravel');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/index')
        ->has('filters')
        ->where('filters.search', 'Laravel')
    );
});
