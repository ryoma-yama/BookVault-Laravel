<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $langPath = lang_path("{$locale}.json");

        if (File::exists($langPath)) {
            $translations = json_decode(File::get($langPath), true);

            return response()->json($translations);
        }

        return response()->json([]);
    }
}
