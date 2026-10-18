<?php

declare(strict_types=1);

namespace App\Services\Reconciliation;

use RuntimeException;

/** Independent source-only lookup. No target values, importer codec or lossy numeric casts. */
final class IntegerReferenceIndex
{
    private array $entries = [];

    public function __construct(iterable $rows, array $referenceColumns, array $primaryColumns)
    {
        foreach ($referenceColumns as $definition) {
            if (! preg_match('/^(?:tinyint|smallint|mediumint|int|integer|bigint)\b/i', $definition)) {
                throw new RuntimeException('Independent relationship indexing requires reviewed integer comparison semantics.');
            }
        }
        foreach ($rows as $row) {
            $tuple = [];
            foreach ($referenceColumns as $column => $definition) {
                $tuple[] = $row['values'][$column];
            }
            if (in_array(null, $tuple, true)) {
                continue;
            }
            $key = $this->tuple($tuple);
            if (isset($this->entries[$key])) {
                $this->entries[$key]['count']++;
            } else {
                $primary = [];
                foreach ($primaryColumns as $column) {
                    $primary[$column] = $row['values'][$column];
                }
                $this->entries[$key] = ['count' => 1, 'primary' => $primary, 'ordinal' => $row['ordinal']];
            }
        }
    }

    public function find(array $tuple): ?array
    {
        if (in_array(null, $tuple, true)) {
            return null;
        }

        return $this->entries[$this->tuple($tuple)] ?? null;
    }

    private function tuple(array $values): string
    {
        $canonical = [];
        foreach ($values as $value) {
            if (! is_string($value) || ! preg_match('/^([+-]?)([0-9]+)$/D', $value, $match)) {
                throw new RuntimeException('Invalid exact integer relationship tuple.');
            }
            $digits = ltrim($match[2], '0');
            $canonical[] = ($match[1] === '-' && $digits !== '' ? '-' : '').($digits === '' ? '0' : $digits);
        }

        return json_encode($canonical, JSON_THROW_ON_ERROR);
    }
}
