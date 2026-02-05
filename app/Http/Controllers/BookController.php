<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request): Response
    {
        // Use Scout search when there's a search query
        if ($request->filled('search')) {
            $books = Book::search($request->input('search'))
                ->query(function ($builder) {
                    $builder->with([
                        'tags:id,name',
                        'authors:id,name',
                    ]);
                })
                ->paginate(15)
                ->withQueryString();
        } else {
            // Use traditional query builder when no search query
            $query = Book::query()->with([
                'tags:id,name',
                'authors:id,name',
            ]);

            $this->applySorting($query, $request);

            $books = $query->paginate(15)->withQueryString();
        }

        return Inertia::render('books/index', [
            'books' => $books,
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    /**
     * Find a book by ISBN-13.
     */
    public function findByIsbn(Request $request, string $isbn)
    {
        // Validate ISBN format
        $cleanedIsbn = $this->normalizeIsbn($isbn);
        
        if (!$this->isValidIsbn13($cleanedIsbn)) {
            return Inertia::render('books/not-found', [
                'error' => 'Invalid ISBN-13 format',
                'statusCode' => 422,
            ]);
        }

        // Find book by exact ISBN match
        $book = Book::where('isbn_13', $cleanedIsbn)
            ->with(['authors:id,name', 'tags:id,name'])
            ->first();

        if (!$book) {
            return Inertia::render('books/not-found', [
                'error' => 'Book with ISBN ' . $isbn . ' not found',
                'statusCode' => 404,
            ]);
        }

        // Return book detail page
        return $this->show($book);
    }

    /**
     * Normalize ISBN by removing hyphens, spaces, and other non-digit characters.
     */
    private function normalizeIsbn(string $isbn): string
    {
        return preg_replace('/[^0-9]/', '', $isbn);
    }

    /**
     * Check if the given string is a valid ISBN-13.
     */
    private function isValidIsbn13(string $isbn): bool
    {
        // Must be exactly 13 digits and start with 978 or 979
        return preg_match('/^(978|979)\d{10}$/', $isbn) === 1;
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): Response
    {
        $book->load('authors:id,name');

        return Inertia::render('books/show', [
            'book' => $book,
        ]);
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');

        $allowedSortFields = ['title', 'created_at'];
        if (in_array($sortField, $allowedSortFields, true)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }
}
