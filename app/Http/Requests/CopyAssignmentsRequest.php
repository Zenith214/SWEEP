<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CopyAssignmentsRequest extends FormRequest
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
            'source_date' => 'required|date',
            'target_date' => 'required|date|after_or_equal:today|different:source_date',
            'truck_ids' => 'nullable|array',
            'truck_ids.*' => 'exists:trucks,id',
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
            'source_date.required' => 'The source date is required.',
            'source_date.date' => 'The source date must be a valid date.',
            'target_date.required' => 'The target date is required.',
            'target_date.date' => 'The target date must be a valid date.',
            'target_date.after_or_equal' => 'The target date cannot be in the past.',
            'target_date.different' => 'The target date must be different from the source date.',
            'truck_ids.array' => 'The truck selection must be an array.',
            'truck_ids.*.exists' => 'One or more selected trucks do not exist.',
        ];
    }
}
