<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

test('can list all reviews', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    Review::factory()->count(3)->create([
        'book_id' => $book->id,
        'user_id' => $user->id,
    ]);

    $response = $this->getJson('/api/reviews');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'content', 'rating', 'user', 'book'],
            ],
        ]);
});

test('can filter reviews by book', function () {
    $book1 = Book::factory()->create();
    $book2 = Book::factory()->create();
    $user = User::factory()->create();

    Review::factory()->create(['book_id' => $book1->id, 'user_id' => $user->id]);
    Review::factory()->create(['book_id' => $book1->id, 'user_id' => $user->id]);
    Review::factory()->create(['book_id' => $book2->id, 'user_id' => $user->id]);

    $response = $this->getJson("/api/reviews?book_id={$book1->id}");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

test('authenticated user can create a review', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'content' => 'This is a great book with lots of interesting content!',
        'rating' => 5,
    ]);

    $response->assertCreated()
        ->assertJson([
            'content' => 'This is a great book with lots of interesting content!',
            'rating' => 5,
        ]);

    expect(Review::count())->toBe(1);
});

test('guest cannot create a review', function () {
    $book = Book::factory()->create();

    $response = $this->postJson('/api/reviews', [
        'book_id' => $book->id,
        'content' => 'This is a great book!',
        'rating' => 5,
    ]);

    $response->assertUnauthorized();
});

test('review creation validates rating range', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'content' => 'This is a great book!',
        'rating' => 6,  // Invalid: max is 5
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['rating']);
});

test('review creation validates minimum rating', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'content' => 'This is a great book!',
        'rating' => 0,  // Invalid: min is 1
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['rating']);
});

test('review creation validates content length', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'content' => 'Short',  // Too short (min 10 characters)
        'rating' => 5,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

test('review creator can update their review', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'content' => 'Original content here for testing purposes.',
        'rating' => 3,
    ]);

    $response = $this->actingAs($user)->putJson("/api/reviews/{$review->id}", [
        'content' => 'Updated content with more details and information.',
        'rating' => 5,
    ]);

    $response->assertOk()
        ->assertJson([
            'content' => 'Updated content with more details and information.',
            'rating' => 5,
        ]);
});

test('user cannot update another users review', function () {
    $book = Book::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $owner->id,
        'content' => 'Original content here.',
        'rating' => 3,
    ]);

    $response = $this->actingAs($otherUser)->putJson("/api/reviews/{$review->id}", [
        'content' => 'Trying to update.',
        'rating' => 5,
    ]);

    $response->assertForbidden();
});

test('review creator can delete their review', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'content' => 'Content to be deleted.',
        'rating' => 4,
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/reviews/{$review->id}");

    $response->assertNoContent();
    expect(Review::find($review->id))->toBeNull();
});

test('user cannot delete another users review', function () {
    $book = Book::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $owner->id,
        'content' => 'Original content.',
        'rating' => 4,
    ]);

    $response = $this->actingAs($otherUser)->deleteJson("/api/reviews/{$review->id}");

    $response->assertForbidden();
    expect(Review::find($review->id))->not->toBeNull();
});
