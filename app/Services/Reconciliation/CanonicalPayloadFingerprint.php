<?php

declare(strict_types=1);

namespace App\Services\Reconciliation;

use RuntimeException;

/** Independently implements the published MPS v1 evidence format; no conversion codec calls. */
final class CanonicalPayloadFingerprint
{
    public function digest(object $row, array $table): string
    {
        $columns = array_values(array_filter($table['columns'], fn (array $column): bool => str_starts_with($column['name'], 'c_')));
        $digest = hash_init('sha256');
        hash_update($digest, "MPS\x01".pack('N', count($columns)));
        foreach ($columns as $column) {
            foreach ([$column['name'], $column['definition']] as $field) {
                hash_update($digest, pack('N', strlen($field)).$field);
            }
            $value = $row->{$column['name']};
            if ($value === null) {
                hash_update($digest, "\x00");

                continue;
            }
            if (preg_match('/^(float|double)\b/i', $column['definition'], $type)) {
                if (! is_numeric($value)) {
                    throw new RuntimeException('Canonical floating evidence is invalid.');
                }
                $bytes = pack(strtolower($type[1]) === 'float' ? 'G' : 'E', (float) $value);
            } elseif (preg_match('/^(?:tinyint|smallint|mediumint|int|bigint)\b/i', $column['definition'])) {
                if (! preg_match('/^(-?)([0-9]+)$/D', (string) $value, $parts)) {
                    throw new RuntimeException('Canonical integer evidence is invalid.');
                }
                $digits = ltrim($parts[2], '0');
                $bytes = ($parts[1] === '-' && $digits !== '' ? '-' : '').($digits === '' ? '0' : $digits);
            } else {
                $bytes = (string) $value;
            }
            hash_update($digest, "\x01".pack('N', strlen($bytes)).$bytes);
        }

        return hash_final($digest, true);
    }
}
