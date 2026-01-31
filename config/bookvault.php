<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Google Books API, etc.
    |
    */

    'google_books' => [
        'api_key' => env('GOOGLE_BOOKS_API_KEY', ''),
        'api_base_url' => env('GOOGLE_BOOKS_API_BASE_URL', 'https://www.googleapis.com/books/v1/volumes'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Storage Policy
    |--------------------------------------------------------------------------
    |
    | BookVault uses Google Books API image URLs directly instead of
    | uploading and storing book cover images locally. This reduces
    | storage requirements and ensures up-to-date book information.
    |
    | Cover images are accessed using the Google Books volume ID:
    | https://books.google.com/books/content?id={volume_id}&printsec=frontcover&img=1&zoom=1&source=gbs_api
    |
    */

];
