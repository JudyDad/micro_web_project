<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Import\Readers\FrozenJsonSourceReader;
use App\Services\ImportTargetGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class RegisterImportSnapshot extends Command
{
    protected $signature = 'phase3:register-snapshot {manifest : Private verified frozen manifest path}';

    protected $description = 'Register an immutable isolated snapshot without publication or account activation';

    public function handle(): int
    {
        try {
            ImportTargetGuard::check();
            $path = realpath((string) $this->argument('manifest'));
            $reader = new FrozenJsonSourceReader((string) $this->argument('manifest'));
            $contract = $reader->contract();
            if ($path === false || strlen($path) > 1024) {
                throw new RuntimeException('Invalid manifest locator.');
            }
            DB::transaction(static function () use ($contract, $path): void {
                $source = DB::table('sources')->where('id', $contract['source_id'])->lockForUpdate()->first();
                $city = $source ? DB::table('cities')->where('id', $source->city_id)->first() : null;
                if (! $city || $city->name_en !== $contract['city']) {
                    throw new RuntimeException('Snapshot source identity differs.');
                }
                $existing = DB::table('snapshots')->where('id', $contract['snapshot_id'])->lockForUpdate()->first();
                if ($existing) {
                    if ((int) $existing->source_id !== $contract['source_id']
                        || bin2hex($existing->archive_sha256) !== $contract['archive_sha256']
                        || bin2hex($existing->sql_sha256) !== $contract['sql_sha256']) {
                        throw new RuntimeException('Snapshot identity is already registered for different evidence.');
                    }

                    return;
                }
                DB::table('snapshots')->insert(['id' => $contract['snapshot_id'], 'source_id' => $contract['source_id'],
                    'archive_sha256' => hex2bin($contract['archive_sha256']), 'sql_sha256' => hex2bin($contract['sql_sha256']),
                    'manifest_locator' => $path, 'captured_at' => null]);
            });
            $this->info('Immutable snapshot registered; no rows imported, accounts activated or data published.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Snapshot registration refused. Check private evidence and existing snapshot identities.');

            return self::FAILURE;
        }
    }
}
