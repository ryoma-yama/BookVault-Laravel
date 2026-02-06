<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;

it('returns json response for api requests', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/loans', [
            'book_copy_id' => $bookCopy->id,
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'id',
            'user_id',
            'book_copy_id',
        ])
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('book_copy_id', $bookCopy->id);
});

it('returns redirect for web requests', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    $response = $this->actingAs($user)
        ->post('/loans', [
            'book_copy_id' => $bookCopy->id,
        ]);

    $response->assertRedirect();
});

it('handles validation errors properly for api requests', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/loans', [
            'book_id' => 999999,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('book_id');
});

it('handles validation errors properly for web requests', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/loans', [
            'book_id' => 999999,
        ]);

    $response->assertSessionHasErrors('book_id');
});

it('handles availability errors for api requests', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

    // Make copy unavailable
    \App\Models\Loan::factory()->create(['book_copy_id' => $bookCopy->id]);

    $response = $this->actingAs($user)
        ->postJson('/loans', [
            'book_id' => $book->id,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('book_id');
});

it('handles availability errors for web requests', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

    // Make copy unavailable
    \App\Models\Loan::factory()->create(['book_copy_id' => $bookCopy->id]);

    $response = $this->actingAs($user)
        ->post('/loans', [
            'book_id' => $book->id,
        ]);

    $response->assertSessionHasErrors('book_id');
});
