<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ImportTargetGuard
{
    public static function check(): void
    {
        $config = DB::connection()->getConfig();
        if (! app()->environment(['local', 'testing']) || ($config['driver'] ?? null) !== 'mysql'
            || ! in_array($config['database'] ?? null, ['micro_projects_local', 'micro_projects_test'], true)
            || ! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! empty($config['url']) || ! empty($config['unix_socket'])) {
            throw new RuntimeException('Use a dedicated loopback local/testing import target.');
        }
        $server = DB::selectOne('SELECT DATABASE() AS name, VERSION() AS version');
        if ($server->name !== $config['database'] || ! str_starts_with($server->version, '8.') || str_contains($server->version, 'MariaDB')) {
            throw new RuntimeException('Import target must be the configured MySQL 8 database.');
        }
    }
}
