<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

// Unauthenticated users cannot create reviews
it('prevents unauthenticated users from accessing review create page', function () {
    $book = Book::factory()->create();
    
    $response = $this->get("/books/{$book->id}/reviews/create");
    
    $response->assertRedirect('/login');
});

it('prevents unauthenticated users from storing a review', function () {
    $book = Book::factory()->create();
    
    $response = $this->post('/reviews', [
        'book_id' => $book->id,
        'comment' => 'Great book!',
        'is_recommended' => true,
    ]);
    
    $response->assertRedirect('/login');
});

it('prevents unauthenticated users from accessing review edit page', function () {
    $review = Review::factory()->create();
    
    $response = $this->get("/reviews/{$review->id}/edit");
    
    $response->assertRedirect('/login');
});

it('prevents unauthenticated users from updating a review', function () {
    $review = Review::factory()->create();
    
    $response = $this->put("/reviews/{$review->id}", [
        'comment' => 'Updated comment',
        'is_recommended' => false,
    ]);
    
    $response->assertRedirect('/login');
});

it('prevents unauthenticated users from deleting a review', function () {
    $review = Review::factory()->create();
    
    $response = $this->delete("/reviews/{$review->id}");
    
    $response->assertRedirect('/login');
});

// Authenticated users can create one review per book
it('allows authenticated users to access review create page', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->get("/books/{$book->id}/reviews/create");
    
    $response->assertOk();
});

it('allows authenticated users to create a review for a book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'comment' => 'This is a great book! I really enjoyed reading it.',
        'is_recommended' => true,
    ]);
    
    $response->assertRedirect("/books/{$book->id}");
    
    $this->assertDatabaseHas('reviews', [
        'book_id' => $book->id,
        'user_id' => $user->id,
        'comment' => 'This is a great book! I really enjoyed reading it.',
        'is_recommended' => true,
    ]);
});

it('prevents users from creating duplicate reviews for the same book', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    // Create first review
    Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);
    
    // Attempt to create second review
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'comment' => 'Another review for the same book',
        'is_recommended' => true,
    ]);
    
    $response->assertSessionHasErrors();
    
    // Verify only one review exists
    $this->assertEquals(1, Review::where('user_id', $user->id)
        ->where('book_id', $book->id)
        ->count());
});

// Validation tests
it('validates is_recommended is required', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'comment' => 'This is a comment',
    ]);
    
    $response->assertSessionHasErrors('is_recommended');
});

it('validates is_recommended is boolean', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'comment' => 'This is a comment',
        'is_recommended' => 'not-a-boolean',
    ]);
    
    $response->assertSessionHasErrors('is_recommended');
});

it('validates comment is required', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'is_recommended' => true,
    ]);
    
    $response->assertSessionHasErrors('comment');
});

it('validates comment is a string', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'comment' => ['not', 'a', 'string'],
        'is_recommended' => true,
    ]);
    
    $response->assertSessionHasErrors('comment');
});

it('validates comment does not exceed 400 characters', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'comment' => str_repeat('a', 401),
        'is_recommended' => true,
    ]);
    
    $response->assertSessionHasErrors('comment');
});

it('allows comment with exactly 400 characters', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    
    $response = $this->actingAs($user)->post('/reviews', [
        'book_id' => $book->id,
        'comment' => str_repeat('a', 400),
        'is_recommended' => true,
    ]);
    
    $response->assertRedirect("/books/{$book->id}");
});

// Users can only edit their own reviews
it('allows users to access their own review edit page', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");
    
    $response->assertOk();
});

it('allows users to update their own reviews', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create([
        'user_id' => $user->id,
        'comment' => 'Original comment',
        'is_recommended' => true,
    ]);
    
    $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
        'comment' => 'Updated comment',
        'is_recommended' => false,
    ]);
    
    $response->assertRedirect("/books/{$review->book_id}");
    
    $review->refresh();
    expect($review->comment)->toBe('Updated comment');
    expect($review->is_recommended)->toBe(false);
});

it('prevents users from accessing other users review edit page', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $otherUser->id]);
    
    $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");
    
    $response->assertForbidden();
});

it('prevents users from updating other users reviews', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $review = Review::factory()->create([
        'user_id' => $otherUser->id,
        'comment' => 'Original comment',
    ]);
    
    $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
        'comment' => 'Malicious update',
        'is_recommended' => false,
    ]);
    
    $response->assertForbidden();
    
    $review->refresh();
    expect($review->comment)->toBe('Original comment');
});

// Users can only delete their own reviews
it('allows users to delete their own reviews', function () {
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);
    
    $response = $this->actingAs($user)->delete("/reviews/{$review->id}");
    
    $response->assertRedirect("/books/{$review->book_id}");
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

it('prevents users from deleting other users reviews', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $otherUser->id]);
    
    $response = $this->actingAs($user)->delete("/reviews/{$review->id}");
    
    $response->assertForbidden();
    $this->assertDatabaseHas('reviews', ['id' => $review->id]);
});
