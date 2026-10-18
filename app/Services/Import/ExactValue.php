<?php

declare(strict_types=1);

namespace App\Services\Import;

use RuntimeException;

/** Type-aware roundtrip guard; independent reconciliation remains mandatory. */
final class ExactValue
{
    public static function equal(mixed $stored, ?string $expected, string $definition): bool
    {
        if ($stored === null || $expected === null) {
            return $stored === null && $expected === null;
        }
        if (is_resource($stored)) {
            $stored = stream_get_contents($stored);
        }
        if (preg_match('/^(float|double)\b/i', $definition, $match)) {
            // PHP's native binary-protocol value preserves IEEE width. Source
            // supplies a roundtrip decimal, never MySQL display formatting.
            $format = strtolower($match[1]) === 'float' ? 'g' : 'e';

            return pack($format, (float) $stored) === pack($format, (float) $expected);
        }
        if (preg_match('/^(?:tinyint|smallint|mediumint|int|bigint)\b/i', $definition)) {
            return self::integer((string) $stored) === self::integer($expected);
        }

        return (string) $stored === $expected;
    }

    private static function integer(string $value): string
    {
        if (! preg_match('/\A[+-]?[0-9]+\z/', $value)) {
            throw new RuntimeException('Invalid exact integer representation.');
        }
        $negative = str_starts_with($value, '-');
        $digits = ltrim(ltrim($value, '+-'), '0');

        return ($negative && $digits !== '' ? '-' : '').($digits === '' ? '0' : $digits);
    }
}
