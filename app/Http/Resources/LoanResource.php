<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'bookCopyId' => $this->book_copy_id,
            'borrowedDate' => $this->borrowed_date?->toDateString(),
            'returnedDate' => $this->returned_date?->toDateString(),
            'createdAt' => $this->created_at?->toISOString(),
            'updatedAt' => $this->updated_at?->toISOString(),
            'bookCopy' => $this->whenLoaded('bookCopy', function () {
                $bookCopy = $this->bookCopy;

                return [
                    'id' => $bookCopy->id,
                    'bookId' => $bookCopy->book_id,
                    'acquiredDate' => $bookCopy->acquired_date?->toDateString(),
                    'discardedDate' => $bookCopy->discarded_date?->toDateString(),
                    'book' => isset($bookCopy->book) ? [
                        'id' => $bookCopy->book->id,
                        'title' => $bookCopy->book->title,
                        'isbn13' => $bookCopy->book->isbn_13 ?? null,
                        'publisher' => $bookCopy->book->publisher ?? null,
                        'publishedDate' => $bookCopy->book->published_date ?? null,
                        'description' => $bookCopy->book->description ?? null,
                        'imageUrl' => $bookCopy->book->image_url ?? null,
                    ] : null,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'role' => $this->user->role ?? null,
                ];
            }),
        ];
    }
}
