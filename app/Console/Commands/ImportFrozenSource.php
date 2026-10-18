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
        } catch (Throwable) {
            // Database exceptions may contain SQL bindings/private row values.
            $this->error('Import stopped; durable checkpoints are retained. Inspect the private source and target before retry.');

            return self::FAILURE;
        }
    }
}
