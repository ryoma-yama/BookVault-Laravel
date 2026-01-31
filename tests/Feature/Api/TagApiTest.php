<?php

use App\Models\Book;
use App\Models\Tag;

test('can list all tags', function () {
    Tag::factory()->count(3)->create();

    $response = $this->getJson('/api/tags');

    $response->assertOk()
        ->assertJsonStructure([
            '*' => ['id', 'name', 'books_count'],
        ]);
});

test('can show a single tag with its books', function () {
    $tag = Tag::factory()->create(['name' => 'Fiction']);
    $book1 = Book::factory()->create();
    $book2 = Book::factory()->create();

    $tag->books()->attach([$book1->id, $book2->id]);

    $response = $this->getJson("/api/tags/{$tag->id}");

    $response->assertOk()
        ->assertJson([
            'id' => $tag->id,
            'name' => 'Fiction',
        ])
        ->assertJsonStructure([
            'books' => [
                '*' => ['id', 'title', 'publisher'],
            ],
        ]);

    expect($response->json('books'))->toHaveCount(2);
});
