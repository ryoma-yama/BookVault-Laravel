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
            'user_id' => $this->user_id,
            'book_copy_id' => $this->book_copy_id,
            'borrowed_date' => $this->borrowed_date?->toDateString(),
            'returned_date' => $this->returned_date?->toDateString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'book_copy' => $this->whenLoaded('bookCopy'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
