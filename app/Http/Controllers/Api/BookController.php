<?php

namespace App\Http\Controllers\Api;

use App\Concerns\SyncsRelatedEntities;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    use SyncsRelatedEntities;

    /**
     * Display a listing of books.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::query()
            ->with(['tags:id,name', 'reviews'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->has('tags')) {
            $tagNames = is_array($request->tags) ? $request->tags : [$request->tags];
            $query->whereHas('tags', function ($q) use ($tagNames) {
                $q->whereIn('name', $tagNames);
            });
        }

        $books = $query->paginate(10);

        // Map aggregated columns to expected response format
        $books->getCollection()->transform(function ($book) {
            $book->average_rating = $book->reviews_avg_rating;
            $book->review_count = $book->reviews_count;

            return $book;
        });

        return response()->json($books);
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = Book::create($request->except('tags'));

        if ($request->has('tags')) {
            $this->attachTagsByName($book, $request->tags);
        }

        $book->load(['tags:id,name', 'reviews']);

        return response()->json($book, 201);
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): JsonResponse
    {
        $book->load(['tags:id,name', 'reviews.user:id,name'])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        // Map aggregated columns to expected response format
        $book->average_rating = $book->reviews_avg_rating;
        $book->review_count = $book->reviews_count;

        return response()->json($book);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->except('tags'));

        if ($request->has('tags')) {
            $this->attachTagsByName($book, $request->tags);
        }

        $book->load(['tags:id,name', 'reviews']);

        return response()->json($book);
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
