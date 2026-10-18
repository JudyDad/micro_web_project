<?php

declare(strict_types=1);

namespace App\Services\Reconciliation;

use RuntimeException;

/** Independent wire decoder. Deliberately does not call the import key encoder. */
final class SourceKeyInspector
{
    public function ordinal(string $bytes, string $sqlHash, string $table): int
    {
        if (substr($bytes, 0, 4) !== "MPO\x01" || substr($bytes, 4, 32) !== hex2bin($sqlHash)) {
            throw new RuntimeException('Invalid ordinal identity header.');
        }
        $offset = 36;
        $name = $this->field($bytes, $offset);
        $ordinal = $this->field($bytes, $offset);
        $maximum = (string) PHP_INT_MAX;
        if ($name !== $table || ! preg_match('/^[1-9][0-9]*$/D', $ordinal)
            || strlen($ordinal) > strlen($maximum) || (strlen($ordinal) === strlen($maximum) && strcmp($ordinal, $maximum) > 0)
            || $offset !== strlen($bytes)) {
            throw new RuntimeException('Invalid ordinal identity fields.');
        }

        return (int) $ordinal;
    }

    public function primaryMatches(string $bytes, array $columns, array $values): bool
    {
        try {
            if (substr($bytes, 0, 4) !== "MPK\x01" || strlen($bytes) < 8 || unpack('Ncount', substr($bytes, 4, 4))['count'] !== count($columns)) {
                return false;
            }
            $offset = 8;
            foreach ($columns as $name => $definition) {
                if ($this->field($bytes, $offset) !== $name || $this->field($bytes, $offset) !== $definition || $offset >= strlen($bytes)) {
                    return false;
                }
                $tag = ord($bytes[$offset++]);
                if ($tag !== ($values[$name] === null ? 0 : 1)) {
                    return false;
                }
                if ($tag === 1 && $this->field($bytes, $offset) !== $values[$name]) {
                    return false;
                }
            }

            return $offset === strlen($bytes);
        } catch (RuntimeException) {
            return false;
        }
    }

    private function field(string $bytes, int &$offset): string
    {
        if ($offset + 4 > strlen($bytes)) {
            throw new RuntimeException('Truncated identity length.');
        }
        $length = unpack('Nlength', substr($bytes, $offset, 4))['length'];
        $offset += 4;
        if ($length > strlen($bytes) - $offset) {
            throw new RuntimeException('Truncated identity payload.');
        }
        $value = substr($bytes, $offset, $length);
        $offset += $length;

        return $value;
    }
}
