<?php

use App\Helpers\GoogleBooksHelper;

describe('GoogleBooksHelper', function () {
    it('generates correct cover image url from google id', function () {
        $googleId = 'abc123xyz';
        $url = GoogleBooksHelper::getCoverUrl($googleId);

        expect($url)->toBe('https://books.google.com/books/content?id=abc123xyz&printsec=frontcover&img=1&zoom=1&source=gbs_api');
    });

    it('handles null google id', function () {
        $url = GoogleBooksHelper::getCoverUrl(null);

        expect($url)->toBe('https://books.google.com/books/content?id=&printsec=frontcover&img=1&zoom=1&source=gbs_api');
    });

    it('handles empty string google id', function () {
        $url = GoogleBooksHelper::getCoverUrl('');

        expect($url)->toBe('https://books.google.com/books/content?id=&printsec=frontcover&img=1&zoom=1&source=gbs_api');
    });

    it('generates correct url with special characters', function () {
        $googleId = 'test-id_123';
        $url = GoogleBooksHelper::getCoverUrl($googleId);

        expect($url)->toContain('id=test-id_123');
    });
});
