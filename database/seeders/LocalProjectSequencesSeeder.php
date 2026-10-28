<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Local test databases only: gives every registered source a project-number sequence starting at 1 when it has none,
 * so applications can be created on an empty database. It never touches an existing sequence. At cutover the
 * sequences are set from the reconciled source maximums instead (importer floors), never from this seeder.
 */
final class LocalProjectSequencesSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Local project sequences are never seeded in production.');
        }
        foreach (DB::table('sources')->orderBy('id')->pluck('id') as $source) {
            if (! DB::table('project_number_sequences')->where('source_id', $source)->exists()) {
                DB::table('project_number_sequences')->insert(['source_id' => $source, 'next_number' => 1]);
                $this->command?->info('source '.$source.': sequence initialized at 1');
            }
        }
    }
}
