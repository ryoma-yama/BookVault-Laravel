<?php

use App\Models\User;

test('profile page displays display_name field', function () {
    $user = User::factory()->create(['display_name' => 'Test Display Name']);

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('display_name can be updated', function () {
    $user = User::factory()->create(['display_name' => 'Old Name']);

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'display_name' => 'New Display Name',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->display_name)->toBe('New Display Name');
});

test('display_name is required', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'display_name' => '',
        ]);

    $response->assertSessionHasErrors('display_name');
});

test('display_name cannot exceed 255 characters', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'display_name' => str_repeat('a', 256),
        ]);

    $response->assertSessionHasErrors('display_name');
});

test('user can register with display_name', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'display_name' => 'My Display Name',
    ]);

    $response->assertRedirect();
    
    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->display_name)->toBe('My Display Name');
});

test('display_name is required during registration', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('display_name');
});
