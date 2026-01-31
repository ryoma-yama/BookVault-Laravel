<?php

namespace App\Http\Requests;

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
        $bookId = $this->route('book');
        
        return [
            'google_id' => ['nullable', 'string', 'max:100', 'unique:books,google_id,' . $bookId],
            'isbn_13' => ['sometimes', 'required', 'string', 'size:13', 'unique:books,isbn_13,' . $bookId],
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'publisher' => ['sometimes', 'required', 'string', 'max:100'],
            'published_date' => ['sometimes', 'required', 'date'],
            'description' => ['sometimes', 'required', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
