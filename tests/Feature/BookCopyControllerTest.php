<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->withoutVite();
});

test('authenticated user can create a book copy', function () {
    $book = Book::factory()->create();

    $response = $this->actingAs($this->user)
        ->post(route('admin.copies.store', $book), [
            'acquired_date' => '2024-01-15',
        ]);

    $response->assertRedirect(route('admin.books.edit', $book));

    expect(BookCopy::count())->toBe(1)
        ->and(BookCopy::first()->book_id)->toBe($book->id)
        ->and(BookCopy::first()->acquired_date->format('Y-m-d'))->toBe('2024-01-15');
});

test('authenticated user can update a book copy', function () {
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create([
        'book_id' => $book->id,
        'acquired_date' => '2024-01-15',
    ]);

    $response = $this->actingAs($this->user)
        ->put(route('admin.copies.update', [$book, $copy]), [
            'acquired_date' => '2024-02-20',
            'discarded_date' => '2024-06-15',
        ]);

    $response->assertRedirect(route('admin.books.edit', $book));

    $copy->refresh();
    expect($copy->acquired_date->format('Y-m-d'))->toBe('2024-02-20')
        ->and($copy->discarded_date->format('Y-m-d'))->toBe('2024-06-15');
});

test('authenticated user can delete a book copy', function () {
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create(['book_id' => $book->id]);

    expect(BookCopy::count())->toBe(1);

    $response = $this->actingAs($this->user)
        ->delete(route('admin.copies.destroy', [$book, $copy]));

    $response->assertRedirect(route('admin.books.edit', $book));

    expect(BookCopy::count())->toBe(0);
});

test('creating a copy requires acquired_date', function () {
    $book = Book::factory()->create();

    $response = $this->actingAs($this->user)
        ->post(route('admin.copies.store', $book), [
            'acquired_date' => '',
        ]);

    $response->assertSessionHasErrors(['acquired_date']);
    expect(BookCopy::count())->toBe(0);
});

test('updating a copy requires acquired_date', function () {
    $book = Book::factory()->create();
    $copy = BookCopy::factory()->create(['book_id' => $book->id]);

    $response = $this->actingAs($this->user)
        ->put(route('admin.copies.update', [$book, $copy]), [
            'acquired_date' => '',
        ]);

    $response->assertSessionHasErrors(['acquired_date']);
});

test('guest cannot create book copies', function () {
    $this->withoutVite();
    $book = Book::factory()->create();

    $response = $this->post(route('admin.copies.store', $book), [
        'acquired_date' => '2024-01-15',
    ]);

    $response->assertRedirect(route('login'));
    expect(BookCopy::count())->toBe(0);
});
