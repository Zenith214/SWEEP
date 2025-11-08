<?php

namespace App\Http\Requests;

use App\Models\CollectionLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $collectionLog = $this->route('collectionLog');
        
        // Check if the log can be edited by the current user
        return $collectionLog && $collectionLog->canBeEditedBy($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'completion_time' => 'required_if:status,' . CollectionLog::STATUS_COMPLETED . '|nullable|date',
            'status' => [
                'required',
                Rule::in([
                    CollectionLog::STATUS_COMPLETED,
                    CollectionLog::STATUS_INCOMPLETE,
                    CollectionLog::STATUS_ISSUE_REPORTED
                ])
            ],
            'issue_type' => [
                'required_if:status,' . CollectionLog::STATUS_ISSUE_REPORTED,
                'nullable',
                Rule::in(array_keys(CollectionLog::ISSUE_TYPES))
            ],
            'issue_description' => 'required_if:status,' . CollectionLog::STATUS_ISSUE_REPORTED . '|nullable|string',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'crew_notes' => 'nullable|string|max:1000'
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
            'completion_time.required_if' => 'Completion time is required when status is completed.',
            'completion_time.date' => 'Completion time must be a valid date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid status selected.',
            'issue_type.required_if' => 'Issue type is required when reporting an issue.',
            'issue_type.in' => 'Invalid issue type selected.',
            'issue_description.required_if' => 'Issue description is required when reporting an issue.',
            'completion_percentage.integer' => 'Completion percentage must be a number.',
            'completion_percentage.min' => 'Completion percentage must be at least 0.',
            'completion_percentage.max' => 'Completion percentage cannot exceed 100.',
            'crew_notes.max' => 'Crew notes cannot exceed 1000 characters.'
        ];
    }
}
