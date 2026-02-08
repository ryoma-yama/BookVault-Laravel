<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\BookCopy;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Display a listing of the user's reservations.
     */
    public function index(Request $request)
    {
        $reservations = Reservation::with(['bookCopy.book', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($reservations);
    }

    /**
     * Store a newly created reservation.
     */
    public function store(StoreReservationRequest $request)
    {
        return DB::transaction(function () use ($request) {
            BookCopy::findOrFail($request->validated()['book_copy_id']);

            $hasActiveReservation = Reservation::where('user_id', $request->user()->id)
                ->where('book_copy_id', $request->validated()['book_copy_id'])
                ->pending()
                ->exists();

            if ($hasActiveReservation) {
                return response()->json([
                    'message' => 'You already have an active reservation for this book copy.',
                ], 422);
            }

            $reservation = Reservation::create([
                'user_id' => $request->user()->id,
                'book_copy_id' => $request->validated()['book_copy_id'],
                'reserved_at' => now(),
                'fulfilled' => false,
            ]);

            return response()->json($reservation->load(['bookCopy.book', 'user']), 201);
        });
    }

    /**
     * Display the specified reservation.
     */
    public function show(Request $request, Reservation $reservation)
    {
        if (! $this->isOwnedByUser($reservation, $request)) {
            return $this->unauthorizedResponse();
        }

        return response()->json($reservation->load(['bookCopy.book', 'user']));
    }

    /**
     * Update the specified reservation (fulfill it).
     */
    public function update(Request $request, Reservation $reservation)
    {
        if (! $this->isOwnedByUser($reservation, $request)) {
            return $this->unauthorizedResponse();
        }

        if (! $reservation->isPending()) {
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
        if (! $this->isOwnedByUser($reservation, $request)) {
            return $this->unauthorizedResponse();
        }

        if (! $reservation->isPending()) {
            return response()->json([
                'message' => 'Cannot cancel a fulfilled reservation.',
            ], 422);
        }

        $reservation->cancel();

        return response()->json(['message' => 'Reservation cancelled successfully']);
    }

    /**
     * Check if the reservation belongs to the authenticated user.
     */
    private function isOwnedByUser(Reservation $reservation, Request $request): bool
    {
        return $reservation->user_id === $request->user()->id;
    }

    /**
     * Return a standardized unauthorized response.
     */
    private function unauthorizedResponse()
    {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
