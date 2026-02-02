<?php

use App\Http\Controllers\Admin\BookCopyController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Language route for laravel-react-i18n
Route::get('/languages', [LanguageController::class, 'index'])->name('languages.index');

// Locale switching route
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public book routes
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// Loan and reservation routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::apiResource('loans', LoanController::class);
    Route::apiResource('reservations', ReservationController::class);
});

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');

    Route::resource('books', \App\Http\Controllers\Admin\BookController::class);

    // Google Books API
    Route::post('/api/google-books/search', [\App\Http\Controllers\Api\GoogleBooksController::class, 'searchByIsbn'])
        ->name('api.google-books.search');

    // Book copy management
    Route::get('copies/{book}', [BookCopyController::class, 'show'])->name('copies.show');
    Route::post('copies/{book}', [BookCopyController::class, 'store'])->name('copies.store');
    Route::put('copies/{book}/{copy}', [BookCopyController::class, 'update'])->name('copies.update');
    Route::delete('copies/{book}/{copy}', [BookCopyController::class, 'destroy'])->name('copies.destroy');
});

require __DIR__.'/settings.php';
