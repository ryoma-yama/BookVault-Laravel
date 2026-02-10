<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookCopyRequest;
use App\Http\Requests\Admin\UpdateBookCopyRequest;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\RedirectResponse;

class BookCopyController extends Controller
{
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

        return to_route('admin.books.edit', $book)
            ->with('success', __('Book copy added successfully.'));
    }

    /**
     * Update the specified copy in storage.
     */
    public function update(UpdateBookCopyRequest $request, Book $book, BookCopy $copy): RedirectResponse
    {
        $copy->update($request->validated());

        return to_route('admin.books.edit', $book)
            ->with('success', __('Book copy updated successfully.'));
    }

    /**
     * Remove the specified copy from storage.
     */
    public function destroy(Book $book, BookCopy $copy): RedirectResponse
    {
        $copy->delete();

        return to_route('admin.books.edit', $book)
            ->with('success', __('Book copy deleted successfully.'));
    }
}
