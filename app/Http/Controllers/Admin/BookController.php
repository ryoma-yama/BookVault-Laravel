<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Book\StoreBook;
use App\Actions\Book\UpdateBook;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Inertia\Inertia;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index()
    {
        $books = Book::with(['authors:id,name', 'tags:id,name'])
            ->withCount(['copies as copies_count' => function ($query) {
                $query->active();
            }])
            ->latest()
            ->paginate(12);

        return Inertia::render('admin/books/index', [
            'books' => $books,
        ]);
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        return Inertia::render('admin/books/form');
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request, StoreBook $action)
    {
        $action->execute($request->validated());

        return to_route('admin.books.index');
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book)
    {
        $book->load([
            'authors:id,name',
            'tags:id,name',
            'copies' => function ($query) {
                $query->active()->orderBy('acquired_date', 'desc');
            },
        ]);

        return Inertia::render('admin/books/form', [
            'book' => $book,
        ]);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book, UpdateBook $action)
    {
        $action->execute($book, $request->validated());

        return to_route('admin.books.index');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return to_route('admin.books.index');
    }
}
