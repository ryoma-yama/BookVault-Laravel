<?php

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Business Logic Tests - BookCopy State & Relationships

test('book copy can be discarded', function () {
    $copy = BookCopy::factory()->create([
        'acquired_date' => '2024-01-15',
        'discarded_date' => null,
    ]);

    expect($copy->isDiscarded())->toBeFalse();

    $copy->update(['discarded_date' => '2024-06-15']);

    expect($copy->isDiscarded())->toBeTrue()
        ->and($copy->discarded_date->format('Y-m-d'))->toBe('2024-06-15');
});

test('book available copies count excludes discarded copies', function () {
    $book = Book::factory()->create();

    // Create 3 available copies
    BookCopy::factory()->count(3)->create(['book_id' => $book->id]);

    // Create 2 discarded copies
    BookCopy::factory()->count(2)->discarded()->create(['book_id' => $book->id]);

    $book->refresh();

    expect($book->available_copies_count)->toBe(3)
        ->and($book->copies)->toHaveCount(5);
});

test('deleting a book cascades to copies', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->count(3)->create(['book_id' => $book->id]);

    expect(BookCopy::count())->toBe(3);

    $book->delete();

    expect(BookCopy::count())->toBe(0);
});
