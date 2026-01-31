<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of reviews for a specific book.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'book']);

        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        $reviews = $query->latest()->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $this->authorize('create', Review::class);

        $review = Review::create([
            'book_id' => $request->book_id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'rating' => $request->rating,
        ]);

        return response()->json($review->load(['user', 'book']), 201);
    }

    /**
     * Display the specified review.
     */
    public function show(Review $review): JsonResponse
    {
        return response()->json($review->load(['user', 'book']));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return response()->json($review->load(['user', 'book']));
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json(null, 204);
    }
}
