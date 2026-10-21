<?php

declare(strict_types=1);

namespace App\Services\Finance;

/**
 * Exact decimal arithmetic on strings (bcmath). The desktop used floating point for display only;
 * nothing here rounds unless a rule says so explicitly, and rounding is always half away from zero (.NET "N").
 */
final class Money
{
    /** Strips the grouping separators the desktop removed (`Replace(",", "")`) and maps Arabic-Indic digits. */
    public static function normalize(string $input): string
    {
        $input = strtr(trim($input), ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '٫' => '.', '٬' => ',']);

        return str_replace([',', ' '], '', $input);
    }

    public static function isDecimal(string $value, int $maxScale, bool $allowNegative = false): bool
    {
        $pattern = '/\A'.($allowNegative ? '-?' : '').'\d{1,15}(?:\.\d{1,'.max(1, $maxScale).'})?\z/';

        return preg_match($pattern, $value) === 1 && ($maxScale > 0 || ! str_contains($value, '.'));
    }

    /** Canonical form of a stored or entered number: no exponent, no trailing zeros, no "-0". */
    public static function canonical(string|int|float|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_float($value)) {
            $value = rtrim(rtrim(sprintf('%.6F', $value), '0'), '.');
        }
        $value = (string) $value;
        if (! preg_match('/\A-?\d+(?:\.\d+)?\z/', $value)) {
            return $value;
        }
        if (str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }
        $value = ltrim($value, '+');

        return $value === '' || $value === '-' || $value === '-0' ? '0' : $value;
    }

    public static function round(string $value, int $scale): string
    {
        $half = '0.'.str_repeat('0', $scale).'5';
        $rounded = str_starts_with($value, '-') ? bcsub($value, $half, $scale) : bcadd($value, $half, $scale);

        return bccomp($rounded, '0', $scale) === 0 ? bcadd('0', '0', $scale) : $rounded;
    }

    public static function compare(string $a, string $b, int $scale = 8): int
    {
        return bccomp($a, $b, $scale);
    }

    public static function isZero(?string $value): bool
    {
        return $value === null || bccomp($value, '0', 8) === 0;
    }

    public static function sum(iterable $values): string
    {
        $total = '0';
        foreach ($values as $value) {
            $total = bcadd($total, $value ?? '0', 8);
        }

        return self::canonical($total) ?? '0';
    }

    /** Whether the decimal survives a round trip through single precision (a MySQL FLOAT column). */
    public static function survivesFloat(string $value): bool
    {
        $stored = unpack('f', pack('f', (float) $value))[1];

        return self::compare(self::round(sprintf('%.8F', $stored), 4), self::round($value, 4), 4) === 0;
    }

    /** Whole currency units exactly representable in the FLOAT(24,0) payment column. */
    public static function isWholeFloatAmount(string $value): bool
    {
        return preg_match('/\A-?\d+\z/', $value) === 1 && bccomp(ltrim($value, '-'), '16777216', 0) <= 0;
    }
}
