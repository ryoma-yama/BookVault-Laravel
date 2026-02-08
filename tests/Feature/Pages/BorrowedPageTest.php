<?php

use App\Models\Loan;
use App\Models\User;

it('renders borrowed books page without errors for authenticated user', function () {
    $user = User::factory()->create();
    Loan::factory()->count(2)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/borrowed');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('borrowed/index')
        ->has('loans', 2)
        ->has('loans.0.book_copy')
        ->has('loans.0.book_copy.book')
    );
});

it('renders borrowed books page with no loans', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/borrowed');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('borrowed/index')
        ->has('loans', 0)
    );
});

it('can access book title from loan data structure', function () {
    $user = User::factory()->create();
    $loan = Loan::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/borrowed');

    $response->assertSuccessful();
    $loans = $response->viewData('page')['props']['loans'];
    
    expect($loans[0]['book_copy'])->toHaveKey('book');
    expect($loans[0]['book_copy']['book'])->toHaveKey('title');
    expect($loans[0]['book_copy']['book']['title'])->toBeString();
});
