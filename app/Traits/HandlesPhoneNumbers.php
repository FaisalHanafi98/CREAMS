<?php

namespace App\Traits;

use App\Rules\MalaysianPhoneRule;

trait HandlesPhoneNumbers
{
    /**
     * Normalize phone numbers before saving to database
     */
    public function normalizePhoneNumbers(array $data, array $phoneFields = [])
    {
        // Default phone fields if not specified
        if (empty($phoneFields)) {
            $phoneFields = [
                'phone', 
                'trainee_phone_number', 
                'guardian_phone', 
                'emergency_contact_phone',
                'contact_phone',
                'mobile',
                'telephone'
            ];
        }

        foreach ($phoneFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $data[$field] = MalaysianPhoneRule::normalize($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Get phone validation rules for Malaysian numbers
     */
    public function getPhoneValidationRules($allowLandline = true, $required = true)
    {
        $rules = [
            new MalaysianPhoneRule($allowLandline),
            'string',
            'max:20'
        ];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    /**
     * Format phone number for display
     */
    public function formatPhone($phone)
    {
        return MalaysianPhoneRule::format($phone);
    }
}