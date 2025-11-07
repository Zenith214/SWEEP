<?php

namespace App\Http\Requests;

use App\Models\Truck;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTruckStatusRequest extends FormRequest
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
            'operational_status' => [
                'required',
                Rule::in([
                    Truck::STATUS_OPERATIONAL,
                    Truck::STATUS_MAINTENANCE,
                    Truck::STATUS_OUT_OF_SERVICE,
                ]),
            ],
            'notes' => 'nullable|string',
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
            'operational_status.required' => 'The operational status is required.',
            'operational_status.in' => 'The selected operational status is invalid.',
        ];
    }
}
