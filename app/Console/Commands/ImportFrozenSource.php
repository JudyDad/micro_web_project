<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Import\DeterministicImporter;
use App\Services\Import\Readers\FrozenJsonSourceReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ImportFrozenSource extends Command
{
    protected $signature = 'phase3:import {manifest : Private frozen source manifest path} {--batch-size=250}';

    protected $description = 'Stage an isolated frozen source into preservation and shared tables without publication';

    public function handle(): int
    {
        $config = DB::connection()->getConfig();
        if (! app()->environment(['local', 'testing']) || ($config['driver'] ?? null) !== 'mysql'
            || ! in_array($config['database'] ?? null, ['micro_projects_local', 'micro_projects_test'], true)
            || ! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! empty($config['url']) || ! empty($config['unix_socket'])) {
            $this->error('Import requires a dedicated loopback local/testing target database.');

            return self::FAILURE;
        }
        try {
            $size = filter_var($this->option('batch-size'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);
            if ($size === false) {
                $this->error('Batch size must be from 1 to 1000.');

                return self::FAILURE;
            }
            $reader = new FrozenJsonSourceReader((string) $this->argument('manifest'));
            $result = (new DeterministicImporter(DB::connection()))->import($reader, $size);
            $this->line(json_encode($result, JSON_THROW_ON_ERROR));
            $this->info('Staged only. Independent reconciliation and authorized publication remain required.');

            return self::SUCCESS;
        } catch (Throwable $error) {
            // Database exceptions may contain SQL bindings/private row values.
            $this->error('Import stopped ('.$this->safeDiagnostic($error).'); durable checkpoints are retained. Inspect the private source and target before retry.');

            return self::FAILURE;
        }
    }

    private function safeDiagnostic(Throwable $error): string
    {
        for ($cause = $error; $cause !== null; $cause = $cause->getPrevious()) {
            if ($cause instanceof \PDOException && is_array($cause->errorInfo ?? null)) {
                $state = $cause->errorInfo[0] ?? null;
                $driverCode = $cause->errorInfo[1] ?? null;
                if (is_string($state) && preg_match('/\A[A-Z0-9]{5}\z/D', $state) === 1
                    && (is_int($driverCode) || (is_string($driverCode) && preg_match('/\A[0-9]{1,6}\z/D', $driverCode) === 1))) {
                    return 'database error '.$state.'/'.$driverCode;
                }
            }
        }

        return 'non-database failure';
    }
}
