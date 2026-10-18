<?php

declare(strict_types=1);

namespace App\Services\Reconciliation;

/** Independent typed comparison; never consumes importer digests or transformations. */
final class CellComparator
{
    public function equal(?string $expected, mixed $actual, string $definition): bool
    {
        if ($expected === null || $actual === null) {
            return $expected === null && $actual === null;
        }
        if (preg_match('/^(?:tinyint|smallint|mediumint|int|integer|bigint|decimal|numeric)\b/i', $definition)) {
            $canonical = $this->decimal($expected);

            return $canonical !== null && $canonical === $this->decimal((string) $actual);
        }
        if (preg_match('/^(float|double|real)\b(?:\((\d+)(?:,\d+)?\))?/i', $definition, $match)) {
            $double = strtolower($match[1]) !== 'float' || (isset($match[2]) && ! str_contains($match[0], ',') && (int) $match[2] > 24);

            return is_numeric($expected) && is_numeric($actual)
                && pack($double ? 'E' : 'G', (float) $expected) === pack($double ? 'E' : 'G', (float) $actual);
        }

        return $expected === (string) $actual;
    }

    private function decimal(string $value): ?string
    {
        if (! preg_match('/^([+-]?)(\d+)(?:\.(\d+))?$/D', $value, $parts)) {
            return null;
        }
        $whole = ltrim($parts[2], '0');
        $fraction = rtrim($parts[3] ?? '', '0');
        $number = ($whole === '' ? '0' : $whole).($fraction === '' ? '' : '.'.$fraction);

        return ($parts[1] === '-' && $number !== '0' ? '-' : '').$number;
    }
}
