<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MalaysianPhoneRule implements ValidationRule
{
    protected $allowLandline;
    
    public function __construct($allowLandline = true)
    {
        $this->allowLandline = $allowLandline;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Let required rule handle empty values
        }

        // Remove all non-digit and non-plus characters
        $cleaned = preg_replace('/[^\d+]/', '', $value);
        
        // Normalize to +60 format
        $normalized = $this->normalizePhoneNumber($cleaned);
        
        if (!$normalized) {
            $fail('The :attribute must be a valid Malaysian phone number.');
            return;
        }

        // Validate Malaysian phone number patterns
        if (!$this->isValidMalaysianNumber($normalized)) {
            $fail('The :attribute must be a valid Malaysian phone number (+60 format).');
            return;
        }
    }

    /**
     * Normalize phone number to +60 format
     */
    private function normalizePhoneNumber($phone)
    {
        // Remove spaces and formatting
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Handle different input formats
        if (str_starts_with($phone, '+60')) {
            return $phone; // Already in correct format
        } elseif (str_starts_with($phone, '60')) {
            return '+' . $phone;
        } elseif (str_starts_with($phone, '0')) {
            return '+6' . $phone; // 012345678 -> +6012345678
        } elseif (preg_match('/^[1-9]/', $phone) && strlen($phone) >= 8) {
            return '+60' . $phone; // 12345678 -> +6012345678
        }
        
        return false; // Invalid format
    }

    /**
     * Validate Malaysian phone number patterns
     */
    private function isValidMalaysianNumber($phone)
    {
        // Remove +60 prefix for pattern matching
        $number = str_replace('+60', '', $phone);
        
        // Mobile numbers (10-11 digits after +60)
        $mobilePatterns = [
            '/^1[0-9]-?[0-9]{7,8}$/',  // 10-11 digits starting with 1 (mobile)
            '/^1[1-9][0-9]{7,8}$/'     // Alternative mobile pattern
        ];

        // Landline patterns (8-9 digits after +60)
        $landlinePatterns = [
            '/^[2-9][0-9]{7,8}$/',     // Landline: 2-9 followed by 7-8 digits
            '/^0[3-9][0-9]{7,8}$/'     // Alternative landline with leading 0
        ];

        // Check mobile patterns
        foreach ($mobilePatterns as $pattern) {
            if (preg_match($pattern, $number)) {
                return true;
            }
        }

        // Check landline patterns if allowed
        if ($this->allowLandline) {
            foreach ($landlinePatterns as $pattern) {
                if (preg_match($pattern, $number)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Static method to normalize phone number for database storage
     */
    public static function normalize($phone)
    {
        if (empty($phone)) {
            return $phone;
        }

        // Remove all non-digit and non-plus characters
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // Handle different input formats
        if (str_starts_with($cleaned, '+60')) {
            return $cleaned; // Already in correct format
        } elseif (str_starts_with($cleaned, '60')) {
            return '+' . $cleaned;
        } elseif (str_starts_with($cleaned, '0')) {
            return '+6' . $cleaned; // 012345678 -> +6012345678
        } elseif (preg_match('/^[1-9]/', $cleaned) && strlen($cleaned) >= 8) {
            return '+60' . $cleaned; // 12345678 -> +6012345678
        }
        
        return $phone; // Return original if can't normalize
    }

    /**
     * Static method to format phone number for display
     */
    public static function format($phone)
    {
        if (empty($phone)) {
            return $phone;
        }

        // Ensure it's normalized first
        $normalized = self::normalize($phone);
        
        if (!str_starts_with($normalized, '+60')) {
            return $phone; // Return original if not Malaysian
        }

        // Remove +60 prefix for formatting
        $number = str_replace('+60', '', $normalized);
        
        // Format based on number length and pattern
        if (strlen($number) === 10 && str_starts_with($number, '1')) {
            // Mobile: +60 12-345 6789
            return '+60 ' . substr($number, 0, 2) . '-' . substr($number, 2, 3) . ' ' . substr($number, 5);
        } elseif (strlen($number) === 9 && str_starts_with($number, '1')) {
            // Mobile: +60 12-345 678
            return '+60 ' . substr($number, 0, 2) . '-' . substr($number, 2, 3) . ' ' . substr($number, 5);
        } elseif (strlen($number) >= 8) {
            // Landline: +60 3-1234 5678
            return '+60 ' . substr($number, 0, 1) . '-' . substr($number, 1, 4) . ' ' . substr($number, 5);
        }
        
        return $normalized; // Return normalized if can't format
    }
}