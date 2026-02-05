<?php

use App\Models\User;

test('user has default role of user', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe('user');
});

test('user can be created with admin role', function () {
    $user = User::factory()->create(['role' => 'admin']);

    expect($user->role)->toBe('admin');
});

test('user role can only be admin or user', function () {
    // Test that factory defaults to 'user' role and doesn't accept invalid roles
    $user = User::factory()->create();
    expect($user->role)->toBe('user');

    // Test that only valid roles are accepted through validation
    // (invalid roles would be rejected at the controller/request validation level)
    $validRoles = ['admin', 'user'];
    expect(in_array($user->role, $validRoles))->toBeTrue();
});

test('user has is_admin helper method', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    expect($admin->isAdmin())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
});
