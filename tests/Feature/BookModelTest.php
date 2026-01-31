<?php

use App\Models\Book;

describe('Book Model', function () {
    it('can create a book using factory', function () {
        $book = Book::factory()->create();
        
        expect($book)->toBeInstanceOf(Book::class)
            ->and($book->google_id)->toBeString()
            ->and($book->title)->toBeString();
    });

    it('has required fillable attributes', function () {
        $book = Book::factory()->create([
            'title' => 'Test Book',
            'publisher' => 'Test Publisher',
        ]);
        
        expect($book->title)->toBe('Test Book')
            ->and($book->publisher)->toBe('Test Publisher');
    });

    it('can retrieve books from database', function () {
        Book::factory()->count(3)->create();
        
        expect(Book::count())->toBe(3);
    });
});
