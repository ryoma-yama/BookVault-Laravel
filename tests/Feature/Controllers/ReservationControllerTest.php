<?php

use App\Models\BookCopy;
use App\Models\Reservation;
use App\Models\User;

it('requires authentication to access reservations', function () {
    $response = $this->getJson('/reservations');

    $response->assertUnauthorized();
});

it('can list user reservations', function () {
    $user = User::factory()->create();
    Reservation::factory()->count(3)->create(['user_id' => $user->id]);
    Reservation::factory()->count(2)->create(); // Other user's reservations

    $response = $this->actingAs($user)->getJson('/reservations');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('can create a reservation', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();

    $response = $this->actingAs($user)->postJson('/reservations', [
        'book_copy_id' => $bookCopy->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('book_copy_id', $bookCopy->id)
        ->assertJsonPath('fulfilled', false);

    expect(Reservation::where('user_id', $user->id)->where('book_copy_id', $bookCopy->id)->exists())->toBeTrue();
});

it('cannot create duplicate active reservation', function () {
    $user = User::factory()->create();
    $bookCopy = BookCopy::factory()->create();
    
    Reservation::factory()->create([
        'user_id' => $user->id,
        'book_copy_id' => $bookCopy->id,
        'fulfilled' => false,
    ]);

    $response = $this->actingAs($user)->postJson('/reservations', [
        'book_copy_id' => $bookCopy->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'You already have an active reservation for this book copy.');
});

it('can show a specific reservation', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson("/reservations/{$reservation->id}");

    $response->assertSuccessful()
        ->assertJsonPath('id', $reservation->id);
});

it('cannot show another users reservation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/reservations/{$reservation->id}");

    $response->assertForbidden();
});

it('can fulfill a reservation', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $user->id, 'fulfilled' => false]);

    expect($reservation->fulfilled)->toBeFalse();

    $response = $this->actingAs($user)->putJson("/reservations/{$reservation->id}");

    $response->assertSuccessful();

    expect($reservation->fresh()->fulfilled)->toBeTrue();
});

it('cannot fulfill another users reservation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/reservations/{$reservation->id}");

    $response->assertForbidden();
});

it('cannot fulfill already fulfilled reservation', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->fulfilled()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson("/reservations/{$reservation->id}");

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'This reservation has already been fulfilled.');
});

it('can cancel a reservation', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $user->id, 'fulfilled' => false]);

    $response = $this->actingAs($user)->deleteJson("/reservations/{$reservation->id}");

    $response->assertSuccessful();
    expect(Reservation::find($reservation->id))->toBeNull();
});

it('cannot cancel another users reservation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $reservation = Reservation::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->deleteJson("/reservations/{$reservation->id}");

    $response->assertForbidden();
    expect(Reservation::find($reservation->id))->not->toBeNull();
});

it('cannot cancel fulfilled reservation', function () {
    $user = User::factory()->create();
    $reservation = Reservation::factory()->fulfilled()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/reservations/{$reservation->id}");

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Cannot cancel a fulfilled reservation.');
    
    expect(Reservation::find($reservation->id))->not->toBeNull();
});

it('validates book_copy_id when creating reservation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/reservations', [
        'book_copy_id' => 999999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('book_copy_id');
});
