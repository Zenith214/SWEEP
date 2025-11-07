<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
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
            'route_id' => 'required|exists:routes,id',
            'collection_time' => 'required|date_format:H:i',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'is_active' => 'boolean',
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
            'route_id.required' => 'Please select a route.',
            'route_id.exists' => 'The selected route does not exist.',
            'collection_time.required' => 'The collection time is required.',
            'collection_time.date_format' => 'The collection time must be in HH:MM format.',
            'start_date.required' => 'The start date is required.',
            'start_date.after_or_equal' => 'Start date cannot be in the past.',
            'end_date.after' => 'End date must be after start date.',
            'days_of_week.required' => 'Please select at least one collection day.',
            'days_of_week.min' => 'Please select at least one collection day.',
            'days_of_week.*.between' => 'Invalid day of week selected.',
        ];
    }
}
