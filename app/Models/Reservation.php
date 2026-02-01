<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_copy_id',
        'user_id',
        'reserved_at',
        'fulfilled',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'fulfilled' => 'boolean',
    ];

    /**
     * Scope to get only pending (unfulfilled, uncancelled) reservations.
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('fulfilled', false);
    }

    /**
     * Check if this reservation is still pending.
     */
    public function isPending(): bool
    {
        return ! $this->fulfilled;
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancel(): void
    {
        $this->delete();
    }

    public function fulfill(): void
    {
        $this->fulfilled = true;
        $this->save();
    }
}
