<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
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

        return Inertia::render('Admin/Books/Index', [
            'books' => $books,
        ]);
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        return Inertia::render('Admin/Books/Form');
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn_13' => 'required|string|size:13|unique:books,isbn_13',
            'title' => 'required|string|max:100',
            'publisher' => 'required|string|max:100',
            'published_date' => 'required|string',
            'description' => 'required|string',
            'google_id' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'authors' => 'nullable|array',
            'authors.*' => 'string|max:100',
        ]);

        $book = Book::create([
            'google_id' => $validated['google_id'] ?? null,
            'isbn_13' => $validated['isbn_13'],
            'title' => $validated['title'],
            'publisher' => $validated['publisher'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'],
            'image_url' => $validated['image_url'] ?? null,
        ]);

        // Attach authors
        if (! empty($validated['authors'])) {
            $this->syncAuthors($book, $validated['authors']);
        }

        return redirect('/admin/books');
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book)
    {
        $book->load('authors');

        return Inertia::render('Admin/Books/Form', [
            'book' => $book,
        ]);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'isbn_13' => 'required|string|size:13|unique:books,isbn_13,'.$book->id,
            'title' => 'required|string|max:100',
            'publisher' => 'required|string|max:100',
            'published_date' => 'required|string',
            'description' => 'required|string',
            'google_id' => 'nullable|string|max:100',
            'image_url' => 'nullable|string',
            'authors' => 'nullable|array',
            'authors.*' => 'string|max:100',
        ]);

        $book->update([
            'google_id' => $validated['google_id'] ?? null,
            'isbn_13' => $validated['isbn_13'],
            'title' => $validated['title'],
            'publisher' => $validated['publisher'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'],
            'image_url' => $validated['image_url'] ?? null,
        ]);

        // Sync authors
        if (isset($validated['authors'])) {
            $this->syncAuthors($book, $validated['authors']);
        }

        return redirect('/admin/books');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return redirect('/admin/books');
    }

    /**
     * Sync authors for a book.
     */
    private function syncAuthors(Book $book, array $authorNames): void
    {
        $authorIds = [];

        foreach ($authorNames as $name) {
            $author = Author::firstOrCreate(['name' => $name]);
            $authorIds[] = $author->id;
        }

        $book->authors()->sync($authorIds);
    }
}
