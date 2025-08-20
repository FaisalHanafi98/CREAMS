<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MinimumEnrollmentRule implements ValidationRule
{
    protected $minimumTrainees;

    public function __construct($minimumTrainees = 1)
    {
        $this->minimumTrainees = $minimumTrainees;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If participants field is empty or null, fail validation
        if (empty($value)) {
            $fail("At least {$this->minimumTrainees} trainee must be enrolled to create an activity.");
            return;
        }

        // Parse participant IDs (assuming comma-separated or array format)
        $participantIds = [];
        if (is_string($value)) {
            $participantIds = array_filter(explode(',', $value));
        } elseif (is_array($value)) {
            $participantIds = array_filter($value);
        }

        // Check if minimum enrollment requirement is met
        if (count($participantIds) < $this->minimumTrainees) {
            $fail("At least {$this->minimumTrainees} trainee(s) must be enrolled. Currently " . count($participantIds) . " selected.");
        }
    }
}
