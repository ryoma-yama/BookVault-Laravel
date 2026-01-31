<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::with(['tags', 'reviews']);

        // Filter by tags if provided
        if ($request->has('tags')) {
            $tagNames = is_array($request->tags) ? $request->tags : [$request->tags];
            $query->whereHas('tags', function ($q) use ($tagNames) {
                $q->whereIn('name', $tagNames);
            });
        }

        $books = $query->paginate(10);

        // Add review statistics to each book
        $books->getCollection()->transform(function ($book) {
            $book->average_rating = $book->averageRating();
            $book->review_count = $book->reviewCount();
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

        // Handle tags if provided
        if ($request->has('tags')) {
            $this->syncTags($book, $request->tags);
        }

        return response()->json($book->load(['tags', 'reviews']), 201);
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): JsonResponse
    {
        $book->load(['tags', 'reviews.user']);
        $book->average_rating = $book->averageRating();
        $book->review_count = $book->reviewCount();

        return response()->json($book);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->except('tags'));

        // Handle tags if provided
        if ($request->has('tags')) {
            $this->syncTags($book, $request->tags);
        }

        return response()->json($book->load(['tags', 'reviews']));
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(null, 204);
    }

    /**
     * Sync tags for a book.
     */
    private function syncTags(Book $book, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        $book->tags()->sync($tagIds);
    }
}
