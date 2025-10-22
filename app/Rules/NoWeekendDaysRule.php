<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoWeekendDaysRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Value is an array of selected days
        if (is_array($value)) {
            $weekendDays = array_intersect($value, ['Saturday', 'Sunday']);

            if (!empty($weekendDays)) {
                $fail('Activities cannot be scheduled on weekends. Please remove ' . implode(' and ', $weekendDays) . ' from the schedule.');
            }
        }
    }
}
