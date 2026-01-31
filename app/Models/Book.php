<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'google_id',
        'isbn_13',
        'title',
        'publisher',
        'published_date',
        'description',
    ];

    /**
     * Get the copies for the book.
     */
    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    /**
     * Get the available (not discarded) copies count.
     */
    public function getAvailableCopiesCountAttribute(): int
    {
        return $this->copies()->whereNull('discarded_date')->count();
    }
}
