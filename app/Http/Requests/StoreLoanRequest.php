<?php

namespace App\Http\Requests;

use App\Models\BookCopy;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
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
            'book_id' => 'required_without:book_copy_id|exists:books,id',
            'book_copy_id' => 'required_without:book_id|exists:book_copies,id',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Only run if base validation passed
            if ($validator->failed()) {
                return;
            }

            $bookCopy = $this->resolveBookCopy();

            if (! $bookCopy) {
                $field = $this->has('book_id') ? 'book_id' : 'book_copy_id';
                $message = $this->has('book_id')
                    ? 'This book is not available for borrowing.'
                    : 'This book copy is not available for borrowing.';

                $validator->errors()->add($field, $message);
            }
        });
    }

    /**
     * Resolve the book copy to borrow.
     * Returns null if no available copy found.
     */
    public function resolveBookCopy(): ?BookCopy
    {
        if ($this->has('book_id')) {
            return BookCopy::availableForBook($this->book_id)->first();
        }

        $bookCopy = BookCopy::find($this->book_copy_id);

        if ($bookCopy && ! $bookCopy->isAvailable()) {
            return null;
        }

        return $bookCopy;
    }

    /**
     * Get the validated book copy instance.
     * This should only be called after validation has passed.
     */
    public function getBookCopy(): BookCopy
    {
        return $this->resolveBookCopy();
    }
}
