<?php

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guest cannot access admin dashboard', function () {
    get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('regular user cannot access admin dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can access admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/dashboard'));
});

test('admin dashboard displays total books count', function () {
    $admin = User::factory()->admin()->create();
    Book::factory()->count(10)->create();

    actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/dashboard')
            ->where('stats.total_books', 10)
        );
});

test('admin dashboard displays total users count', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(5)->create();

    actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/dashboard')
            ->where('stats.total_users', 6) // 5 + 1 admin
        );
});

test('admin dashboard displays active loans count', function () {
    $admin = User::factory()->admin()->create();
    Loan::factory()->count(3)->create(); // active loans
    Loan::factory()->count(2)->returned()->create(); // returned loans

    actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/dashboard')
            ->where('stats.active_loans', 3)
        );
});

test('admin dashboard displays total loans count', function () {
    $admin = User::factory()->admin()->create();
    Loan::factory()->count(3)->create();
    Loan::factory()->count(2)->returned()->create();

    actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/dashboard')
            ->where('stats.total_loans', 5)
        );
});
