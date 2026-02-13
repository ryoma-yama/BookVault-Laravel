<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;

use function Pest\Laravel\get;

/**
 * Test to verify that book details include current loan information.
 * This test should fail initially and pass after implementation.
 */
it('includes current loan information in book details', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $book = Book::factory()->create(['title' => 'Test Book']);
    $copy = BookCopy::factory()->for($book)->create();

    // Create an outstanding loan
    $loan = Loan::factory()->for($copy, 'bookCopy')->for($user)->create([
        'borrowed_date' => now()->subDays(5),
        'returned_date' => null,
    ]);

    $response = get(route('books.show', $book));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->has('book.current_loans', 1)
        ->where('book.current_loans.0.user.name', 'John Doe')
        ->has('book.current_loans.0.borrowed_date')
    );
});

it('shows empty current loans when no book is borrowed', function () {
    $book = Book::factory()->create();
    BookCopy::factory()->for($book)->create();

    $response = get(route('books.show', $book));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->has('book.current_loans', 0)
    );
});

it('includes multiple current loans when multiple copies are borrowed', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);
    $book = Book::factory()->create();
    $copy1 = BookCopy::factory()->for($book)->create();
    $copy2 = BookCopy::factory()->for($book)->create();

    // Create two outstanding loans
    Loan::factory()->for($copy1, 'bookCopy')->for($user1)->create([
        'borrowed_date' => now()->subDays(3),
        'returned_date' => null,
    ]);

    Loan::factory()->for($copy2, 'bookCopy')->for($user2)->create([
        'borrowed_date' => now()->subDays(2),
        'returned_date' => null,
    ]);

    $response = get(route('books.show', $book));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->has('book.current_loans', 2)
    );
});

it('does not include returned loans in current loans', function () {
    $user = User::factory()->create(['name' => 'Charlie']);
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->for($book)->create();

    // Create a returned loan
    Loan::factory()->for($copy, 'bookCopy')->for($user)->create([
        'borrowed_date' => now()->subDays(10),
        'returned_date' => now()->subDays(3),
    ]);

    $response = get(route('books.show', $book));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('books/show')
        ->has('book.current_loans', 0)
    );
});
