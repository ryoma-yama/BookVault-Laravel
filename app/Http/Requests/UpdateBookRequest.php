<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');
        $bookId = $book instanceof Book ? $book->id : $book;

        return [
            'google_id' => ['nullable', 'string', 'max:100', 'unique:books,google_id,'.$bookId],
            'isbn_13' => ['required', 'string', 'size:13', 'unique:books,isbn_13,'.$bookId],
            'title' => ['required', 'string', 'max:100'],
            'publisher' => ['required', 'string', 'max:100'],
            'published_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'image_url' => ['nullable', 'string'],
            'authors' => ['nullable', 'array'],
            'authors.*' => ['string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
