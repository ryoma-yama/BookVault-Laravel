<?php

use App\Services\GoogleBooksService;
use Illuminate\Support\Facades\Http;

describe('GoogleBooksService', function () {
    beforeEach(function () {
        // Ensure clean state for each test
        Http::preventStrayRequests();
    });

    test('fetches book info successfully with valid ISBN', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::sequence()
                ->push([
                    'items' => [
                        [
                            'id' => 'test-volume-id',
                        ],
                    ],
                ], 200)
                ->push([
                    'volumeInfo' => [
                        'title' => 'Test Book',
                        'authors' => ['Test Author'],
                        'publisher' => 'Test Publisher',
                        'publishedDate' => '2024-01-15',
                        'description' => 'Test book description',
                        'imageLinks' => [
                            'thumbnail' => 'http://example.com/image.jpg',
                        ],
                        'industryIdentifiers' => [
                            [
                                'type' => 'ISBN_13',
                                'identifier' => '9781234567890',
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $service = new GoogleBooksService;
        $result = $service->fetchBookInfoByISBN('9781234567890');

        expect($result)->not->toBeNull()
            ->and($result['google_id'])->toBe('test-volume-id')
            ->and($result['isbn_13'])->toBe('9781234567890')
            ->and($result['title'])->toBe('Test Book')
            ->and($result['authors'])->toContain('Test Author')
            ->and($result['publisher'])->toBe('Test Publisher')
            ->and($result['published_date'])->toBe('2024-01-15')
            ->and($result['description'])->toBe('Test book description')
            ->and($result['image_url'])->toBe('https://example.com/image.jpg');
    });

    test('converts http image URLs to https', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::sequence()
                ->push([
                    'items' => [['id' => 'test-id']],
                ], 200)
                ->push([
                    'volumeInfo' => [
                        'title' => 'Book with HTTP Image',
                        'authors' => [],
                        'publisher' => '',
                        'publishedDate' => '',
                        'description' => '',
                        'imageLinks' => [
                            'thumbnail' => 'http://insecure.example.com/image.jpg',
                        ],
                        'industryIdentifiers' => [],
                    ],
                ], 200),
        ]);

        $service = new GoogleBooksService;
        $result = $service->fetchBookInfoByISBN('9999999999999');

        expect($result['image_url'])->toBe('https://insecure.example.com/image.jpg');
    });

    test('returns null when ISBN not found in search results', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [],
            ], 200),
        ]);

        $service = new GoogleBooksService;
        $result = $service->fetchBookInfoByISBN('9999999999999');

        expect($result)->toBeNull();
    });

    test('returns null when volumeInfo is missing', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::sequence()
                ->push([
                    'items' => [['id' => 'test-id']],
                ], 200)
                ->push([], 200),
        ]);

        $service = new GoogleBooksService;
        $result = $service->fetchBookInfoByISBN('9781234567890');

        expect($result)->toBeNull();
    });

    test('throws exception when search API returns error', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response(
                ['error' => 'Invalid API key'],
                401
            ),
        ]);

        $service = new GoogleBooksService;

        expect(fn () => $service->fetchBookInfoByISBN('9781234567890'))
            ->toThrow(Exception::class, 'Google Books ISBN search failed: 401');
    });

    test('throws exception when detail API returns error', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::sequence()
                ->push([
                    'items' => [['id' => 'test-id']],
                ], 200)
                ->push(
                    ['error' => 'Volume not found'],
                    404
                ),
        ]);

        $service = new GoogleBooksService;

        expect(fn () => $service->fetchBookInfoByISBN('9781234567890'))
            ->toThrow(Exception::class, 'Google Books detail fetch failed: 404');
    });

    test('extracts ISBN-13 from industryIdentifiers correctly', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::sequence()
                ->push([
                    'items' => [['id' => 'test-id']],
                ], 200)
                ->push([
                    'volumeInfo' => [
                        'title' => 'Book with Multiple ISBNs',
                        'authors' => [],
                        'publisher' => '',
                        'publishedDate' => '',
                        'description' => '',
                        'imageLinks' => [],
                        'industryIdentifiers' => [
                            [
                                'type' => 'ISBN_10',
                                'identifier' => '1234567890',
                            ],
                            [
                                'type' => 'ISBN_13',
                                'identifier' => '9781234567890',
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $service = new GoogleBooksService;
        $result = $service->fetchBookInfoByISBN('9781234567890');

        expect($result['isbn_13'])->toBe('9781234567890');
    });

    test('handles missing industryIdentifiers gracefully', function () {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::sequence()
                ->push([
                    'items' => [['id' => 'test-id']],
                ], 200)
                ->push([
                    'volumeInfo' => [
                        'title' => 'Book without ISBN',
                        'authors' => [],
                        'publisher' => '',
                        'publishedDate' => '',
                        'description' => '',
                        'imageLinks' => [],
                    ],
                ], 200),
        ]);

        $service = new GoogleBooksService;
        $result = $service->fetchBookInfoByISBN('9999999999999');

        expect($result['isbn_13'])->toBeNull();
    });
});
