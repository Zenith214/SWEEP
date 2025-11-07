<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DuplicateScheduleRequest extends FormRequest
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
        $sourceRouteId = $this->route('schedule')->route_id;

        return [
            'target_route_id' => [
                'required',
                'exists:routes,id',
                'different:source_route_id',
                function ($attribute, $value, $fail) use ($sourceRouteId) {
                    if ($value == $sourceRouteId) {
                        $fail('The target route must be different from the source route.');
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
            'target_route_id.required' => 'Please select a target route.',
            'target_route_id.exists' => 'The selected route does not exist.',
            'target_route_id.different' => 'The target route must be different from the source route.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'source_route_id' => $this->route('schedule')->route_id,
        ]);
    }
}
