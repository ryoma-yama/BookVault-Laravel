<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\GoogleBooksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleBooksController extends Controller
{
    public function __construct(private GoogleBooksService $googleBooksService) {}

    /**
     * Search for a book by ISBN
     * First checks if ISBN already exists in database, then calls Google Books API
     */
    public function searchByIsbn(Request $request): JsonResponse
    {
        $request->validate([
            'isbn' => 'required|string',
        ]);

        // Early return: Check if ISBN already exists in database
        $existingBook = Book::where('isbn_13', $request->isbn)->first();
        if ($existingBook) {
            return response()->json([
                'error' => __('This ISBN is already registered in the system.'),
            ], 422);
        }

        try {
            $bookInfo = $this->googleBooksService->fetchBookInfoByISBN($request->isbn);

            if (! $bookInfo) {
                return response()->json(['error' => 'Book not found'], 404);
            }

            return response()->json($bookInfo);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
