<?php

namespace App\Helpers;

class GoogleBooksHelper
{
    /**
     * Get Google Books cover image URL from volume ID.
     *
     * @param  string|null  $googleId  - Google Books API volume ID
     * @return string - Cover image URL (medium size with zoom=1)
     */
    public static function getCoverUrl(?string $googleId): string
    {
        return 'https://books.google.com/books/content?id='.($googleId ?? '').'&printsec=frontcover&img=1&zoom=1&source=gbs_api';
    }
}
