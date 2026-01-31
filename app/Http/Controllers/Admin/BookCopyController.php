<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'acquired_date' => ['required', 'date'],
        ]);

        BookCopy::create([
            'book_id' => $book->id,
            'acquired_date' => $validated['acquired_date'],
            'discarded_date' => null,
        ]);

        return redirect()->route('admin.copies.show', $book);
    }

    /**
     * Update the specified copy in storage.
     */
    public function update(Request $request, Book $book, BookCopy $copy): RedirectResponse
    {
        $validated = $request->validate([
            'acquired_date' => ['required', 'date'],
            'discarded_date' => ['nullable', 'date'],
        ]);

        // Validate that discarded_date is after or equal to acquired_date
        if (isset($validated['discarded_date']) && $validated['discarded_date']) {
            $acquiredDate = new \DateTime($validated['acquired_date']);
            $discardedDate = new \DateTime($validated['discarded_date']);

            if ($discardedDate < $acquiredDate) {
                return back()->withErrors([
                    'discarded_date' => '廃棄日は取得日以降の日付である必要があります。',
                ]);
            }
        }

        $copy->update($validated);

        return redirect()->route('admin.copies.show', $book);
    }

    /**
     * Remove the specified copy from storage.
     */
    public function destroy(Book $book, BookCopy $copy): RedirectResponse
    {
        $copy->delete();

        return redirect()->route('admin.copies.show', $book);
    }
}
