<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookCopyRequest extends FormRequest
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
            'acquired_date' => ['required', 'date'],
            'discarded_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->discarded_date && $this->acquired_date) {
                $acquiredDate = new \DateTime($this->acquired_date);
                $discardedDate = new \DateTime($this->discarded_date);

                if ($discardedDate < $acquiredDate) {
                    $validator->errors()->add('discarded_date', '廃棄日は取得日以降の日付である必要があります。');
                }
            }
        });
    }
}
