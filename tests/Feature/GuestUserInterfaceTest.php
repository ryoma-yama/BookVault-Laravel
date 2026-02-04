<?php

use function Pest\Laravel\get;

describe('Guest User Interface', function () {
    test('guest user can access home page without errors', function () {
        $response = get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->where('auth.user', null)
        );
    });

    test('guest user should not see user menu in Inertia props', function () {
        $response = get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
        );
    });

    test('authenticated user sees user info in Inertia props', function () {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('books/index')
            ->where('auth.user.id', $user->id)
            ->where('auth.user.name', $user->name)
            ->where('auth.user.email', $user->email)
        );
    });
});
