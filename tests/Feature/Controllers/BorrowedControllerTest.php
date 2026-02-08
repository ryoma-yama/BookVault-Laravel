<?php

use App\Models\Loan;
use App\Models\User;

it('requires authentication to access borrowed books page', function () {
    $response = $this->get('/borrowed');

    $response->assertRedirect('/login');
});

it('displays users borrowed books ordered by borrowed date descending', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create loans for the authenticated user
    $loan1 = Loan::factory()->create([
        'user_id' => $user->id,
        'borrowed_date' => now()->subDays(5),
    ]);
    $loan2 = Loan::factory()->create([
        'user_id' => $user->id,
        'borrowed_date' => now()->subDays(1),
    ]);
    $loan3 = Loan::factory()->create([
        'user_id' => $user->id,
        'borrowed_date' => now()->subDays(10),
    ]);

    // Create loan for another user (should not appear)
    Loan::factory()->create([
        'user_id' => $otherUser->id,
        'borrowed_date' => now(),
    ]);

    $response = $this->actingAs($user)->get('/borrowed');

    $response->assertSuccessful();
    
    $loans = $response->viewData('page')['props']['loans'];
    expect($loans)->toHaveCount(3);
    
    // Verify order: most recent first
    expect($loans[0]['id'])->toBe($loan2->id);
    expect($loans[1]['id'])->toBe($loan1->id);
    expect($loans[2]['id'])->toBe($loan3->id);
});

it('includes book and user relationship data', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/borrowed');

    $response->assertSuccessful();
    
    $loans = $response->viewData('page')['props']['loans'];
    expect($loans[0]['book_copy'])->not->toBeNull();
    expect($loans[0]['book_copy']['book'])->not->toBeNull();
});

it('displays both active and returned loans', function () {
    $user = User::factory()->create();

    $activeLoan = Loan::factory()->create([
        'user_id' => $user->id,
        'returned_date' => null,
    ]);
    
    $returnedLoan = Loan::factory()->returned()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/borrowed');

    $response->assertSuccessful();
    
    $loans = $response->viewData('page')['props']['loans'];
    expect($loans)->toHaveCount(2);
});
