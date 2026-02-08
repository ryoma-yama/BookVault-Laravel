<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
    /**
     * Display all loans for admin management.
     */
    public function index(): Response
    {
        $loans = Loan::with(['bookCopy.book', 'user'])
            ->latest('borrowed_date')
            ->get();

        return Inertia::render('admin/loans/index', [
            'loans' => $loans,
        ]);
    }
}
