<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Display a listing of the user's loans.
     */
    public function index(Request $request)
    {
        $loans = Loan::with(['bookCopy.book', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($loans);
    }

    /**
     * Store a newly created loan (borrow a book).
     */
    public function store(StoreLoanRequest $request)
    {
        $loan = Loan::create([
            'user_id' => $request->user()->id,
            'book_copy_id' => $request->getBookCopy()->id,
            'borrowed_date' => now(),
        ]);

        $loan->load(['bookCopy.book', 'user']);

        // For Inertia requests, redirect back
        if ($request->wantsJson()) {
            return response()->json($loan, 201);
        }

        return back();
    }

    /**
     * Display the specified loan.
     */
    public function show(Request $request, Loan $loan)
    {
        if (! $this->isOwnedByUser($loan, $request)) {
            return $this->unauthorizedResponse();
        }

        return response()->json($loan->load(['bookCopy.book', 'user']));
    }

    /**
     * Update the specified loan (return a book).
     */
    public function update(Request $request, Loan $loan)
    {
        // Allow admins to return any loan (proxy return)
        $isAdmin = $request->user()->isAdmin();
        $isOwner = $this->isOwnedByUser($loan, $request);

        if (! $isAdmin && ! $isOwner) {
            return $this->unauthorizedResponse();
        }

        if (! $loan->isOutstanding()) {
            return response()->json([
                'message' => 'This loan has already been returned.',
            ], 422);
        }

        $loan->returnBook();

        return response()->json($loan->fresh()->load(['bookCopy.book', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json(['message' => 'Loan records cannot be deleted'], 403);
    }

    /**
     * Check if the loan belongs to the authenticated user.
     */
    private function isOwnedByUser(Loan $loan, Request $request): bool
    {
        return $loan->user_id === $request->user()->id;
    }

    /**
     * Return a standardized unauthorized response.
     */
    private function unauthorizedResponse()
    {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
