<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class SourceCatalog
{
    private const PREFIXES = [1 => 'a', 2 => 'd', 3 => 'h', 4 => 'l', 5 => 'w'];

    public function sourceIds(): array
    {
        return array_keys(self::PREFIXES);
    }

    public function table(int $sourceId, string $name): string
    {
        if (! isset(self::PREFIXES[$sourceId]) || ! in_array($name, ['user', 'microproject'], true)) {
            throw new InvalidArgumentException('Unknown source or table.');
        }

        return 'legacy_'.self::PREFIXES[$sourceId].'_'.$name;
    }

    public function tableId(string $name): int
    {
        if (! in_array($name, ['user', 'microproject'], true)) {
            throw new InvalidArgumentException('Unknown table.');
        }
        $id = DB::table('source_tables')->where('name', $name)->value('id');
        if ($id === null || ($name === 'user' && (int) $id !== 117)) {
            throw new RuntimeException('Preservation catalog mismatch.');
        }

        return (int) $id;
    }
}
