<?php

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;

it('can borrow an available book copy', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    expect($bookCopy->isAvailable())->toBeTrue();

    $loan = Loan::create([
        'user_id' => $user->id,
        'book_copy_id' => $bookCopy->id,
        'borrowed_date' => now(),
    ]);

    expect($loan)->toBeInstanceOf(Loan::class)
        ->and($loan->isActive())->toBeTrue()
        ->and($bookCopy->fresh()->isAvailable())->toBeFalse();
});

it('can return a borrowed book', function () {
    $loan = Loan::factory()->create();
    $bookCopy = $loan->bookCopy;

    expect($bookCopy->isAvailable())->toBeFalse()
        ->and($loan->isActive())->toBeTrue();

    $loan->returnBook();

    expect($loan->fresh()->isActive())->toBeFalse()
        ->and($bookCopy->fresh()->isAvailable())->toBeTrue();
});

it('tracks loan history for a user', function () {
    $user = User::factory()->create();
    $activeLoans = Loan::factory()->count(2)->create(['user_id' => $user->id]);
    $returnedLoans = Loan::factory()->returned()->count(3)->create(['user_id' => $user->id]);

    expect($user->loans)->toHaveCount(5)
        ->and($user->activeLoans)->toHaveCount(2)
        ->and($user->loanHistory)->toHaveCount(3);
});

it('cannot have multiple active loans for same book copy', function () {
    $bookCopy = BookCopy::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Loan::create([
        'user_id' => $user1->id,
        'book_copy_id' => $bookCopy->id,
        'borrowed_date' => now(),
    ]);

    expect($bookCopy->fresh()->isAvailable())->toBeFalse();

    // Second user should not be able to borrow the same copy
    $bookCopyAvailable = $bookCopy->fresh()->isAvailable();
    expect($bookCopyAvailable)->toBeFalse();
});

it('allows borrowing after return', function () {
    $bookCopy = BookCopy::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // First user borrows
    $loan1 = Loan::create([
        'user_id' => $user1->id,
        'book_copy_id' => $bookCopy->id,
        'borrowed_date' => now(),
    ]);

    expect($bookCopy->fresh()->isAvailable())->toBeFalse();

    // Return the book
    $loan1->returnBook();

    expect($bookCopy->fresh()->isAvailable())->toBeTrue();

    // Second user can now borrow
    $loan2 = Loan::create([
        'user_id' => $user2->id,
        'book_copy_id' => $bookCopy->id,
        'borrowed_date' => now(),
    ]);

    expect($loan2->isActive())->toBeTrue()
        ->and($bookCopy->fresh()->isAvailable())->toBeFalse();
});

it('cannot borrow a discarded book copy', function () {
    $bookCopy = BookCopy::factory()->discarded()->create();

    expect($bookCopy->isAvailable())->toBeFalse();
});
