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
     * Check if ISBN already exists in database
     */
    public function checkIsbn(Request $request): JsonResponse
    {
        $request->validate([
            'isbn' => 'required|string|size:13',
        ]);

        $exists = Book::where('isbn_13', $request->isbn)->exists();

        if ($exists) {
            return response()->json([
                'exists' => true,
                'error' => __('This ISBN is already registered in the system.'),
            ], 422);
        }

        return response()->json(['exists' => false]);
    }

    /**
     * Search for a book by ISBN
     */
    public function searchByIsbn(Request $request): JsonResponse
    {
        $request->validate([
            'isbn' => 'required|string',
        ]);

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
