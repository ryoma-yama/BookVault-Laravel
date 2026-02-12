<?php

use App\Models\BookCopy;
use App\Models\Loan;

// Business Logic Tests - BookCopy Availability & Loan State

it('is available when not loaned and not discarded', function () {
    $bookCopy = BookCopy::factory()->create(['discarded_date' => null]);

    expect($bookCopy->isAvailable())->toBeTrue();
});

it('is not available when currently loaned', function () {
    $bookCopy = BookCopy::factory()->create(['discarded_date' => null]);
    Loan::factory()->create([
        'book_copy_id' => $bookCopy->id,
        'returned_date' => null,
    ]);

    expect($bookCopy->isAvailable())->toBeFalse();
});

it('is available when previous loan was returned', function () {
    $bookCopy = BookCopy::factory()->create(['discarded_date' => null]);
    Loan::factory()->returned()->create([
        'book_copy_id' => $bookCopy->id,
    ]);

    expect($bookCopy->isAvailable())->toBeTrue();
});

it('is not available when discarded', function () {
    $bookCopy = BookCopy::factory()->discarded()->create();

    expect($bookCopy->isAvailable())->toBeFalse();
});

it('can get current loan', function () {
    $bookCopy = BookCopy::factory()->create();
    $activeLoan = Loan::factory()->create([
        'book_copy_id' => $bookCopy->id,
        'returned_date' => null,
    ]);
    Loan::factory()->returned()->create(['book_copy_id' => $bookCopy->id]);

    $currentLoan = $bookCopy->currentLoan();

    expect($currentLoan)->toBeInstanceOf(Loan::class)
        ->and($currentLoan->id)->toBe($activeLoan->id);
});

it('returns null for current loan when no active loan', function () {
    $bookCopy = BookCopy::factory()->create();
    Loan::factory()->returned()->create(['book_copy_id' => $bookCopy->id]);

    expect($bookCopy->currentLoan())->toBeNull();
});
