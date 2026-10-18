<?php

declare(strict_types=1);

namespace App\Services\Sharing;

use RuntimeException;

/** Canonical typed storage bytes; deliberately distinct from dump-token audit hashes. */
final class SharedRowCodec
{
    public static function encode(array $values, array $columns): string
    {
        $result = "MPS\x01".pack('N', count($columns));
        foreach ($columns as $column) {
            $name = $column['name'];
            if (! array_key_exists($name, $values)) {
                throw new RuntimeException('Canonical payload column is missing.');
            }
            $result .= self::field($name).self::field($column['definition']);
            $value = $values[$name];
            if ($value === null) {
                $result .= "\x00";

                continue;
            }
            if (is_resource($value)) {
                if (! (stream_get_meta_data($value)['seekable'] ?? false) || ! rewind($value)) {
                    throw new RuntimeException('Canonical binary payload must support repeatable exact reads.');
                }
                $value = stream_get_contents($value);
            }
            if (preg_match('/^(float|double)\b/i', $column['definition'], $match)) {
                if (! is_numeric($value)) {
                    throw new RuntimeException('Canonical floating payload is invalid.');
                }
                $value = pack(strtolower($match[1]) === 'float' ? 'G' : 'E', (float) $value);
            } elseif (preg_match('/^(?:tinyint|smallint|mediumint|int|bigint)\b/i', $column['definition'])) {
                $value = (string) $value;
                if (! preg_match('/\A-?[0-9]+\z/', $value)) {
                    throw new RuntimeException('Canonical integer payload is invalid.');
                }
                $negative = str_starts_with($value, '-');
                $value = ltrim(ltrim($value, '-'), '0');
                $value = ($negative && $value !== '' ? '-' : '').($value === '' ? '0' : $value);
            } elseif (! is_string($value) && ! is_int($value)) {
                throw new RuntimeException('Canonical payload has an unsupported scalar type.');
            }
            $result .= "\x01".self::field((string) $value);
        }

        return $result;
    }

    public static function columns(array $table): array
    {
        return array_values(array_filter($table['columns'], fn ($c) => str_starts_with($c['name'], 'c_')));
    }

    private static function field(string $value): string
    {
        return pack('N', strlen($value)).$value;
    }
}
