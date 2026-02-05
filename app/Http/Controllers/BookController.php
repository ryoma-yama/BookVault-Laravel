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
            $searchTerm = $request->input('search');

            // Check if search term is ISBN-13 format (13 digits)
            if ($this->isIsbn13Format($searchTerm)) {
                // Clean the ISBN for database search (remove hyphens and spaces)
                $cleanedIsbn = preg_replace('/[\s-]/', '', $searchTerm);

                // Try exact match on ISBN-13 first
                $query = Book::query()
                    ->where('isbn_13', $cleanedIsbn)
                    ->with([
                        'tags:id,name',
                        'authors:id,name',
                    ]);

                $books = $query->paginate(15)->withQueryString();

                // If no results from ISBN search, fall back to full-text search
                if ($books->isEmpty()) {
                    $books = Book::search($searchTerm)
                        ->query(function ($builder) {
                            $builder->with([
                                'tags:id,name',
                                'authors:id,name',
                            ]);
                        })
                        ->paginate(15)
                        ->withQueryString();
                }
            } else {
                // Use full-text search for non-ISBN queries
                $books = Book::search($searchTerm)
                    ->query(function ($builder) {
                        $builder->with([
                            'tags:id,name',
                            'authors:id,name',
                        ]);
                    })
                    ->paginate(15)
                    ->withQueryString();
            }
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
     * Check if the given string is in ISBN-13 format.
     */
    private function isIsbn13Format(string $value): bool
    {
        // Remove any hyphens or spaces
        $cleaned = preg_replace('/[\s-]/', '', $value);

        // Check if it's exactly 13 digits and starts with 978 or 979
        return preg_match('/^(978|979)\d{10}$/', $cleaned) === 1;
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
