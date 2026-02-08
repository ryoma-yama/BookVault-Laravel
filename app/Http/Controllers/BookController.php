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
                    $builder->hasValidCopies()
                        ->with([
                            'tags:id,name',
                            'authors:id,name',
                        ]);
                })
                ->paginate(15)
                ->withQueryString();
        } else {
            // Use traditional query builder when no search query
            $query = Book::query()
                ->hasValidCopies()
                ->with([
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

        if (! $this->isValidIsbn13($cleanedIsbn)) {
            return back()->withErrors([
                'isbn' => 'Invalid ISBN-13 format. Please scan a valid ISBN-13 barcode.',
            ]);
        }

        // Find book by exact ISBN match
        $book = Book::where('isbn_13', $cleanedIsbn)
            ->with(['authors:id,name', 'tags:id,name'])
            ->first();

        if (! $book) {
            return back()->withErrors([
                'isbn' => 'Book with ISBN '.$isbn.' not found in our catalog.',
            ]);
        }

        // Redirect to book detail page
        return redirect()->route('books.show', $book);
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
        $book->load('authors:id,name', 'tags:id,name');

        // Calculate inventory status
        $activeCopies = $book->copies()->active()->get();
        $totalCopies = $activeCopies->count();
        $borrowedCount = $activeCopies->filter(fn ($copy) => $copy->hasOutstandingLoan())->count();
        $availableCount = $totalCopies - $borrowedCount;

        // Get current loans (outstanding loans for this book)
        $currentLoans = [];
        foreach ($activeCopies as $copy) {
            $outstandingLoan = $copy->loans()->outstanding()->with('user:id,name')->first();
            if ($outstandingLoan) {
                $currentLoans[] = [
                    'user' => [
                        'id' => $outstandingLoan->user->id,
                        'name' => $outstandingLoan->user->name,
                    ],
                    'borrowed_date' => $outstandingLoan->borrowed_date->format('Y/m/d'),
                ];
            }
        }

        return Inertia::render('books/show', [
            'book' => array_merge($book->toArray(), [
                'inventory_status' => [
                    'total_copies' => $totalCopies,
                    'borrowed_count' => $borrowedCount,
                    'available_count' => $availableCount,
                ],
                'current_loans' => $currentLoans,
            ]),
        ]);
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'desc');

        $allowedSortFields = ['id', 'title', 'created_at'];
        if (in_array($sortField, $allowedSortFields, true)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }
}
