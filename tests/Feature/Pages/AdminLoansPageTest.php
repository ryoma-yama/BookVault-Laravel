<?php

use App\Models\Loan;
use App\Models\User;

it('renders admin loans page without errors for admin user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    Loan::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get('/admin/loans');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/loans/index')
        ->has('loans', 3)
        ->has('loans.0.book_copy')
        ->has('loans.0.book_copy.book')
        ->has('loans.0.user')
    );
});

it('renders admin loans page with no loans', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/loans');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/loans/index')
        ->has('loans', 0)
    );
});

it('can access book title and user name from loan data structure', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['name' => 'John Doe']);
    $loan = Loan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get('/admin/loans');

    $response->assertSuccessful();
    $loans = $response->viewData('page')['props']['loans'];
    
    // Verify book_copy.book structure
    expect($loans[0]['book_copy'])->toHaveKey('book');
    expect($loans[0]['book_copy']['book'])->toHaveKey('title');
    expect($loans[0]['book_copy']['book']['title'])->toBeString();
    
    // Verify user structure
    expect($loans[0]['user'])->toHaveKey('name');
    expect($loans[0]['user']['name'])->toBe('John Doe');
});

it('includes all required loan data for calculations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'borrowed_date' => now()->subDays(5),
        'returned_date' => null,
    ]);

    $response = $this->actingAs($admin)->get('/admin/loans');

    $response->assertSuccessful();
    $loans = $response->viewData('page')['props']['loans'];
    
    expect($loans[0])->toHaveKeys(['borrowed_date', 'returned_date', 'book_copy', 'user']);
});
