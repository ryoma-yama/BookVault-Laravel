<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the authenticated user's reviews.
     */
    public function index(): Response
    {
        $reviews = Review::where('user_id', auth()->id())
            ->with(['book.authors', 'user'])
            ->latest()
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'comment' => $review->comment,
                    'is_recommended' => $review->is_recommended,
                    'created_at' => $review->created_at->toIso8601String(),
                    'book' => [
                        'id' => $review->book->id,
                        'title' => $review->book->title,
                        'authors' => $review->book->authors->map(fn ($author) => [
                            'id' => $author->id,
                            'name' => $author->name,
                        ]),
                    ],
                    'user' => [
                        'id' => $review->user->id,
                        'name' => $review->user->name,
                    ],
                ];
            });

        return Inertia::render('reviews/index', [
            'reviews' => $reviews,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Book $book): Response
    {
        return Inertia::render('reviews/form', [
            'book' => $book->load('authors'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request)
    {
        $this->authorize('create', Review::class);

        $review = Review::create([
            'book_id' => $request->book_id,
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
            'is_recommended' => $request->is_recommended,
        ]);

        return redirect()->route('books.show', $review->book_id)
            ->with('success', 'Review posted successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review): Response
    {
        $this->authorize('update', $review);

        return Inertia::render('reviews/form', [
            'review' => $review->load(['book.authors', 'user']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book_id)
            ->with('success', 'Review updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $bookId = $review->book_id;
        $review->delete();

        return redirect()->route('books.show', $bookId)
            ->with('success', 'Review deleted successfully!');
    }
}
