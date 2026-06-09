<?php

namespace App\Helpers;

class TimeHelper
{
    /**
     * Parse a time string like "1:16.389" into seconds as a float.
     * Or return the float if it's already a numeric string.
     */
    public static function timeToSeconds(?string $timeStr): ?float
    {
        if (empty($timeStr)) {
            return null;
        }

        if (str_contains($timeStr, ':')) {
            $parts = explode(':', $timeStr);
            // $parts[0] = minutes, $parts[1] = seconds
            return ($parts[0] * 60) + (float)$parts[1];
        }

        return (float)$timeStr;
    }
}
