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
    $this->expectException(\Illuminate\Database\QueryException::class);
    
    User::factory()->create(['role' => 'invalid_role']);
});

test('user has is_admin helper method', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    expect($admin->isAdmin())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
});
