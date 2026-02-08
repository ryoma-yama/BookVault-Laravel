<?php

use App\Models\Loan;
use App\Models\User;

it('requires authentication to access admin loans page', function () {
    $response = $this->get('/admin/loans');

    $response->assertRedirect('/login');
});

it('requires admin role to access admin loans page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/admin/loans');

    $response->assertForbidden();
});

it('allows admin to access loans management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin/loans');

    $response->assertSuccessful();
});

it('displays all loans ordered by borrowed date descending', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $loan1 = Loan::factory()->create([
        'user_id' => $user1->id,
        'borrowed_date' => now()->subDays(5),
    ]);
    $loan2 = Loan::factory()->create([
        'user_id' => $user2->id,
        'borrowed_date' => now()->subDays(1),
    ]);
    $loan3 = Loan::factory()->create([
        'user_id' => $user1->id,
        'borrowed_date' => now()->subDays(10),
    ]);

    $response = $this->actingAs($admin)->get('/admin/loans');

    $response->assertSuccessful();

    $loans = $response->viewData('page')['props']['loans'];
    expect($loans)->toHaveCount(3);

    // Verify order: most recent first
    expect($loans[0]['id'])->toBe($loan2->id);
    expect($loans[1]['id'])->toBe($loan1->id);
    expect($loans[2]['id'])->toBe($loan3->id);
});

it('includes book copy and user relationship data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($admin)->get('/admin/loans');

    $response->assertSuccessful();

    $loans = $response->viewData('page')['props']['loans'];
    expect($loans[0]['book_copy'])->not->toBeNull();
    expect($loans[0]['book_copy']['book'])->not->toBeNull();
    expect($loans[0]['user'])->not->toBeNull();
});

it('admin can return a users loan via proxy', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $loan = Loan::factory()->create([
        'user_id' => $user->id,
        'returned_date' => null,
    ]);

    expect($loan->returned_date)->toBeNull();

    $response = $this->actingAs($admin)->putJson("/loans/{$loan->id}");

    $response->assertSuccessful();

    expect($loan->fresh()->returned_date)->not->toBeNull();
});
