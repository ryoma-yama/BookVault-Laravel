<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'book_copy_id',
        'user_id',
        'borrowed_date',
        'returned_date',
    ];

    protected $casts = [
        'borrowed_date' => 'date',
        'returned_date' => 'date',
    ];

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    /**
     * Get the user that owns the loan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this loan is still outstanding (book not yet returned).
     */
    public function isOutstanding(): bool
    {
        return $this->returned_date === null;
    }

    /**
     * Legacy alias for isOutstanding() - maintained for backward compatibility.
     */
    public function isActive(): bool
    {
        return $this->isOutstanding();
    }

    /**
     * Scope to get only outstanding (unreturned) loans.
     *
     * @param  Builder<Loan>  $query
     * @return Builder<Loan>
     */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereNull('returned_date');
    }

    /**
     * Scope to get only returned loans.
     *
     * @param  Builder<Loan>  $query
     * @return Builder<Loan>
     */
    public function scopeReturned(Builder $query): Builder
    {
        return $query->whereNotNull('returned_date');
    }

    public function returnBook(): void
    {
        $this->returned_date = now();
        $this->save();
    }
}
