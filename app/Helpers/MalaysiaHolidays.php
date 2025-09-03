<?php

namespace App\Helpers;

use Carbon\Carbon;

class MalaysiaHolidays
{
    /**
     * Get Malaysia public holidays for a given year
     * This includes major fixed and variable holidays
     */
    public static function getHolidays($year = null)
    {
        $year = $year ?? date('Y');
        $holidays = [];

        // Fixed holidays
        $holidays[] = Carbon::create($year, 1, 1);   // New Year's Day
        $holidays[] = Carbon::create($year, 2, 1);   // Federal Territory Day (KL, Putrajaya, Labuan)
        $holidays[] = Carbon::create($year, 5, 1);   // Labour Day
        $holidays[] = Carbon::create($year, 6, 3);   // Birthday of Yang di-Pertuan Agong
        $holidays[] = Carbon::create($year, 8, 31);  // National Day
        $holidays[] = Carbon::create($year, 9, 16);  // Malaysia Day
        $holidays[] = Carbon::create($year, 12, 25); // Christmas Day

        // Variable holidays (approximate dates - should be updated annually)
        // These dates vary each year based on lunar calendar
        $variableHolidays = self::getVariableHolidays($year);
        $holidays = array_merge($holidays, $variableHolidays);

        return collect($holidays)->map(function ($date) {
            return $date->format('Y-m-d');
        })->toArray();
    }

    /**
     * Get variable holidays that change each year
     * These are approximate and should be updated with official dates
     */
    private static function getVariableHolidays($year)
    {
        $holidays = [];

        // Chinese New Year (2 days) - varies each year
        if ($year == 2025) {
            $holidays[] = Carbon::create(2025, 1, 29);  // Chinese New Year Day 1
            $holidays[] = Carbon::create(2025, 1, 30);  // Chinese New Year Day 2
        } elseif ($year == 2024) {
            $holidays[] = Carbon::create(2024, 2, 10);
            $holidays[] = Carbon::create(2024, 2, 11);
        }

        // Hari Raya Puasa (Eid al-Fitr) - varies each year
        if ($year == 2025) {
            $holidays[] = Carbon::create(2025, 3, 31);  // Estimated
            $holidays[] = Carbon::create(2025, 4, 1);   // Day 2
        } elseif ($year == 2024) {
            $holidays[] = Carbon::create(2024, 4, 10);
            $holidays[] = Carbon::create(2024, 4, 11);
        }

        // Hari Raya Haji (Eid al-Adha) - varies each year
        if ($year == 2025) {
            $holidays[] = Carbon::create(2025, 6, 7);   // Estimated
        } elseif ($year == 2024) {
            $holidays[] = Carbon::create(2024, 6, 17);
        }

        // Islamic New Year - varies each year
        if ($year == 2025) {
            $holidays[] = Carbon::create(2025, 6, 27);  // Estimated
        } elseif ($year == 2024) {
            $holidays[] = Carbon::create(2024, 7, 7);
        }

        // Prophet Muhammad's Birthday - varies each year
        if ($year == 2025) {
            $holidays[] = Carbon::create(2025, 9, 5);   // Estimated
        } elseif ($year == 2024) {
            $holidays[] = Carbon::create(2024, 9, 15);
        }

        // Deepavali - varies each year
        if ($year == 2025) {
            $holidays[] = Carbon::create(2025, 10, 20); // Estimated
        } elseif ($year == 2024) {
            $holidays[] = Carbon::create(2024, 10, 31);
        }

        return $holidays;
    }

    /**
     * Check if a given date is a public holiday
     */
    public static function isPublicHoliday($date)
    {
        $date = Carbon::parse($date);
        $holidays = self::getHolidays($date->year);
        
        return in_array($date->format('Y-m-d'), $holidays);
    }

    /**
     * Check if a given date is a weekend (Saturday or Sunday)
     */
    public static function isWeekend($date)
    {
        $date = Carbon::parse($date);
        return $date->dayOfWeek === 0 || $date->dayOfWeek === 6; // Sunday = 0, Saturday = 6
    }

    /**
     * Check if a given date is a non-working day (weekend or public holiday)
     */
    public static function isNonWorkingDay($date)
    {
        return self::isWeekend($date) || self::isPublicHoliday($date);
    }

    /**
     * Get the reason why a date is non-working
     */
    public static function getNonWorkingDayReason($date)
    {
        $date = Carbon::parse($date);
        
        if (self::isWeekend($date)) {
            return 'Weekend - Centre is closed on weekends';
        }
        
        if (self::isPublicHoliday($date)) {
            return 'Malaysia Public Holiday - Centre is closed';
        }
        
        return null;
    }
}