<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of the user's reservations.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $reservations = Reservation::with(['bookCopy.book', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return response()->json($reservations);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_copy_id' => 'required|exists:book_copies,id',
        ]);

        $bookCopy = BookCopy::findOrFail($request->book_copy_id);

        // Check if user already has an active reservation for this copy
        $existingReservation = Reservation::where('user_id', $request->user()->id)
            ->where('book_copy_id', $request->book_copy_id)
            ->where('fulfilled', false)
            ->first();

        if ($existingReservation) {
            return response()->json([
                'message' => 'You already have an active reservation for this book copy.',
            ], 422);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'book_copy_id' => $request->book_copy_id,
            'reserved_at' => now(),
            'fulfilled' => false,
        ]);

        return response()->json($reservation->load(['bookCopy.book', 'user']), 201);
    }

    /**
     * Display the specified reservation.
     */
    public function show(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($reservation->load(['bookCopy.book', 'user']));
    }

    /**
     * Update the specified reservation (fulfill it).
     */
    public function update(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($reservation->fulfilled) {
            return response()->json([
                'message' => 'This reservation has already been fulfilled.',
            ], 422);
        }

        $reservation->fulfill();

        return response()->json($reservation->fresh()->load(['bookCopy.book', 'user']));
    }

    /**
     * Remove the specified reservation (cancel it).
     */
    public function destroy(Request $request, Reservation $reservation)
    {
        if ($reservation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($reservation->fulfilled) {
            return response()->json([
                'message' => 'Cannot cancel a fulfilled reservation.',
            ], 422);
        }

        $reservation->cancel();

        return response()->json(['message' => 'Reservation cancelled successfully']);
    }
}
