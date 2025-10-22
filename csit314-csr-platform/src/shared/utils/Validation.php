<?php
declare(strict_types=1);

namespace shared\utils;

class Validation
{
    public static function sanitizeString(?string $value): string
    {
        return trim((string) $value);
    }

    public static function requireField(string $value, string $message): void
    {
        if ($value === '') {
            throw new \InvalidArgumentException($message);
        }
    }

    public static function email(string $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Please provide a valid email address.');
        }
    }

    public static function integerId($value): int
    {
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('A valid identifier is required.');
        }
        return (int) $value;
    }
}
