<?php

use App\Models\Book;
use App\Models\Tag;

test('tag can be created', function () {
    $tag = Tag::create(['name' => 'Fiction']);

    expect($tag)->toBeInstanceOf(Tag::class)
        ->and($tag->name)->toBe('Fiction');
});

test('tag name must be unique', function () {
    Tag::create(['name' => 'Fiction']);
    
    expect(fn () => Tag::create(['name' => 'Fiction']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

test('tag can have multiple books', function () {
    $tag = Tag::factory()->create();
    $book1 = Book::factory()->create();
    $book2 = Book::factory()->create();

    $tag->books()->attach([$book1->id, $book2->id]);

    expect($tag->books)->toHaveCount(2);
});

test('deleting a tag detaches from books', function () {
    $tag = Tag::factory()->create();
    $book = Book::factory()->create();

    $book->tags()->attach($tag->id);
    expect($book->tags)->toHaveCount(1);

    $tag->delete();

    expect($book->fresh()->tags)->toHaveCount(0);
});
