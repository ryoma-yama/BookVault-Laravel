<?php

use App\Models\Book;
use App\Models\Tag;
use App\Models\User;

test('can list all books', function () {
    Book::factory()->count(3)->create();

    $response = $this->getJson('/api/books');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'publisher', 'isbn_13', 'tags', 'reviews'],
            ],
        ]);
});

test('can filter books by tag', function () {
    $fictionTag = Tag::factory()->create(['name' => 'Fiction']);
    $scienceTag = Tag::factory()->create(['name' => 'Science']);

    $book1 = Book::factory()->create(['title' => 'Fiction Book']);
    $book1->tags()->attach($fictionTag);

    $book2 = Book::factory()->create(['title' => 'Science Book']);
    $book2->tags()->attach($scienceTag);

    $response = $this->getJson('/api/books?tags[]=Fiction');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.title'))->toBe('Fiction Book');
});

test('can show a single book with review stats', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $book->reviews()->create([
        'user_id' => $user->id,
        'content' => 'Great book!',
        'rating' => 5,
    ]);

    $response = $this->getJson("/api/books/{$book->id}");

    $response->assertOk()
        ->assertJson([
            'id' => $book->id,
            'title' => $book->title,
            'average_rating' => 5.0,
            'review_count' => 1,
        ]);
});

test('authenticated user can create a book with tags', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/books', [
        'isbn_13' => '9781234567890',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test description',
        'tags' => ['Fiction', 'Adventure'],
    ]);

    $response->assertCreated()
        ->assertJson([
            'title' => 'Test Book',
        ]);

    $book = Book::first();
    expect($book->tags)->toHaveCount(2);
    expect($book->tags->pluck('name')->toArray())->toContain('Fiction', 'Adventure');
});

test('guest cannot create a book', function () {
    $response = $this->postJson('/api/books', [
        'isbn_13' => '9781234567890',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test description',
    ]);

    $response->assertUnauthorized();
});

test('book creation validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/books', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['isbn_13', 'title', 'publisher', 'published_date', 'description']);
});

test('book creation validates isbn_13 length', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/books', [
        'isbn_13' => '123',  // Too short
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test description',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['isbn_13']);
});

test('authenticated user can update a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create(['title' => 'Old Title']);

    $response = $this->actingAs($user)->putJson("/api/books/{$book->id}", [
        'title' => 'New Title',
    ]);

    $response->assertOk()
        ->assertJson([
            'title' => 'New Title',
        ]);
});

test('authenticated user can update book tags', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $oldTag = Tag::factory()->create(['name' => 'OldTag']);
    $book->tags()->attach($oldTag);

    $response = $this->actingAs($user)->putJson("/api/books/{$book->id}", [
        'tags' => ['NewTag1', 'NewTag2'],
    ]);

    $response->assertOk();

    $book->refresh();
    expect($book->tags)->toHaveCount(2);
    expect($book->tags->pluck('name')->toArray())->toContain('NewTag1', 'NewTag2');
    expect($book->tags->pluck('name')->toArray())->not->toContain('OldTag');
});

test('authenticated user can delete a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/api/books/{$book->id}");

    $response->assertNoContent();
    expect(Book::find($book->id))->toBeNull();
});
