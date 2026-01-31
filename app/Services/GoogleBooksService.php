<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    private const API_BASE = 'https://www.googleapis.com/books/v1/volumes';

    /**
     * Fetch book information from Google Books API by ISBN
     *
     * @param  string  $isbn
     * @return array|null
     */
    public function fetchBookInfoByISBN(string $isbn): ?array
    {
        $apiKey = config('services.google_books.api_key');
        $useApiKey = $apiKey && $apiKey !== 'your-google-books-api-key';

        // Step 1: Search by ISBN
        $searchUrl = self::API_BASE;
        $searchParams = ['q' => "isbn:{$isbn}"];
        if ($useApiKey) {
            $searchParams['key'] = $apiKey;
        }

        $searchResponse = Http::get($searchUrl, $searchParams);

        if (! $searchResponse->successful()) {
            throw new \Exception("Google Books ISBN検索に失敗: {$searchResponse->status()}");
        }

        $searchData = $searchResponse->json();

        if (! isset($searchData['items']) || empty($searchData['items'])) {
            return null;
        }

        $googleId = $searchData['items'][0]['id'];

        // Step 2: Get detailed volume information
        $volumeUrl = self::API_BASE."/{$googleId}";
        $volumeResponse = Http::get($volumeUrl);

        if (! $volumeResponse->successful()) {
            throw new \Exception("Google Books 詳細取得に失敗: {$volumeResponse->status()}");
        }

        $volumeData = $volumeResponse->json();

        if (! isset($volumeData['volumeInfo'])) {
            return null;
        }

        $info = $volumeData['volumeInfo'];

        // Extract ISBN-13
        $isbn13 = null;
        if (isset($info['industryIdentifiers'])) {
            foreach ($info['industryIdentifiers'] as $identifier) {
                if ($identifier['type'] === 'ISBN_13') {
                    $isbn13 = $identifier['identifier'];
                    break;
                }
            }
        }

        return [
            'google_id' => $googleId,
            'isbn_13' => $isbn13,
            'title' => $info['title'] ?? '',
            'authors' => $info['authors'] ?? [],
            'publisher' => $info['publisher'] ?? '',
            'published_date' => $info['publishedDate'] ?? '',
            'description' => $info['description'] ?? '',
        ];
    }

    /**
     * Get Google Books cover image URL
     *
     * @param  string|null  $googleId
     * @return string
     */
    public function getCoverUrl(?string $googleId): string
    {
        if (! $googleId) {
            return '';
        }

        return "https://books.google.com/books/content?id={$googleId}&printsec=frontcover&img=1&zoom=1&source=gbs_api";
    }
}
