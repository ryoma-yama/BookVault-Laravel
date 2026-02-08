<?php

use App\Http\Controllers\Admin\BookCopyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowedController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

// Language route for laravel-react-i18n
Route::get('/languages', [LanguageController::class, 'index'])->name('languages.index');

// Locale switching route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// Books index route at root (accessible to everyone)
// Named 'home' - reverted from 'home' as requested
Route::get('/', [BookController::class, 'index'])->name('home');
Route::get('/books/isbn/{isbn}', [BookController::class, 'findByIsbn'])->name('books.isbn');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Loan routes (require authentication)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('borrowed', [BorrowedController::class, 'index'])->name('borrowed.index');
    Route::apiResource('loans', LoanController::class);
});

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    Route::get('loans', [AdminLoanController::class, 'index'])->name('loans.index');

    Route::resource('books', \App\Http\Controllers\Admin\BookController::class);

    // Google Books API
    Route::post('/api/google-books/check-isbn', [\App\Http\Controllers\Api\GoogleBooksController::class, 'checkIsbn'])
        ->name('api.google-books.check-isbn');
    Route::post('/api/google-books/search', [\App\Http\Controllers\Api\GoogleBooksController::class, 'searchByIsbn'])
        ->name('api.google-books.search');

    // Book copy management
    Route::get('copies/{book}', [BookCopyController::class, 'show'])->name('copies.show');
    Route::post('copies/{book}', [BookCopyController::class, 'store'])->name('copies.store');
    Route::put('copies/{book}/{copy}', [BookCopyController::class, 'update'])->name('copies.update');
    Route::delete('copies/{book}/{copy}', [BookCopyController::class, 'destroy'])->name('copies.destroy');
});

require __DIR__.'/settings.php';
