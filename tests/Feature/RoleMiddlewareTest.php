<?php

use App\Models\User;

test('admin can access admin-only routes', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this
        ->actingAs($admin)
        ->get('/test-admin-route');

    $response->assertStatus(200);
});

test('regular user cannot access admin-only routes', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this
        ->actingAs($user)
        ->get('/test-admin-route');

    $response->assertForbidden();
});

test('guest cannot access admin-only routes', function () {
    $response = $this->get('/test-admin-route');

    $response->assertRedirect(route('login'));
});
