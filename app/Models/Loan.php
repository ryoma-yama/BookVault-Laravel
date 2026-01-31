<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->returned_date === null;
    }

    public function returnBook(): void
    {
        $this->returned_date = now();
        $this->save();
    }
}
