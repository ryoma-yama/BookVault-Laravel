<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;

test('book show page includes inventory status', function () {
    $book = Book::factory()->create();

    // Create 3 copies
    $copy1 = BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);
    $copy2 = BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);
    $copy3 = BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => now()]); // discarded

    // Create a user and loan for copy1 (borrowed and not returned)
    $user = User::factory()->create();
    Loan::factory()->create([
        'book_copy_id' => $copy1->id,
        'user_id' => $user->id,
        'borrowed_date' => now(),
        'returned_date' => null,
    ]);

    $response = $this->get("/books/{$book->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->has('book')
        ->where('book.id', $book->id)
        ->has('book.inventory_status')
        ->where('book.inventory_status.total_copies', 2) // Only non-discarded
        ->where('book.inventory_status.borrowed_count', 1)
        ->where('book.inventory_status.available_count', 1)
    );
});

test('book show page calculates available copies correctly', function () {
    $book = Book::factory()->create();

    // Create 4 valid copies
    $copy1 = BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);
    $copy2 = BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);
    $copy3 = BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);
    $copy4 = BookCopy::factory()->create(['book_id' => $book->id, 'discarded_date' => null]);

    $user = User::factory()->create();

    // Borrow copy1 (not returned)
    Loan::factory()->create([
        'book_copy_id' => $copy1->id,
        'user_id' => $user->id,
        'borrowed_date' => now(),
        'returned_date' => null,
    ]);

    // Borrow copy2 (not returned)
    Loan::factory()->create([
        'book_copy_id' => $copy2->id,
        'user_id' => $user->id,
        'borrowed_date' => now(),
        'returned_date' => null,
    ]);

    // Borrow copy3 but already returned
    Loan::factory()->create([
        'book_copy_id' => $copy3->id,
        'user_id' => $user->id,
        'borrowed_date' => now()->subDays(10),
        'returned_date' => now()->subDays(3),
    ]);

    // copy4 never borrowed

    $response = $this->get("/books/{$book->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->where('book.inventory_status.total_copies', 4)
        ->where('book.inventory_status.borrowed_count', 2)
        ->where('book.inventory_status.available_count', 2)
    );
});

test('book with no copies shows zero inventory', function () {
    $book = Book::factory()->create();

    $response = $this->get("/books/{$book->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->where('book.inventory_status.total_copies', 0)
        ->where('book.inventory_status.borrowed_count', 0)
        ->where('book.inventory_status.available_count', 0)
    );
});
