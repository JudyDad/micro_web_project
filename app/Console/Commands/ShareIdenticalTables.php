<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Import\Readers\FrozenJsonSourceReader;
use App\Services\ImportTargetGuard;
use App\Services\Reconciliation\IndependentReconciler;
use App\Services\Sharing\SharedCatalogConverter;
use App\Services\SharingEvidence\ArchiveCatalogProof;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class ShareIdenticalTables extends Command
{
    protected $signature = 'phase3:share-identical {configuration : Private JSON with manifests, archives and decision/evidence locators}';

    protected $description = 'Verify and atomically share only approved complete identical definition tables';

    public function handle(IndependentReconciler $reconciler): int
    {
        try {
            ImportTargetGuard::check();
            if (! extension_loaded('zip')) {
                $this->error('Original archive verification requires the PHP zip extension.');

                return self::FAILURE;
            }
            $path = realpath((string) $this->argument('configuration'));
            if ($path === false || ! is_file($path)) {
                throw new RuntimeException('Private sharing configuration is unavailable.');
            }
            $configuration = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($configuration['manifests'] ?? null) || ! is_array($configuration['archives'] ?? null)) {
                throw new RuntimeException('Private sharing configuration requires source manifests and immutable archive paths.');
            }
            $readers = [];
            foreach ($configuration['manifests'] as $city => $manifest) {
                if (! is_string($city) || ! is_string($manifest)) {
                    throw new RuntimeException('Invalid private sharing source descriptor.');
                }
                $readers[$city] = new FrozenJsonSourceReader($manifest);
            }
            $converter = new SharedCatalogConverter(DB::connection(), new ArchiveCatalogProof($configuration['archives']), $reconciler);
            $result = $converter->convert($readers, (string) ($configuration['decision_locator'] ?? ''), (string) ($configuration['evidence_locator'] ?? ''));
            $this->line(json_encode($result, JSON_THROW_ON_ERROR));
            $this->info('Canonical definitions published behind existing source-scoped aliases. No account grants or source publications changed.');

            return self::SUCCESS;
        } catch (Throwable) {
            // Private archive/driver exceptions can contain paths, SQL or data.
            $this->error('Whole-table sharing stopped. Existing committed groups remain intact; inspect private evidence before retry.');

            return self::FAILURE;
        }
    }
}
