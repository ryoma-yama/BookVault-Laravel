<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
        return [
            'google_id' => ['nullable', 'string', 'max:100', 'unique:books,google_id'],
            'isbn_13' => ['required', 'string', 'size:13', 'unique:books,isbn_13'],
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
