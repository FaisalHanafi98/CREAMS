<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt with comprehensive logging
     */
    protected function failedValidation(Validator $validator)
    {
        $this->logValidationFailure($validator);

        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'The provided data is invalid.',
                    'errors' => $this->formatErrors($validator->errors()->toArray()),
                    'error_code' => 'VALIDATION_ERROR',
                    'error_id' => uniqid('val_err_')
                ], 422)
            );
        }

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    /**
     * Log validation failure with context
     */
    protected function logValidationFailure(Validator $validator): void
    {
        Log::channel('validation')->warning('Form validation failed', [
            'form_request' => get_class($this),
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'user_id' => session('id'),
            'user_role' => session('role'),
            'ip' => $this->ip(),
            'validation_errors' => $validator->errors()->toArray(),
            'failed_fields' => array_keys($validator->errors()->toArray()),
            'input_data' => $this->sanitizeInputData(),
            'timestamp' => now(),
            'session_id' => $this->session()->getId()
        ]);
    }

    /**
     * Format validation errors with user-friendly messages
     */
    protected function formatErrors(array $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $field => $messages) {
            $formattedErrors[$field] = [
                'messages' => $messages,
                'friendly_message' => $this->getFriendlyFieldMessage($field, $messages[0] ?? ''),
                'field_label' => $this->getFieldLabel($field)
            ];
        }

        return $formattedErrors;
    }

    /**
     * Get friendly field message
     */
    protected function getFriendlyFieldMessage(string $field, string $message): string
    {
        $fieldLabel = $this->getFieldLabel($field);

        // Common validation error patterns with friendly messages
        $patterns = [
            'required' => "Please provide your {$fieldLabel}.",
            'email' => "Please enter a valid email address.",
            'min' => "The {$fieldLabel} is too short.",
            'max' => "The {$fieldLabel} is too long.",
            'numeric' => "Please enter a valid number for {$fieldLabel}.",
            'date' => "Please enter a valid date for {$fieldLabel}.",
            'unique' => "This {$fieldLabel} is already in use.",
            'confirmed' => "The {$fieldLabel} confirmation does not match.",
            'image' => "Please upload a valid image file for {$fieldLabel}.",
            'mimes' => "Please upload a valid file type for {$fieldLabel}.",
            'size' => "The file size for {$fieldLabel} is too large.",
        ];

        foreach ($patterns as $rule => $friendlyMessage) {
            if (str_contains(strtolower($message), $rule)) {
                return $friendlyMessage;
            }
        }

        return $message; // Return original message if no pattern matches
    }

    /**
     * Get user-friendly field label
     */
    protected function getFieldLabel(string $field): string
    {
        $labels = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'phone_number' => 'Phone Number',
            'ic_number' => 'IC Number',
            'date_of_birth' => 'Date of Birth',
            'email' => 'Email Address',
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation',
            'centre_id' => 'Centre',
            'role' => 'Role',
            'status' => 'Status',
            'address_line_1' => 'Address Line 1',
            'address_line_2' => 'Address Line 2',
            'city' => 'City',
            'state' => 'State',
            'postcode' => 'Postcode',
            'emergency_contact_name' => 'Emergency Contact Name',
            'emergency_contact_phone' => 'Emergency Contact Phone',
            'guardian_name' => 'Guardian Name',
            'guardian_phone' => 'Guardian Phone',
            'profile_picture' => 'Profile Picture',
            'message' => 'Message',
            'subject' => 'Subject',
        ];

        return $labels[$field] ?? ucwords(str_replace(['_', '-'], ' ', $field));
    }

    /**
     * Sanitize input data for logging (remove sensitive information)
     */
    protected function sanitizeInputData(): array
    {
        $data = $this->all();
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'api_secret', '_token'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'email' => 'Please enter a valid email address.',
            'unique' => 'This :attribute is already in use.',
            'min' => 'The :attribute must be at least :min characters.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'confirmed' => 'The :attribute confirmation does not match.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be a whole number.',
            'date' => 'The :attribute must be a valid date.',
            'image' => 'The :attribute must be an image file.',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'size' => 'The :attribute may not be greater than :size kilobytes.',
            'phone' => 'Please enter a valid Malaysian phone number.',
            'ic' => 'Please enter a valid Malaysian IC number.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'phone_number' => 'phone number',
            'ic_number' => 'IC number',
            'date_of_birth' => 'date of birth',
            'email' => 'email address',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'centre_id' => 'centre',
            'address_line_1' => 'address line 1',
            'address_line_2' => 'address line 2',
            'postcode' => 'postal code',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_phone' => 'emergency contact phone',
            'guardian_name' => 'guardian name',
            'guardian_phone' => 'guardian phone',
            'profile_picture' => 'profile picture',
        ];
    }

    /**
     * Log successful validation for audit trail
     */
    protected function passedValidation(): void
    {
        Log::channel('validation')->debug('Form validation passed', [
            'form_request' => get_class($this),
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'user_id' => session('id'),
            'validated_fields' => array_keys($this->validated()),
            'timestamp' => now()
        ]);
    }
}