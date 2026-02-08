<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::factory()->count(10)->create();
        $bookCopies = \App\Models\BookCopy::all();

        // Create some active loans
        $users->each(function ($user) use ($bookCopies) {
            \App\Models\Loan::factory()->count(rand(0, 2))->create([
                'user_id' => $user->id,
                'book_copy_id' => $bookCopies->random()->id,
            ]);
        });

        // Create some returned loans (history)
        $users->each(function ($user) use ($bookCopies) {
            \App\Models\Loan::factory()->returned()->count(rand(2, 5))->create([
                'user_id' => $user->id,
                'book_copy_id' => $bookCopies->random()->id,
            ]);
        });
    }
}
