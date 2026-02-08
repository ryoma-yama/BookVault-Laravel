<?php

use App\Models\Book;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('isbn check returns exists true for duplicate isbn', function () {
    Book::create([
        'isbn_13' => '9784123456789',
        'title' => 'Existing Book',
        'publisher' => 'Test Publisher',
        'published_date' => '2024-01-01',
        'description' => 'Test Description',
    ]);

    $response = actingAs($this->admin)->postJson('/admin/api/google-books/check-isbn', [
        'isbn' => '9784123456789',
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'exists' => true,
    ]);
    expect($response->json('error'))->toContain('already registered');
});

test('isbn check returns exists false for new isbn', function () {
    $response = actingAs($this->admin)->postJson('/admin/api/google-books/check-isbn', [
        'isbn' => '9781234567890',
    ]);

    $response->assertOk();
    $response->assertJson([
        'exists' => false,
    ]);
});

test('isbn check validates isbn format', function () {
    $response = actingAs($this->admin)->postJson('/admin/api/google-books/check-isbn', [
        'isbn' => '123', // Invalid - too short
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['isbn']);
});
