<?php

use App\Models\Book;
use App\Models\Review;
use App\Models\User;

// User Reviews Index Tests
it('allows authenticated users to access their reviews index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/reviews');

    $response->assertOk();
});

it('prevents unauthenticated users from accessing reviews index page', function () {
    $response = $this->get('/reviews');

    $response->assertRedirect('/login');
});

it('displays only the authenticated users reviews on reviews index page', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    
    $userReview1 = Review::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subMinute(),
    ]);
    $userReview2 = Review::factory()->create([
        'user_id' => $user->id,
        'created_at' => now(),
    ]);
    $otherReview = Review::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->get('/reviews');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('reviews/index')
        ->has('reviews', 2)
        ->where('reviews.0.id', $userReview2->id) // Latest first
        ->where('reviews.1.id', $userReview1->id)
    );
});

it('displays reviews in descending order by creation date on user reviews page', function () {
    $user = User::factory()->create();
    
    $oldReview = Review::factory()->create([
        'user_id' => $user->id,
        'created_at' => now()->subDays(2),
    ]);
    
    $newReview = Review::factory()->create([
        'user_id' => $user->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/reviews');

    $response->assertInertia(fn ($page) => $page
        ->where('reviews.0.id', $newReview->id)
        ->where('reviews.1.id', $oldReview->id)
    );
});

// Admin Reviews Index Tests
it('allows admin users to access admin reviews index page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/reviews');

    $response->assertOk();
});

it('prevents non-admin users from accessing admin reviews index page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/admin/reviews');

    $response->assertForbidden();
});

it('prevents unauthenticated users from accessing admin reviews index page', function () {
    $response = $this->get('/admin/reviews');

    $response->assertRedirect('/login');
});

it('displays all reviews on admin reviews index page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $review1 = Review::factory()->create(['user_id' => $user1->id]);
    $review2 = Review::factory()->create(['user_id' => $user2->id]);
    $review3 = Review::factory()->create(['user_id' => $admin->id]);

    $response = $this->actingAs($admin)->get('/admin/reviews');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/reviews/index')
        ->has('reviews', 3)
    );
});

it('displays reviews in descending order by creation date on admin reviews page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $oldReview = Review::factory()->create(['created_at' => now()->subDays(2)]);
    $newReview = Review::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($admin)->get('/admin/reviews');

    $response->assertInertia(fn ($page) => $page
        ->where('reviews.0.id', $newReview->id)
        ->where('reviews.1.id', $oldReview->id)
    );
});

// Admin can edit/delete any review
it('allows admin to edit any review', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get("/reviews/{$review->id}/edit");

    $response->assertOk();
});

it('allows admin to update any review', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $review = Review::factory()->create([
        'user_id' => $user->id,
        'comment' => 'Original comment',
    ]);

    $response = $this->actingAs($admin)->put("/reviews/{$review->id}", [
        'comment' => 'Admin updated comment',
        'is_recommended' => false,
    ]);

    $response->assertRedirect("/books/{$review->book_id}");
    
    $review->refresh();
    expect($review->comment)->toBe('Admin updated comment');
});

it('allows admin to delete any review', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $review = Review::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->delete("/reviews/{$review->id}");

    $response->assertRedirect("/books/{$review->book_id}");
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});
