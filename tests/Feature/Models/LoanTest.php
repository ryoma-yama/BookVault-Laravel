<?php

use App\Models\Loan;

// Business Logic Tests - Loan State Management

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
