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
