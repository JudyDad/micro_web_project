<?php

declare(strict_types=1);

namespace App\Services\Finance;

use DateTimeImmutable;

/** Small input helpers shared by the finance services. */
final class Fields
{
    public static function isDate(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    /** The calendar day of a stored DATE/DATETIME value, or '' when empty. */
    public static function day(mixed $value): string
    {
        return $value === null ? '' : substr((string) $value, 0, 10);
    }

    public static function text(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /** Whether a submitted value is the form's empty slot. */
    public static function blank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }
}
