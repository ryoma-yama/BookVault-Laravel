<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

test('can list all reviews', function () {
    $book = Book::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    Review::factory()->create([
        'book_id' => $book->id,
        'user_id' => $user1->id,
    ]);
    Review::factory()->create([
        'book_id' => $book->id,
        'user_id' => $user2->id,
    ]);
    Review::factory()->create([
        'book_id' => $book->id,
        'user_id' => $user3->id,
    ]);

    $response = $this->getJson('/api/reviews');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'comment', 'is_recommended', 'user', 'book'],
            ],
        ]);
});

test('can filter reviews by book', function () {
    $book1 = Book::factory()->create();
    $book2 = Book::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Review::factory()->create(['book_id' => $book1->id, 'user_id' => $user1->id]);
    Review::factory()->create(['book_id' => $book1->id, 'user_id' => $user2->id]);
    Review::factory()->create(['book_id' => $book2->id, 'user_id' => $user1->id]);

    $response = $this->getJson("/api/reviews?book_id={$book1->id}");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

test('authenticated user can create a review', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'comment' => 'This is a great book with lots of interesting content!',
        'is_recommended' => true,
    ]);

    $response->assertCreated()
        ->assertJson([
            'comment' => 'This is a great book with lots of interesting content!',
            'is_recommended' => true,
        ]);

    expect(Review::count())->toBe(1);
});

test('guest cannot create a review', function () {
    $book = Book::factory()->create();

    $response = $this->postJson('/api/reviews', [
        'book_id' => $book->id,
        'comment' => 'This is a great book!',
        'is_recommended' => true,
    ]);

    $response->assertUnauthorized();
});

test('review creation validates is_recommended is boolean', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'comment' => 'This is a great book!',
        'is_recommended' => 'not-a-boolean',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['is_recommended']);
});

test('review creation validates is_recommended is required', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'comment' => 'This is a great book!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['is_recommended']);
});

test('review creation validates comment length', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/reviews', [
        'book_id' => $book->id,
        'comment' => str_repeat('a', 401),  // Too long (max 400 characters)
        'is_recommended' => true,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['comment']);
});

test('review creator can update their review', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'comment' => 'Original content here for testing purposes.',
        'is_recommended' => false,
    ]);

    $response = $this->actingAs($user)->putJson("/api/reviews/{$review->id}", [
        'comment' => 'Updated content with more details and information.',
        'is_recommended' => true,
    ]);

    $response->assertOk()
        ->assertJson([
            'comment' => 'Updated content with more details and information.',
            'is_recommended' => true,
        ]);
});

test('user cannot update another users review', function () {
    $book = Book::factory()->create();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $owner->id,
        'comment' => 'Original content here.',
        'is_recommended' => false,
    ]);

    $response = $this->actingAs($otherUser)->putJson("/api/reviews/{$review->id}", [
        'comment' => 'Trying to update.',
        'is_recommended' => true,
    ]);

    $response->assertForbidden();
});

test('review creator can delete their review', function () {
    $book = Book::factory()->create();
    $user = User::factory()->create();

    $review = Review::create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'comment' => 'Content to be deleted.',
        'is_recommended' => true,
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
        'comment' => 'Original content.',
        'is_recommended' => true,
    ]);

    $response = $this->actingAs($otherUser)->deleteJson("/api/reviews/{$review->id}");

    $response->assertForbidden();
    expect(Review::find($review->id))->not->toBeNull();
});
