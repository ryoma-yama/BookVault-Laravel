<?php

use App\Models\User;

test('guests can access books index page (public)', function () {
    $response = $this->get(route('books.index'));
    $response->assertOk();
});

test('authenticated users can access books index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('books.index'));
    $response->assertOk();
});

test('authenticated users are redirected to books index after login', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('books.index', absolute: false));
});
