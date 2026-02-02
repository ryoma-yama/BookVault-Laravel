<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    /**
     * Switch the application locale.
     */
    public function switch(Request $request, string $locale)
    {
        // Validate locale
        if (! in_array($locale, ['ja', 'en'])) {
            abort(400, 'Invalid locale');
        }

        // Get the previous URL or default to home
        $previousUrl = $request->headers->get('referer') ?? route('home');

        // Set cookie for 1 year (365 days)
        $cookie = Cookie::make('app_locale', $locale, 60 * 24 * 365);

        return redirect($previousUrl)->cookie($cookie);
    }
}
