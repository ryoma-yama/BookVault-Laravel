<?php

use App\Models\Book;
use App\Models\BookCopy;

test('book list only shows books with at least one valid copy', function () {
    // Create a book with valid copies
    $bookWithValidCopy = Book::factory()->create(['title' => 'Book With Valid Copy']);
    BookCopy::factory()->create([
        'book_id' => $bookWithValidCopy->id,
        'discarded_date' => null,
    ]);

    // Create a book with only discarded copies
    $bookWithDiscardedCopy = Book::factory()->create(['title' => 'Book With Discarded Copy']);
    BookCopy::factory()->create([
        'book_id' => $bookWithDiscardedCopy->id,
        'discarded_date' => now(),
    ]);

    // Create a book with no copies
    $bookWithNoCopies = Book::factory()->create(['title' => 'Book With No Copies']);

    // Create a book with both valid and discarded copies
    $bookWithMixedCopies = Book::factory()->create(['title' => 'Book With Mixed Copies']);
    BookCopy::factory()->create([
        'book_id' => $bookWithMixedCopies->id,
        'discarded_date' => null,
    ]);
    BookCopy::factory()->create([
        'book_id' => $bookWithMixedCopies->id,
        'discarded_date' => now(),
    ]);

    // Test the scope
    $booksWithValidCopies = Book::hasValidCopies()->get();

    expect($booksWithValidCopies)->toHaveCount(2)
        ->and($booksWithValidCopies->pluck('id')->toArray())
        ->toContain($bookWithValidCopy->id, $bookWithMixedCopies->id)
        ->not->toContain($bookWithDiscardedCopy->id, $bookWithNoCopies->id);
});

test('book index route only returns books with valid copies', function () {
    // Create a book with valid copy
    $bookWithValidCopy = Book::factory()->create(['title' => 'Valid Book']);
    BookCopy::factory()->create([
        'book_id' => $bookWithValidCopy->id,
        'discarded_date' => null,
    ]);

    // Create a book with only discarded copies
    $bookWithDiscardedCopy = Book::factory()->create(['title' => 'Discarded Book']);
    BookCopy::factory()->create([
        'book_id' => $bookWithDiscardedCopy->id,
        'discarded_date' => now(),
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('books/index')
        ->has('books.data', 1)
        ->where('books.data.0.id', $bookWithValidCopy->id)
    );
});

test('book list is sorted by id descending by default', function () {
    // Create books with valid copies
    $book1 = Book::factory()->create(['title' => 'Book 1']);
    BookCopy::factory()->create(['book_id' => $book1->id, 'discarded_date' => null]);

    $book2 = Book::factory()->create(['title' => 'Book 2']);
    BookCopy::factory()->create(['book_id' => $book2->id, 'discarded_date' => null]);

    $book3 = Book::factory()->create(['title' => 'Book 3']);
    BookCopy::factory()->create(['book_id' => $book3->id, 'discarded_date' => null]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('books/index')
        ->has('books.data', 3)
        ->where('books.data.0.id', $book3->id)
        ->where('books.data.1.id', $book2->id)
        ->where('books.data.2.id', $book1->id)
    );
});

test('book searchable array includes has_valid_copies flag', function () {
    // Skip this test for database driver as it doesn't support computed fields
    if (config('scout.driver') === 'database') {
        $this->markTestSkipped('Database driver does not support computed fields in search index');
    }
    
    // Create a book with valid copy
    $bookWithValidCopy = Book::factory()->create();
    BookCopy::factory()->create([
        'book_id' => $bookWithValidCopy->id,
        'discarded_date' => null,
    ]);

    // Create a book with no valid copies
    $bookWithoutValidCopy = Book::factory()->create();
    BookCopy::factory()->create([
        'book_id' => $bookWithoutValidCopy->id,
        'discarded_date' => now(),
    ]);

    $searchableWithValid = $bookWithValidCopy->toSearchableArray();
    $searchableWithoutValid = $bookWithoutValidCopy->toSearchableArray();

    expect($searchableWithValid)->toHaveKey('has_valid_copies')
        ->and($searchableWithValid['has_valid_copies'])->toBeTrue();

    expect($searchableWithoutValid)->toHaveKey('has_valid_copies')
        ->and($searchableWithoutValid['has_valid_copies'])->toBeFalse();
});

test('updating book copy discarded_date touches parent book', function () {
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create([
        'book_id' => $book->id,
        'discarded_date' => null,
    ]);

    $originalUpdatedAt = $book->updated_at;

    // Wait a bit to ensure timestamp changes
    sleep(1);

    // Update the copy's discarded_date
    $copy->update(['discarded_date' => now()]);

    // Reload the book
    $book->refresh();

    expect($book->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
});

test('creating book copy touches parent book', function () {
    $book = Book::factory()->create();
    $originalUpdatedAt = $book->updated_at;

    // Wait a bit to ensure timestamp changes
    sleep(1);

    // Create a new copy
    BookCopy::factory()->create([
        'book_id' => $book->id,
        'discarded_date' => null,
    ]);

    // Reload the book
    $book->refresh();

    expect($book->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
});

test('deleting book copy touches parent book', function () {
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create([
        'book_id' => $book->id,
        'discarded_date' => null,
    ]);

    $originalUpdatedAt = $book->updated_at;

    // Wait a bit to ensure timestamp changes
    sleep(1);

    // Delete the copy
    $copy->delete();

    // Reload the book
    $book->refresh();

    expect($book->updated_at->isAfter($originalUpdatedAt))->toBeTrue();
});
