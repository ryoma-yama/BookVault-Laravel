<?php

namespace App\Models;

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
     */
    public function isAvailable(): bool
    {
        // A copy is available if it's not discarded and has no active loan
        return $this->discarded_date === null && 
               !$this->loans()->whereNull('returned_date')->exists();
    }

    /**
     * Check if the copy is discarded.
     */
    public function isDiscarded(): bool
    {
        return $this->discarded_date !== null;
    }

    public function currentLoan(): ?Loan
    {
        return $this->loans()->whereNull('returned_date')->first();
    }
}
