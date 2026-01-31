<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    protected $fillable = [
        'google_id',
        'isbn_13',
        'title',
        'publisher',
        'published_date',
        'description',
        'image_url',
    ];

    /**
     * Get the authors for the book.
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors');
    }
}
