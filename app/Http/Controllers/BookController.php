<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Inertia\Inertia;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index()
    {
        $books = Book::with('authors')
            ->latest()
            ->paginate(12);

        return Inertia::render('Books/Index', [
            'books' => $books,
        ]);
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book)
    {
        $book->load('authors');

        return Inertia::render('Books/Show', [
            'book' => $book,
        ]);
    }
}
