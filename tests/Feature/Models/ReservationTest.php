<?php

use App\Models\BookCopy;
use App\Models\Reservation;
use App\Models\User;

it('can create a reservation', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    $reservation = Reservation::factory()->create([
        'user_id' => $user->id,
        'book_copy_id' => $bookCopy->id,
    ]);

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->user_id)->toBe($user->id)
        ->and($reservation->book_copy_id)->toBe($bookCopy->id)
        ->and($reservation->reserved_at)->not->toBeNull()
        ->and($reservation->fulfilled)->toBeFalse();
});

it('belongs to a user', function () {
    $reservation = Reservation::factory()->create();

    expect($reservation->user)->toBeInstanceOf(User::class);
});

it('belongs to a book copy', function () {
    $reservation = Reservation::factory()->create();

    expect($reservation->bookCopy)->toBeInstanceOf(BookCopy::class);
});

it('can be fulfilled', function () {
    $reservation = Reservation::factory()->create(['fulfilled' => false]);

    expect($reservation->fulfilled)->toBeFalse();

    $reservation->fulfill();

    expect($reservation->fulfilled)->toBeTrue();
});

it('can be cancelled', function () {
    $reservation = Reservation::factory()->create();

    expect(Reservation::find($reservation->id))->not->toBeNull();

    $reservation->cancel();

    expect(Reservation::find($reservation->id))->toBeNull();
});

it('casts reserved_at as datetime', function () {
    $reservation = Reservation::factory()->create();

    expect($reservation->reserved_at)->toBeInstanceOf(\Carbon\CarbonInterface::class);
});

it('casts fulfilled as boolean', function () {
    $reservation = Reservation::factory()->create(['fulfilled' => false]);

    expect($reservation->fulfilled)->toBeBool()
        ->and($reservation->fulfilled)->toBeFalse();
});
