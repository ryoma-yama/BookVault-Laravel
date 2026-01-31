<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
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
        'image_url',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * Get the authors for the book.
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_authors');
    }

    /**
     * Get the tags for the book.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the reviews for the book.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the copies for the book.
     */
    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    /**
     * Get the loans for the book.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the average rating for the book.
     */
    public function averageRating(): ?float
    {
        return $this->reviews()->avg('rating');
    }

    /**
     * Get the review count for the book.
     */
    public function reviewCount(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Get the available (not discarded) copies count.
     */
    public function getAvailableCopiesCountAttribute(): int
    {
        return $this->copies()->active()->count();
    }
}
