<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    /**
     * Display a listing of all reviews.
     */
    public function index(): Response
    {
        $reviews = Review::with(['book.authors', 'user'])
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

        return Inertia::render('admin/reviews/index', [
            'reviews' => $reviews,
        ]);
    }
}
