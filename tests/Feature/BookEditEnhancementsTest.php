<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Tag;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

// ISBN Duplicate Check Tests
test('isbn duplicate check prevents creating book with existing isbn', function () {
    Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Existing Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response = actingAs($this->admin)->post('/admin/books', [
        'isbn_13' => '9784123456789',
        'title' => 'New Book',
        'publisher' => 'New Publisher',
        'published_date' => '2024-02-01',
        'description' => 'New Description',
    ]);

    $response->assertSessionHasErrors(['isbn_13']);
    expect(Book::where('title', 'New Book')->exists())->toBeFalse();
});

// BookCopy Management Tests
test('book edit page loads with active book copies only', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    // Create active copy
    BookCopy::create([
        'book_id' => $book->id,
        'acquired_date' => now()->subDays(10),
        'discarded_date' => null,
    ]);

    // Create discarded copy (should not be loaded)
    BookCopy::create([
        'book_id' => $book->id,
        'acquired_date' => now()->subDays(20),
        'discarded_date' => now()->subDays(5),
    ]);

    $response = actingAs($this->admin)->get("/admin/books/{$book->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/form')
        ->has('book.copies', 1) // Only active copy
        ->where('book.copies.0.discarded_date', null)
    );
});

test('adding book copy sets acquired_date to current date', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $today = now()->format('Y-m-d');

    $response = actingAs($this->admin)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'book_copies' => [
            ['id' => null], // New copy
        ],
    ]);

    $response->assertRedirect('/admin/books');

    $book->refresh();
    expect($book->copies)->toHaveCount(1);
    expect($book->copies->first()->acquired_date->format('Y-m-d'))->toBe($today);
    expect($book->copies->first()->discarded_date)->toBeNull();
});

test('removing book copy sets discarded_date instead of physical deletion', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $copy = BookCopy::create([
        'book_id' => $book->id,
        'acquired_date' => now()->subDays(10),
        'discarded_date' => null,
    ]);

    $today = now()->format('Y-m-d');

    $response = actingAs($this->admin)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'book_copies' => [], // Empty array means all copies should be "removed"
    ]);

    $response->assertRedirect('/admin/books');

    // Copy should still exist but with discarded_date set
    $copy->refresh();
    expect($copy->exists())->toBeTrue();
    expect($copy->discarded_date)->not->toBeNull();
    expect($copy->discarded_date->format('Y-m-d'))->toBe($today);
});

test('book copy can be kept during update', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $copy = BookCopy::create([
        'book_id' => $book->id,
        'acquired_date' => now()->subDays(10),
        'discarded_date' => null,
    ]);

    $response = actingAs($this->admin)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Updated Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'book_copies' => [
            ['id' => $copy->id], // Keep existing copy
        ],
    ]);

    $response->assertRedirect('/admin/books');

    $copy->refresh();
    expect($copy->discarded_date)->toBeNull();
    expect($book->copies()->active()->count())->toBe(1);
});

test('multiple book copies can be added at once', function () {
    $book = App\Models\Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $today = now()->format('Y-m-d');

    $response = actingAs($this->admin)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'book_copies' => [
            ['id' => null],
            ['id' => null],
            ['id' => null],
        ],
    ]);

    $response->assertRedirect('/admin/books');

    $book->refresh();
    expect($book->copies()->active()->count())->toBe(3);
    $book->copies->each(function ($copy) use ($today) {
        expect($copy->acquired_date->format('Y-m-d'))->toBe($today);
        expect($copy->discarded_date)->toBeNull();
    });
});

// Tag Management Tests
test('book edit page loads with tags', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $tag1 = Tag::create(['name' => 'Fiction']);
    $tag2 = Tag::create(['name' => 'Mystery']);
    $book->tags()->attach([$tag1->id, $tag2->id]);

    $response = actingAs($this->admin)->get("/admin/books/{$book->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/books/form')
        ->has('book.tags', 2)
    );
});

test('tags are synced correctly when updating book', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $tag1 = Tag::create(['name' => 'Fiction']);
    $book->tags()->attach($tag1->id);

    $response = actingAs($this->admin)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'tags' => ['Mystery', 'Thriller'],
    ]);

    $response->assertRedirect('/admin/books');

    $book->refresh();
    expect($book->tags)->toHaveCount(2);
    expect($book->tags->pluck('name')->toArray())->toContain('Mystery', 'Thriller');
    expect($book->tags->pluck('name')->toArray())->not->toContain('Fiction');
});

test('existing tags are reused when updating book', function () {
    $book = Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    // Create existing tag
    Tag::create(['name' => 'Fiction']);
    $initialTagCount = Tag::count();

    $response = actingAs($this->admin)->put("/admin/books/{$book->id}", [
        'isbn_13' => '9784123456789',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
        'tags' => ['Fiction'], // Reuse existing tag
    ]);

    $response->assertRedirect('/admin/books');

    // No new tag should be created
    expect(Tag::count())->toBe($initialTagCount);
    $book->refresh();
    expect($book->tags->first()->name)->toBe('Fiction');
});
