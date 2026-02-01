<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
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
    public function store(Request $request)
    {
        $request->validate([
            'book_copy_id' => 'required|exists:book_copies,id',
        ]);

        $bookCopy = BookCopy::findOrFail($request->book_copy_id);

        if (! $bookCopy->isAvailable()) {
            return response()->json([
                'message' => 'This book copy is not available for borrowing.',
            ], 422);
        }

        $loan = Loan::create([
            'user_id' => $request->user()->id,
            'book_copy_id' => $request->book_copy_id,
            'borrowed_date' => now(),
        ]);

        return response()->json($loan->load(['bookCopy.book', 'user']), 201);
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
        if (! $this->isOwnedByUser($loan, $request)) {
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
