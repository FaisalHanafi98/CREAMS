<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Carbon\Carbon;
use App\Models\PublicHoliday;

class NoWeekendOrHolidayRule implements ValidationRule
{
    protected $centreId;
    protected $centreState;

    public function __construct($centreId = null)
    {
        $this->centreId = $centreId;

        // Get centre's state for holiday checking
        if ($centreId) {
            $centre = \DB::table('centres')->where('centre_id', $centreId)->first();
            $this->centreState = $centre->state ?? null;
        }
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $date = Carbon::parse($value);

            // Check if it's a weekend (Saturday or Sunday)
            if ($date->isWeekend()) {
                $fail('Sessions cannot be scheduled on weekends (Saturday or Sunday).');
                return;
            }

            // Check if it's a public holiday
            $isHoliday = PublicHoliday::where('date', $date->format('Y-m-d'))
                ->where('is_active', true)
                ->where(function($query) {
                    // Include federal holidays
                    $query->whereNull('state');
                    // Include state-specific holidays if centre state is known
                    if ($this->centreState) {
                        $query->orWhere('state', $this->centreState);
                    }
                })
                ->exists();

            if ($isHoliday) {
                $holiday = PublicHoliday::where('date', $date->format('Y-m-d'))
                    ->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('state');
                        if ($this->centreState) {
                            $query->orWhere('state', $this->centreState);
                        }
                    })
                    ->first();

                $fail('Sessions cannot be scheduled on public holidays. ' . $date->format('F j, Y') . ' is ' . $holiday->name . '.');
                return;
            }

        } catch (\Exception $e) {
            $fail('Invalid date format.');
        }
    }
}
