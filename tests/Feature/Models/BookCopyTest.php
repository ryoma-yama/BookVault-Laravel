<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;

it('can create a book copy', function () {
    $book = Book::factory()->create();

    $bookCopy = BookCopy::factory()->create([
        'book_id' => $book->id,
    ]);

    expect($bookCopy)->toBeInstanceOf(BookCopy::class)
        ->and($bookCopy->book_id)->toBe($book->id)
        ->and($bookCopy->acquired_date)->not->toBeNull()
        ->and($bookCopy->discarded_date)->toBeNull();
});

it('belongs to a book', function () {
    $bookCopy = BookCopy::factory()->create();

    expect($bookCopy->book)->toBeInstanceOf(Book::class);
});

it('has many loans', function () {
    $bookCopy = BookCopy::factory()->create();
    Loan::factory()->count(3)->create(['book_copy_id' => $bookCopy->id]);

    expect($bookCopy->loans)->toHaveCount(3)
        ->and($bookCopy->loans->first())->toBeInstanceOf(Loan::class);
});

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
