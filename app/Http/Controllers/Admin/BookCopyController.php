<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookCopyRequest;
use App\Http\Requests\Admin\UpdateBookCopyRequest;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookCopyController extends Controller
{
    /**
     * Display the copies for the specified book.
     */
    public function show(Book $book): Response
    {
        $copies = $book->copies()
            ->orderBy('acquired_date', 'desc')
            ->get();

        return Inertia::render('admin/copies/show', [
            'book' => $book,
            'copies' => $copies,
        ]);
    }

    /**
     * Store a newly created copy in storage.
     */
    public function store(StoreBookCopyRequest $request, Book $book): RedirectResponse
    {
        BookCopy::create([
            'book_id' => $book->id,
            'acquired_date' => $request->validated()['acquired_date'],
            'discarded_date' => null,
        ]);

        return to_route('admin.copies.show', $book);
    }

    /**
     * Update the specified copy in storage.
     */
    public function update(UpdateBookCopyRequest $request, Book $book, BookCopy $copy): RedirectResponse
    {
        $copy->update($request->validated());

        return to_route('admin.copies.show', $book);
    }

    /**
     * Remove the specified copy from storage.
     */
    public function destroy(Book $book, BookCopy $copy): RedirectResponse
    {
        $copy->delete();

        return to_route('admin.copies.show', $book);
    }
}
