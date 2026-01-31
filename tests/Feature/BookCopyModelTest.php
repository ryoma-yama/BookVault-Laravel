<?php

use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('book can be created with valid attributes', function () {
    $book = Book::factory()->create([
        'isbn_13' => '9781234567890',
        'title' => 'Test Book',
        'publisher' => 'Test Publisher',
    ]);

    expect($book->isbn_13)->toBe('9781234567890')
        ->and($book->title)->toBe('Test Book')
        ->and($book->publisher)->toBe('Test Publisher')
        ->and($book->exists)->toBeTrue();
});

test('book copy can be created with valid attributes', function () {
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create([
        'book_id' => $book->id,
        'acquired_date' => '2024-01-15',
    ]);

    expect($copy->book_id)->toBe($book->id)
        ->and($copy->acquired_date->format('Y-m-d'))->toBe('2024-01-15')
        ->and($copy->discarded_date)->toBeNull()
        ->and($copy->exists)->toBeTrue();
});

test('book copy belongs to a book', function () {
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create(['book_id' => $book->id]);

    expect($copy->book)->toBeInstanceOf(Book::class)
        ->and($copy->book->id)->toBe($book->id);
});

test('book has many copies', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->count(3)->create(['book_id' => $book->id]);

    expect($book->copies)->toHaveCount(3)
        ->and($book->copies->first())->toBeInstanceOf(BookCopy::class);
});

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

test('book copy acquired date is cast to date', function () {
    $copy = BookCopy::factory()->create(['acquired_date' => '2024-01-15']);

    expect($copy->acquired_date)->toBeInstanceOf(\DateTimeInterface::class);
});

test('book copy discarded date is cast to date when set', function () {
    $copy = BookCopy::factory()->create(['discarded_date' => '2024-06-15']);

    expect($copy->discarded_date)->toBeInstanceOf(\DateTimeInterface::class);
});
