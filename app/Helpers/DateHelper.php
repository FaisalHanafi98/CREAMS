<?php

if (!function_exists('malaysianDate')) {
    /**
     * Format date to Malaysian date format
     *
     * @param string|null $date
     * @return string
     */
    function malaysianDate($date = null) {
        $carbon = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        return $carbon->format('d/m/Y');
    }
}

if (!function_exists('malaysianDateTime')) {
    /**
     * Format date to Malaysian datetime format
     *
     * @param string|null $date
     * @return string
     */
    function malaysianDateTime($date = null) {
        $carbon = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        return $carbon->format('d/m/Y H:i:s');
    }
}

if (!function_exists('malaysianTime')) {
    /**
     * Format date to Malaysian time format
     *
     * @param string|null $date
     * @return string
     */
    function malaysianTime($date = null) {
        $carbon = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        return $carbon->format('H:i:s');
    }
}