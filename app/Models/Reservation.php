<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
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
