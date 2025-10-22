<?php

namespace App\Shared\Boundary\Format;

use DateTimeImmutable;
use Throwable;

final class DateFormatter
{
    /**
     * Format a date string using an English format.
     *
     * @param string|null $value  Raw date/time string from storage.
     * @param string      $format PHP date format string (default Month day, Year).
     */
    public static function date(?string $value, string $format = 'M j, Y'): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        try {
            $dateTime = new DateTimeImmutable($trimmed);
        } catch (Throwable) {
            return $trimmed;
        }

        return $dateTime->format($format);
    }

    /**
     * Format a date and time string using an English format.
     */
    public static function dateTime(?string $value, string $format = 'M j, Y H:i'): ?string
    {
        return self::date($value, $format);
    }
}
