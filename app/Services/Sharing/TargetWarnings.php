<?php

declare(strict_types=1);

namespace App\Services\Sharing;

use Illuminate\Database\ConnectionInterface;
use PDO;
use RuntimeException;

final class TargetWarnings
{
    public static function assertNone(ConnectionInterface $db): void
    {
        // SHOW WARNINGS must use the text protocol. Preparing it natively can
        // replace the original diagnostics with MySQL warning 1295.
        $pdo = $db->getPdo();
        $emulation = $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES);
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $warnings = $pdo->query('SHOW WARNINGS')->fetchAll(PDO::FETCH_OBJ);
        } finally {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $emulation);
        }
        if ($warnings !== []) {
            $codes = array_map(static fn (object $warning): int => (int) $warning->Code, $warnings);
            throw new RuntimeException('Exact payload storage warned; transaction rolled back. Codes: '.implode(',', $codes));
        }
    }
}
