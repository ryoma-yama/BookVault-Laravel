<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Book extends Model
{
    /** @use HasFactory<\Database\Factories\BookFactory> */
    use HasFactory, Searchable;

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

    /**
     * Scope to get only books that have at least one valid (non-discarded) copy.
     *
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    public function scopeHasValidCopies(Builder $query): Builder
    {
        return $query->whereHas('copies', function ($q) {
            $q->whereNull('discarded_date');
        });
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        $array = [
            'id' => $this->id,
            'title' => $this->title,
            'publisher' => $this->publisher,
            'description' => $this->description,
        ];

        // Only include authors and tags for non-database drivers (e.g., Meilisearch)
        // Database driver can't search on relationship fields
        if (config('scout.driver') !== 'database') {
            $array['authors'] = $this->authors->pluck('name')->implode(', ');
            $array['tags'] = $this->tags->pluck('name')->implode(', ');
            $array['has_valid_copies'] = $this->copies()->whereNull('discarded_date')->exists();
        }

        return $array;
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['authors', 'tags']);
    }
}
