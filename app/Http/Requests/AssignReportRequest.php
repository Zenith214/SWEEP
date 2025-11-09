<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AssignReportRequest extends FormRequest
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
            'route_id' => 'nullable|exists:routes,id',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validate that at least one field is provided
            if (empty($this->route_id) && empty($this->assigned_to)) {
                $validator->errors()->add(
                    'assignment',
                    'Please provide at least a route or crew member to assign.'
                );
            }

            // Validate that assigned_to user has collection_crew role
            if ($this->assigned_to) {
                $user = \App\Models\User::find($this->assigned_to);
                if ($user && !$user->hasRole('collection_crew')) {
                    $validator->errors()->add(
                        'assigned_to',
                        'The selected user must be a collection crew member.'
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
            'route_id.exists' => 'The selected route does not exist.',
            'assigned_to.exists' => 'The selected user does not exist.',
        ];
    }
}
