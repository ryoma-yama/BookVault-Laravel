<?php

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;

it('can create a reservation for a loaned book', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();
    
    // Book is currently loaned
    Loan::factory()->create(['book_copy_id' => $bookCopy->id]);

    expect($bookCopy->fresh()->isAvailable())->toBeFalse();

    $reservation = Reservation::create([
        'user_id' => $user->id,
        'book_copy_id' => $bookCopy->id,
        'reserved_at' => now(),
        'fulfilled' => false,
    ]);

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->fulfilled)->toBeFalse();
});

it('can cancel a reservation', function () {
    $reservation = Reservation::factory()->create();
    $reservationId = $reservation->id;

    expect(Reservation::find($reservationId))->not->toBeNull();

    $reservation->cancel();

    expect(Reservation::find($reservationId))->toBeNull();
});

it('can fulfill a reservation', function () {
    $reservation = Reservation::factory()->create(['fulfilled' => false]);

    expect($reservation->fulfilled)->toBeFalse();

    $reservation->fulfill();

    expect($reservation->fresh()->fulfilled)->toBeTrue();
});

it('tracks reservations for a user', function () {
    $user = User::factory()->create();
    Reservation::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->reservations)->toHaveCount(3);
});

it('allows multiple reservations for same book copy', function () {
    $bookCopy = BookCopy::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $reservation1 = Reservation::create([
        'user_id' => $user1->id,
        'book_copy_id' => $bookCopy->id,
        'reserved_at' => now(),
        'fulfilled' => false,
    ]);

    $reservation2 = Reservation::create([
        'user_id' => $user2->id,
        'book_copy_id' => $bookCopy->id,
        'reserved_at' => now()->addMinute(),
        'fulfilled' => false,
    ]);

    expect($bookCopy->reservations)->toHaveCount(2);
});

it('shows active reservations for a book copy', function () {
    $bookCopy = BookCopy::factory()->create();
    
    Reservation::factory()->count(2)->create([
        'book_copy_id' => $bookCopy->id,
        'fulfilled' => false,
    ]);
    
    Reservation::factory()->fulfilled()->create([
        'book_copy_id' => $bookCopy->id,
    ]);

    $activeReservations = $bookCopy->reservations()->where('fulfilled', false)->get();

    expect($activeReservations)->toHaveCount(2);
});

it('reservation can be fulfilled when book is returned', function () {
    $bookCopy = BookCopy::factory()->create();
    $user = User::factory()->create();
    
    // Book is loaned
    $loan = Loan::factory()->create(['book_copy_id' => $bookCopy->id]);
    
    // User makes reservation
    $reservation = Reservation::create([
        'user_id' => $user->id,
        'book_copy_id' => $bookCopy->id,
        'reserved_at' => now(),
        'fulfilled' => false,
    ]);

    expect($reservation->fulfilled)->toBeFalse();

    // Book is returned
    $loan->returnBook();
    
    // Reservation can now be fulfilled
    $reservation->fulfill();

    expect($reservation->fresh()->fulfilled)->toBeTrue();
});
