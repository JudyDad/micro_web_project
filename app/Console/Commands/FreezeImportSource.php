<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Import\Readers\NativePdoSourceExporter;
use Illuminate\Console\Command;
use PDO;
use RuntimeException;
use Throwable;

final class FreezeImportSource extends Command
{
    protected $signature = 'phase3:freeze-source {configuration : Private local JSON configuration path} {output : New private extraction directory}';

    protected $description = 'Freeze an isolated loopback MySQL source as hashed exact-byte streams';

    public function handle(NativePdoSourceExporter $exporter): int
    {
        try {
            if (! app()->environment(['local', 'testing'])) {
                throw new RuntimeException('Source extraction is restricted to local/testing.');
            }
            $configuration = json_decode(file_get_contents($this->argument('configuration')), true, flags: JSON_THROW_ON_ERROR);
            $connection = $configuration['connection'] ?? [];
            if (! in_array($connection['host'] ?? '', ['127.0.0.1', 'localhost', '::1'], true)
                || ! preg_match('/\Amicro_projects_source_[a-z0-9_]+\z/D', $connection['database'] ?? '')
                || ! is_int($connection['port'] ?? null) || $connection['port'] < 1 || $connection['port'] > 65535) {
                throw new RuntimeException('Use a loopback micro_projects_source_* connection.');
            }
            $source = new PDO(
                'mysql:host='.$connection['host'].';port='.$connection['port'].';dbname='.$connection['database'].';charset=utf8mb4',
                $connection['username'] ?? '', $connection['password'] ?? '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false],
            );
            $exporter->export($source, $configuration['identity'] ?? [], $this->argument('output'));
            $this->info('Frozen source manifest and table hashes verified. No source rows were changed.');

            return self::SUCCESS;
        } catch (Throwable) {
            // Driver exceptions can include private rows or credentials; never forward them to CLI logs.
            $this->error('Source extraction failed. Verify private configuration, isolation, hashes and incomplete output.');

            return self::FAILURE;
        }
    }
}
