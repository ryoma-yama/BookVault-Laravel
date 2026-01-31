<?php

namespace App\Http\Controllers\Api;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of tags.
     */
    public function index(): JsonResponse
    {
        $tags = Tag::withCount('books')->get();

        return response()->json($tags);
    }

    /**
     * Display the specified tag.
     */
    public function show(Tag $tag): JsonResponse
    {
        $tag->load(['books' => function ($query) {
            $query->with(['tags', 'reviews']);
        }]);

        return response()->json($tag);
    }
}
