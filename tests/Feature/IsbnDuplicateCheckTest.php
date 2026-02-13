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
