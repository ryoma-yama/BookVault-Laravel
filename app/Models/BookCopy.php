<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    protected $fillable = [
        'book_id',
        'acquired_date',
        'discarded_date',
    ];

    protected $casts = [
        'acquired_date' => 'date',
        'discarded_date' => 'date',
    ];

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

    public function isAvailable(): bool
    {
        // A copy is available if it's not discarded and has no active loan
        return $this->discarded_date === null && 
               !$this->loans()->whereNull('returned_date')->exists();
    }

    public function currentLoan(): ?Loan
    {
        return $this->loans()->whereNull('returned_date')->first();
    }
}
