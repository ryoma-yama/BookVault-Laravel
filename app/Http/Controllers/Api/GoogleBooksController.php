<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleBooksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleBooksController extends Controller
{
    public function __construct(private GoogleBooksService $googleBooksService) {}

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
                return response()->json([
                    'error' => 'Book not found',
                ], 404);
            }

            // Add cover image URL
            $bookInfo['image_url'] = $this->googleBooksService->getCoverUrl($bookInfo['google_id']);

            return response()->json($bookInfo);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
