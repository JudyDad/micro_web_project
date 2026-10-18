<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AttachmentReconciliation\ReferenceInventory;
use App\Services\Import\Readers\FrozenJsonSourceReader;
use Database\Schema\PreservationManifest;
use Illuminate\Console\Command;
use Throwable;

final class InventoryAttachmentReferences extends Command
{
    protected $signature = 'phase3:inventory-attachments {output : New private evidence directory} {manifests* : Frozen source manifests for all five cities}';

    protected $description = 'Inventory attachment metadata and hashes into a private, non-public evidence directory';

    public function handle(ReferenceInventory $inventory): int
    {
        try {
            $readers = [];
            foreach ($this->argument('manifests') as $path) {
                $reader = new FrozenJsonSourceReader($path);
                $city = $reader->contract()['city'];
                if (isset($readers[$city])) {
                    throw new \RuntimeException('Duplicate frozen source city.');
                }
                $readers[$city] = $reader;
            }
            $expected = array_column(PreservationManifest::CITIES, 'name_en');
            sort($expected, SORT_STRING);
            $actual = array_keys($readers);
            sort($actual, SORT_STRING);
            if ($actual !== $expected) {
                throw new \RuntimeException('Attachment inventory requires one frozen source for every approved city.');
            }
            $result = $inventory->write($readers, (string) $this->argument('output'));
            $this->line(json_encode($result, JSON_THROW_ON_ERROR));
            $this->info('Private attachment metadata inventory written; no file bytes were published or served.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Attachment inventory stopped; no public attachment catalog was enabled. Inspect private frozen inputs and evidence directory.');

            return self::FAILURE;
        }
    }
}
