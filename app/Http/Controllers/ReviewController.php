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
     * Show the form for creating a new resource.
     */
    public function create(Book $book): Response
    {
        return Inertia::render('reviews/create', [
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

        return Inertia::render('reviews/edit', [
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
