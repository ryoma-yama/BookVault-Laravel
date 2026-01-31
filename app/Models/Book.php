<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'google_id',
        'isbn_13',
        'title',
        'publisher',
        'published_date',
        'description',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }
}
