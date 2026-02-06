<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    private const API_BASE = 'https://www.googleapis.com/books/v1/volumes';

    /**
     * Fetch book information from Google Books API by ISBN.
     *
     * This method uses a two-step process:
     * 1. Search by ISBN to identify the specific Google Books Volume ID.
     * 2. Fetch the full volume details using that ID.
     *
     * Rationale for two steps:
     * - The 'list' API (Step 1) often returns truncated descriptions.
     * - Key fields like 'publisher' are frequently missing in the search results
     *   but present in the detailed 'get' response.
     */
    public function fetchBookInfoByISBN(string $isbn): ?array
    {
        $apiKey = config('services.google_books.api_key');
        $useApiKey = $apiKey && $apiKey !== 'your-google-books-api-key';

        // Step 1: Search by ISBN to get the Google Books Volume ID
        $searchParams = ['q' => "isbn:{$isbn}"];
        if ($useApiKey) {
            $searchParams['key'] = $apiKey;
        }

        $searchResponse = Http::get(self::API_BASE, $searchParams);

        if (! $searchResponse->successful()) {
            throw new \Exception("Google Books ISBN search failed: {$searchResponse->status()}");
        }

        $searchData = $searchResponse->json();

        if (empty($searchData['items'])) {
            return null;
        }

        $googleId = $searchData['items'][0]['id'];

        // Step 2: Get detailed volume information
        $volumeUrl = self::API_BASE."/{$googleId}";
        $volumeResponse = Http::get($volumeUrl);

        if (! $volumeResponse->successful()) {
            throw new \Exception("Google Books detail fetch failed: {$volumeResponse->status()}");
        }

        $volumeData = $volumeResponse->json();
        $info = $volumeData['volumeInfo'] ?? null;

        if (! $info) {
            return null;
        }

        // Extract ISBN-13
        $isbn13 = collect($info['industryIdentifiers'] ?? [])
            ->firstWhere('type', 'ISBN_13')['identifier'] ?? null;

        // Ensure image URL uses HTTPS to avoid mixed content issues
        $thumbnail = $info['imageLinks']['thumbnail'] ?? '';
        $secureImageUrl = str_replace('http://', 'https://', $thumbnail);

        return [
            'google_id' => $googleId,
            'isbn_13' => $isbn13,
            'title' => $info['title'] ?? '',
            'authors' => $info['authors'] ?? [],
            'publisher' => $info['publisher'] ?? '',
            'published_date' => $info['publishedDate'] ?? '',
            'description' => $info['description'] ?? '',
            'image_url' => $secureImageUrl,
        ];
    }
}
