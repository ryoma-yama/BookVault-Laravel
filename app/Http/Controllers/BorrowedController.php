<?php

namespace App\Http\Controllers;

use App\Http\Resources\LoanResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BorrowedController extends Controller
{
    /**
     * Display the authenticated user's borrowed books.
     */
    public function index(Request $request): Response
    {
        $loans = $request->user()
            ->loans()
            ->with(['bookCopy.book', 'user'])
            ->latest('borrowed_date')
            ->get();

        return Inertia::render('borrowed/index', [
            'loans' => LoanResource::collection($loans)->resolve(),
        ]);
    }
}
