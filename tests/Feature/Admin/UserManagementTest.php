<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guest cannot access admin user list', function () {
    get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

test('regular user cannot access admin user list', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('admin can access admin user list', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/users/index'));
});

test('admin user list displays all users', function () {
    $admin = User::factory()->admin()->create();
    $users = User::factory()->count(3)->create();

    actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users.data', 4) // 3 users + 1 admin
        );
});

test('admin can search users by email', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['email' => 'john@example.com']);
    $user2 = User::factory()->create(['email' => 'jane@example.com']);
    $user3 = User::factory()->create(['email' => 'bob@example.com']);

    actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'john']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users.data', 1)
            ->where('users.data.0.email', 'john@example.com')
        );
});

test('admin can search users by name', function () {
    $admin = User::factory()->admin()->create();
    $user1 = User::factory()->create(['name' => 'John Doe']);
    $user2 = User::factory()->create(['name' => 'Jane Smith']);

    actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'Jane']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users.data', 1)
            ->where('users.data.0.name', 'Jane Smith')
        );
});

test('admin can filter users by role', function () {
    $admin = User::factory()->admin()->create();
    $adminUser = User::factory()->admin()->create();
    $regularUser = User::factory()->create();

    actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'admin']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users.data', 2)
        );
});

test('admin user list is paginated', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(25)->create();

    actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/users/index')
            ->has('users.data', 15)
            ->where('users.per_page', 15)
        );
});

test('admin can update user role', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    expect($user->role)->toBe('user');

    actingAs($admin)
        ->patch(route('admin.users.update', $user), [
            'role' => 'admin',
        ])
        ->assertRedirect();

    expect($user->fresh()->role)->toBe('admin');
});

test('regular user cannot update user role', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    actingAs($user)
        ->patch(route('admin.users.update', $targetUser), [
            'role' => 'admin',
        ])
        ->assertForbidden();

    expect($targetUser->fresh()->role)->toBe('user');
});
