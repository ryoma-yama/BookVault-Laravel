<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
}
