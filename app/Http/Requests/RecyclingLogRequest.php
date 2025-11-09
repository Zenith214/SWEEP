<?php

namespace App\Http\Requests;

use App\Models\RecyclingLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecyclingLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // For creating new logs
        if ($this->isMethod('POST')) {
            return $this->user()->hasRole('collection_crew');
        }

        // For updating existing logs
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $log = $this->route('recycling_log') ?? $this->route('log');
            
            if (!$log || !($log instanceof RecyclingLog)) {
                return false;
            }

            // Check if user owns the log
            if ($log->user_id !== $this->user()->id) {
                return false;
            }

            // Check if within edit window
            if (!$log->isWithinEditWindow()) {
                return false;
            }

            return $this->user()->hasRole('collection_crew');
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'collection_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
            'quality_issue' => 'boolean',
            'materials' => 'required|array|min:1|max:6',
            'materials.*.material_type' => [
                'required',
                Rule::in(['plastic', 'paper', 'glass', 'metal', 'cardboard', 'organic'])
            ],
            'materials.*.weight' => 'required|numeric|min:0.01|max:10000',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for unique material types within the submission
            if ($this->has('materials')) {
                $materialTypes = collect($this->input('materials'))
                    ->pluck('material_type')
                    ->filter();

                if ($materialTypes->count() !== $materialTypes->unique()->count()) {
                    $validator->errors()->add(
                        'materials',
                        'Each material type can only be selected once per log.'
                    );
                }
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'collection_date.required' => 'Collection date is required.',
            'collection_date.date' => 'Collection date must be a valid date.',
            'collection_date.before_or_equal' => 'Collection date cannot be in the future.',
            'notes.string' => 'Notes must be text.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
            'quality_issue.boolean' => 'Quality issue must be true or false.',
            'materials.required' => 'At least one material type must be selected.',
            'materials.array' => 'Materials must be provided as a list.',
            'materials.min' => 'At least one material type must be selected.',
            'materials.max' => 'You can select a maximum of 6 material types.',
            'materials.*.material_type.required' => 'Material type is required for each entry.',
            'materials.*.material_type.in' => 'Invalid material type selected. Must be one of: plastic, paper, glass, metal, cardboard, organic.',
            'materials.*.weight.required' => 'Weight is required for each material.',
            'materials.*.weight.numeric' => 'Weight must be a number.',
            'materials.*.weight.min' => 'Weight must be at least 0.01 kg.',
            'materials.*.weight.max' => 'Weight cannot exceed 10,000 kg.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'collection_date' => 'collection date',
            'materials.*.material_type' => 'material type',
            'materials.*.weight' => 'weight',
        ];
    }
}
