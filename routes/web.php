<?php

use App\Http\Controllers\Admin\BookCopyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('copies/{book}', [BookCopyController::class, 'show'])->name('copies.show');
    Route::post('copies/{book}', [BookCopyController::class, 'store'])->name('copies.store');
    Route::put('copies/{book}/{copy}', [BookCopyController::class, 'update'])->name('copies.update');
    Route::delete('copies/{book}/{copy}', [BookCopyController::class, 'destroy'])->name('copies.destroy');
});

require __DIR__.'/settings.php';
