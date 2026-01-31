<?php

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;

it('requires authentication to access loans', function () {
    $response = $this->getJson('/loans');

    $response->assertUnauthorized();
});

it('can list user loans', function () {
    $user = User::factory()->create();
    Loan::factory()->count(3)->create(['user_id' => $user->id]);
    Loan::factory()->count(2)->create(); // Other user's loans

    $response = $this->actingAs($user)->getJson('/loans');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('can create a loan for available book', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_copy_id' => $bookCopy->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('book_copy_id', $bookCopy->id)
        ->assertJsonPath('returned_date', null);

    expect(Loan::where('user_id', $user->id)->where('book_copy_id', $bookCopy->id)->exists())->toBeTrue();
});

it('cannot create loan for unavailable book', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();
    
    // Make book unavailable by creating active loan
    Loan::factory()->create(['book_copy_id' => $bookCopy->id]);

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_copy_id' => $bookCopy->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'This book copy is not available for borrowing.');
});

it('can show a specific loan', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson("/loans/{$loan->id}");

    $response->assertSuccessful()
        ->assertJsonPath('id', $loan->id);
});

it('cannot show another users loan', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/loans/{$loan->id}");

    $response->assertForbidden();
});

it('can return a book', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $user->id, 'returned_date' => null]);

    expect($loan->returned_date)->toBeNull();

    $response = $this->actingAs($user)->putJson("/loans/{$loan->id}");

    $response->assertSuccessful();

    expect($loan->fresh()->returned_date)->not->toBeNull();
});

it('cannot return another users loan', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/loans/{$loan->id}");

    $response->assertForbidden();
});

it('cannot return already returned loan', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->returned()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/loans/{$loan->id}");

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'This loan has already been returned.');
});

it('cannot delete loans', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/loans/{$loan->id}");

    $response->assertForbidden();
    expect(Loan::find($loan->id))->not->toBeNull();
});

it('validates book_copy_id when creating loan', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/loans', [
        'book_copy_id' => 999999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('book_copy_id');
});
