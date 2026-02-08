<?php

use App\Models\Book;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('google books search returns error for duplicate isbn', function () {
    Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Existing Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response = actingAs($this->admin)->postJson('/admin/api/google-books/search', [
        'isbn' => '9784123456789',
    ]);

    $response->assertStatus(422);
    expect($response->json('error'))->toContain('already registered');
});

test('google books search continues for new isbn', function () {
    // This will fail because we're not actually calling Google Books API
    // but it tests that the duplicate check passes
    $response = actingAs($this->admin)->postJson('/admin/api/google-books/search', [
        'isbn' => '9781234567890',
    ]);

    // Should get 404 (not found) or 500 (API error) but NOT 422 (duplicate)
    expect($response->status())->not->toBe(422);
});
