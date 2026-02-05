<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;

it('allows authenticated user to borrow a book by book_id', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_id' => $book->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('book_copy_id', $bookCopy->id)
        ->assertJsonPath('returned_date', null);

    expect(Loan::where('user_id', $user->id)->where('book_copy_id', $bookCopy->id)->exists())->toBeTrue();
});

it('automatically selects an available copy when borrowing by book_id', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $borrowedCopy = BookCopy::factory()->create(['book_id' => $book->id]);
    $availableCopy = BookCopy::factory()->create(['book_id' => $book->id]);

    // Make first copy unavailable by creating active loan
    Loan::factory()->create(['book_copy_id' => $borrowedCopy->id]);

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_id' => $book->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('book_copy_id', $availableCopy->id);
});

it('returns error when all copies are borrowed', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $bookCopy = BookCopy::factory()->create(['book_id' => $book->id]);

    // Make the only copy unavailable
    Loan::factory()->create(['book_copy_id' => $bookCopy->id]);

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_id' => $book->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'This book is not available for borrowing.');
});

it('requires authentication to borrow a book by book_id', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->create(['book_id' => $book->id]);

    $response = $this->postJson('/loans', [
        'book_id' => $book->id,
    ]);

    $response->assertUnauthorized();
});

it('validates book_id when borrowing', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_id' => 999999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('book_id');
});

it('ignores discarded copies when selecting available copy', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    $discardedCopy = BookCopy::factory()->create([
        'book_id' => $book->id,
        'discarded_date' => now(),
    ]);
    $activeCopy = BookCopy::factory()->create(['book_id' => $book->id]);

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_id' => $book->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('book_copy_id', $activeCopy->id);
});

it('still works with book_copy_id for backward compatibility', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_copy_id' => $bookCopy->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('book_copy_id', $bookCopy->id);
});
