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
                ->query(function ($builder) use ($request) {
                    $builder->with([
                        'tags:id,name',
                        'authors:id,name',
                    ]);

                    // Apply additional filters
                    if ($request->filled('author')) {
                        $builder->whereHas('authors', function ($q) use ($request) {
                            $q->where('name', 'like', "%{$request->input('author')}%");
                        });
                    }

                    if ($request->filled('publisher')) {
                        $builder->where('publisher', 'like', "%{$request->input('publisher')}%");
                    }

                    if ($request->filled('tag')) {
                        $builder->whereHas('tags', function ($q) use ($request) {
                            $q->where('name', 'like', "%{$request->input('tag')}%");
                        });
                    }
                })
                ->paginate(15)
                ->withQueryString();
        } else {
            // Use traditional query builder when no search query
            $query = Book::query()->with([
                'tags:id,name',
                'authors:id,name',
            ]);

            $this->applySearchFilters($query, $request);
            $this->applySorting($query, $request);

            $books = $query->paginate(15)->withQueryString();
        }

        return Inertia::render('books/index', [
            'books' => $books,
            'filters' => $request->only(['search', 'author', 'publisher', 'tag', 'sort', 'direction']),
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
     * Apply search and filter conditions to the query.
     */
    private function applySearchFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->input('search')}%");
        }

        if ($request->filled('author')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->input('author')}%");
            });
        }

        if ($request->filled('publisher')) {
            $query->where('publisher', 'like', "%{$request->input('publisher')}%");
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->input('tag')}%");
            });
        }
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
