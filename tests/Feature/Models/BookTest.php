<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\Tag;
use App\Models\User;

test('book can be created with required fields', function () {
    $book = Book::create([
        'isbn_13' => '9781234567890',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test description',
    ]);

    expect($book)->toBeInstanceOf(Book::class)
        ->and($book->isbn_13)->toBe('9781234567890')
        ->and($book->title)->toBe('Test Book');
});

test('book can have tags', function () {
    $book = Book::factory()->create();
    $tag1 = Tag::factory()->create(['name' => 'Fiction']);
    $tag2 = Tag::factory()->create(['name' => 'Mystery']);

    $book->tags()->attach([$tag1->id, $tag2->id]);

    expect($book->tags)->toHaveCount(2)
        ->and($book->tags->pluck('name')->toArray())->toContain('Fiction', 'Mystery');
});

test('book can have reviews', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'content' => 'Great book!',
        'rating' => 5,
    ]);

    expect($book->reviews)->toHaveCount(1)
        ->and($book->reviews->first()->content)->toBe('Great book!');
});

test('book can calculate average rating', function () {
    $book = Book::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Review::create([
        'book_id' => $book->id,
        'user_id' => $user1->id,
        'content' => 'Good',
        'rating' => 4,
    ]);

    Review::create([
        'book_id' => $book->id,
        'user_id' => $user2->id,
        'content' => 'Excellent',
        'rating' => 5,
    ]);

    expect($book->averageRating())->toBe(4.5);
});

test('book can count reviews', function () {
    $book = Book::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Review::create([
        'book_id' => $book->id,
        'user_id' => $user1->id,
        'content' => 'Good',
        'rating' => 4,
    ]);

    Review::create([
        'book_id' => $book->id,
        'user_id' => $user2->id,
        'content' => 'Excellent',
        'rating' => 5,
    ]);

    expect($book->reviewCount())->toBe(2);
});

test('deleting a book cascades to its reviews', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'content' => 'Test review',
        'rating' => 5,
    ]);

    $reviewId = $review->id;
    $book->delete();

    expect(Review::find($reviewId))->toBeNull();
});

test('deleting a book detaches its tags', function () {
    $book = Book::factory()->create();
    $tag = Tag::factory()->create();

    $book->tags()->attach($tag->id);

    $book->delete();

    expect($tag->fresh()->books)->toHaveCount(0);
});
