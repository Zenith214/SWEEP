<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $collectionLog = $this->route('collectionLog');
        
        // Check if the log can be edited by the current user
        if (!$collectionLog || !$collectionLog->canBeEditedBy($this->user())) {
            return false;
        }
        
        // Check if the photo count limit has been reached
        if ($collectionLog->photos()->count() >= 5) {
            return false;
        }
        
        return true;
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        $collectionLog = $this->route('collectionLog');
        
        if (!$collectionLog) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Collection log not found.');
        }
        
        if ($collectionLog->photos()->count() >= 5) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Maximum of 5 photos allowed per collection log.');
        }
        
        if (!$collectionLog->isEditable()) {
            throw new \Illuminate\Auth\Access\AuthorizationException('This log can no longer be edited. The 2-hour edit window has expired.');
        }
        
        throw new \Illuminate\Auth\Access\AuthorizationException('You do not have permission to upload photos to this log.');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => 'required|image|mimes:jpeg,png,webp|max:5120' // 5MB in kilobytes
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
            'photo.required' => 'A photo is required.',
            'photo.image' => 'The file must be an image.',
            'photo.mimes' => 'The photo must be in JPEG, PNG, or WEBP format.',
            'photo.max' => 'The photo must be smaller than 5MB.'
        ];
    }
}
