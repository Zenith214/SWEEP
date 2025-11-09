<?php

namespace App\Http\Requests;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('resident');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_type' => 'required|in:' . implode(',', array_keys(Report::REPORT_TYPES)),
            'location' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|mimes:jpeg,png,webp|max:5120', // 5MB = 5120KB
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
            'report_type.required' => 'Please select a report type.',
            'report_type.in' => 'The selected report type is invalid.',
            'location.required' => 'Please provide the location of the issue.',
            'location.max' => 'The location must not exceed 255 characters.',
            'description.required' => 'Please provide a description of the issue.',
            'description.max' => 'The description must not exceed 2000 characters.',
            'photos.max' => 'You can upload a maximum of 3 photos per report.',
            'photos.*.image' => 'Each file must be an image.',
            'photos.*.mimes' => 'Photos must be in JPEG, PNG, or WEBP format.',
            'photos.*.max' => 'Each photo must not exceed 5MB in size.',
        ];
    }
}
