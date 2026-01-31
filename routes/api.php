<?php

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('books', [BookController::class, 'index']);
Route::get('books/{book}', [BookController::class, 'show']);
Route::get('tags', [TagController::class, 'index']);
Route::get('tags/{tag}', [TagController::class, 'show']);
Route::get('reviews', [ReviewController::class, 'index']);

// Protected routes
Route::middleware('auth')->group(function () {
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{book}', [BookController::class, 'update']);
    Route::delete('books/{book}', [BookController::class, 'destroy']);

    Route::post('reviews', [ReviewController::class, 'store']);
    Route::get('reviews/{review}', [ReviewController::class, 'show']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
});
