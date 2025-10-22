<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PublicHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'name',
        'name_ms',
        'type',
        'state',
        'description',
        'year',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Check if a given date is a public holiday
     */
    public static function isHoliday($date, $state = null)
    {
        $query = self::where('date', Carbon::parse($date)->format('Y-m-d'))
            ->where('is_active', true);

        if ($state) {
            $query->where(function($q) use ($state) {
                $q->whereNull('state')
                  ->orWhere('state', $state);
            });
        } else {
            $query->whereNull('state'); // Only federal holidays
        }

        return $query->exists();
    }

    /**
     * Get holiday for a specific date
     */
    public static function getHoliday($date, $state = null)
    {
        $query = self::where('date', Carbon::parse($date)->format('Y-m-d'))
            ->where('is_active', true);

        if ($state) {
            $query->where(function($q) use ($state) {
                $q->whereNull('state')
                  ->orWhere('state', $state);
            });
        } else {
            $query->whereNull('state');
        }

        return $query->first();
    }

    /**
     * Get all holidays for a specific year
     */
    public static function getYearHolidays($year, $state = null)
    {
        $query = self::where('year', $year)
            ->where('is_active', true)
            ->orderBy('date');

        if ($state) {
            $query->where(function($q) use ($state) {
                $q->whereNull('state')
                  ->orWhere('state', $state);
            });
        } else {
            $query->whereNull('state');
        }

        return $query->get();
    }
}
