<?php

use App\Models\User;

test('profile page displays name field', function () {
    $user = User::factory()->create(['name' => 'Test User Name']);

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('name can be updated', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'New User Name',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('New User Name');
});

test('name is required', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => '',
            'email' => 'test@example.com',
        ]);

    $response->assertSessionHasErrors('name');
});

test('name cannot exceed 255 characters', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
        ]);

    $response->assertSessionHasErrors('name');
});

test('user can register with name', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test User');
});

test('name is required during registration', function () {
    $response = $this->post(route('register'), [
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('name');
});
