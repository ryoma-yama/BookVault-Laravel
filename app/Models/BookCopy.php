<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'acquired_date',
        'discarded_date',
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array<int, string>
     */
    protected $touches = ['book'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'acquired_date' => 'date',
        'discarded_date' => 'date',
    ];

    /**
     * Get the book that owns the copy.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Check if the copy is available for loan.
     * A copy is available when it's not discarded and has no outstanding loan.
     */
    public function isAvailable(): bool
    {
        return ! $this->isDiscarded() && ! $this->hasOutstandingLoan();
    }

    /**
     * Check if the copy is discarded (withdrawn from circulation).
     */
    public function isDiscarded(): bool
    {
        return $this->discarded_date !== null;
    }

    /**
     * Check if the copy currently has an outstanding (unreturned) loan.
     */
    public function hasOutstandingLoan(): bool
    {
        return $this->loans()->outstanding()->exists();
    }

    /**
     * Scope to get only active (non-discarded) copies.
     *
     * @param  Builder<BookCopy>  $query
     * @return Builder<BookCopy>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('discarded_date');
    }

    /**
     * Scope to get only discarded copies.
     *
     * @param  Builder<BookCopy>  $query
     * @return Builder<BookCopy>
     */
    public function scopeDiscarded(Builder $query): Builder
    {
        return $query->whereNotNull('discarded_date');
    }

    /**
     * Scope to get available copies for a specific book.
     * A copy is available if it's not discarded and has no outstanding loan.
     *
     * @param  Builder<BookCopy>  $query
     * @param  int  $bookId
     * @return Builder<BookCopy>
     */
    public function scopeAvailableForBook(Builder $query, int $bookId): Builder
    {
        return $query->where('book_id', $bookId)
            ->whereNull('discarded_date')
            ->whereDoesntHave('loans', function ($query) {
                $query->whereNull('returned_date');
            });
    }

    /**
     * Get the current outstanding loan for this copy.
     */
    public function currentLoan(): ?Loan
    {
        return $this->loans()->outstanding()->first();
    }
}
