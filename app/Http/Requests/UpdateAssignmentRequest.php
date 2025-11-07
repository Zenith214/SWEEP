<?php

namespace App\Http\Requests;

use App\Models\Truck;
use App\Models\User;
use App\Services\AssignmentService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
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
            'truck_id' => 'required|exists:trucks,id',
            'user_id' => 'required|exists:users,id',
            'route_id' => 'required|exists:routes,id',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation for truck operational status
            if ($this->truck_id) {
                $truck = Truck::find($this->truck_id);
                if ($truck && !$truck->isOperational()) {
                    $validator->errors()->add(
                        'truck_id',
                        "This truck is not operational. Current status: {$truck->operational_status}"
                    );
                }
            }

            // Custom validation for user role (must be collection_crew)
            if ($this->user_id) {
                $user = User::find($this->user_id);
                if ($user && !$user->hasRole('collection_crew')) {
                    $validator->errors()->add(
                        'user_id',
                        'Selected user is not a collection crew member'
                    );
                }
            }

            // Custom validation for conflicts using AssignmentService
            // Exclude the current assignment being updated
            if ($this->truck_id && $this->user_id && $this->route_id && $this->assignment_date) {
                $assignmentService = app(AssignmentService::class);
                $conflicts = $assignmentService->checkConflicts([
                    'truck_id' => $this->truck_id,
                    'user_id' => $this->user_id,
                    'route_id' => $this->route_id,
                    'assignment_date' => $this->assignment_date,
                ], $this->assignment);

                foreach ($conflicts as $conflict) {
                    $validator->errors()->add('assignment_date', $conflict);
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
            'truck_id.required' => 'The truck is required.',
            'truck_id.exists' => 'The selected truck does not exist.',
            'user_id.required' => 'The crew member is required.',
            'user_id.exists' => 'The selected crew member does not exist.',
            'route_id.required' => 'The route is required.',
            'route_id.exists' => 'The selected route does not exist.',
            'assignment_date.required' => 'The assignment date is required.',
            'assignment_date.date' => 'The assignment date must be a valid date.',
        ];
    }
}
