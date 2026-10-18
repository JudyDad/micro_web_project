<?php

declare(strict_types=1);

namespace App\Services\Import;

use InvalidArgumentException;

final class KeyEncoder
{
    public const VERSION = 'length-prefix-v1';

    /** @param array<string,string> $columns Original PK columns in declared order. */
    public static function primary(array $columns, array $values): string
    {
        if ($columns === []) {
            throw new InvalidArgumentException('Primary key components are required.');
        }
        $key = "MPK\x01".pack('N', count($columns));
        foreach ($columns as $name => $definition) {
            if (! array_key_exists($name, $values) || (! is_string($values[$name]) && $values[$name] !== null)) {
                throw new InvalidArgumentException('Key values must be exact strings or NULL.');
            }
            $key .= self::field($name).self::field($definition);
            $key .= $values[$name] === null ? "\x00" : "\x01".self::field($values[$name]);
        }

        return $key;
    }

    public static function ordinal(string $sqlHashHex, string $table, int $ordinal): string
    {
        if (! preg_match('/\A[0-9a-f]{64}\z/', $sqlHashHex) || $ordinal < 1) {
            throw new InvalidArgumentException('Invalid immutable ordinal identity.');
        }

        return "MPO\x01".hex2bin($sqlHashHex).self::field($table).self::field((string) $ordinal);
    }

    private static function field(string $value): string
    {
        if (strlen($value) > 0xFFFFFFFF) {
            throw new InvalidArgumentException('Key field exceeds encoding range.');
        }

        return pack('N', strlen($value)).$value;
    }
}
