<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Local test seeding only: the per-role test accounts. Business rows come from the importer, never from seeders. */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAccountsSeeder::class);
        $this->call(LocalProjectSequencesSeeder::class);
    }
}
