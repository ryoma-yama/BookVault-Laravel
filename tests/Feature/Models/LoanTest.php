<?php

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;

it('can create a loan', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'book_copy_id' => $bookCopy->id,
    ]);

    expect($loan)->toBeInstanceOf(Loan::class)
        ->and($loan->user_id)->toBe($user->id)
        ->and($loan->book_copy_id)->toBe($bookCopy->id)
        ->and($loan->borrowed_date)->not->toBeNull()
        ->and($loan->returned_date)->toBeNull();
});

it('belongs to a user', function () {
    $loan = Loan::factory()->create();

    expect($loan->user)->toBeInstanceOf(User::class);
});

it('belongs to a book copy', function () {
    $loan = Loan::factory()->create();

    expect($loan->bookCopy)->toBeInstanceOf(BookCopy::class);
});

it('can check if loan is active', function () {
    $activeLoan = Loan::factory()->create(['returned_date' => null]);
    $returnedLoan = Loan::factory()->returned()->create();

    expect($activeLoan->isActive())->toBeTrue()
        ->and($returnedLoan->isActive())->toBeFalse();
});

it('can return a book', function () {
    $loan = Loan::factory()->create(['returned_date' => null]);

    expect($loan->returned_date)->toBeNull();

    $loan->returnBook();

    expect($loan->returned_date)->not->toBeNull()
        ->and($loan->isActive())->toBeFalse();
});

it('casts dates correctly', function () {
    $loan = Loan::factory()->create();

    expect($loan->borrowed_date)->toBeInstanceOf(\Carbon\CarbonInterface::class)
        ->and($loan->returned_date)->toBeNull();
});
