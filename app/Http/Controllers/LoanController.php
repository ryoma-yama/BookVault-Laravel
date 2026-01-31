<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Display a listing of the user's loans.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $loans = Loan::with(['bookCopy.book', 'user'])
            ->where('user_id', $user->id)
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

        if (!$bookCopy->isAvailable()) {
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
        if ($loan->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($loan->load(['bookCopy.book', 'user']));
    }

    /**
     * Update the specified loan (return a book).
     */
    public function update(Request $request, Loan $loan)
    {
        if ($loan->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$loan->isActive()) {
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
        // Typically, we don't delete loan records as they're historical data
        return response()->json(['message' => 'Loan records cannot be deleted'], 403);
    }
}
