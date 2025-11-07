<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('administrator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:holidays,date',
            'is_collection_skipped' => 'boolean',
            'reschedule_date' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    if ($value && $value === $this->input('date')) {
                        $fail('Reschedule date must be different from holiday date.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The holiday name is required.',
            'date.required' => 'The holiday date is required.',
            'date.unique' => 'A holiday already exists for this date.',
        ];
    }
}
