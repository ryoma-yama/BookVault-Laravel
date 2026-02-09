<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

test('review can be created', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'comment' => 'Great book!',
        'is_recommended' => true,
    ]);

    expect($review)->toBeInstanceOf(Review::class)
        ->and($review->is_recommended)->toBe(true)
        ->and($review->comment)->toBe('Great book!');
});

test('review belongs to a book', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'comment' => 'Great book!',
        'is_recommended' => true,
    ]);

    expect($review->book->id)->toBe($book->id);
});

test('review belongs to a user', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'comment' => 'Great book!',
        'is_recommended' => true,
    ]);

    expect($review->user->id)->toBe($user->id);
});

test('deleting a user cascades to their reviews', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'comment' => 'Great book!',
        'is_recommended' => true,
    ]);

    $reviewId = $review->id;
    $user->delete();

    expect(Review::find($reviewId))->toBeNull();
});
