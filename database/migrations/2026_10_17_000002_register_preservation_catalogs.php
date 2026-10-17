<?php

use Database\Schema\PreservationManifest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../schema/PreservationManifest.php';

return new class extends Migration
{
    public function up(): void
    {
        $manifest = PreservationManifest::load();
        DB::transaction(function () use ($manifest) {
            foreach (PreservationManifest::CITIES as $id => $city) {
                DB::table('cities')->insert(['id' => $id] + $city);
                DB::table('sources')->insert(['id' => $id, 'city_id' => $id, 'code' => $city['code']]);
            }
            foreach (PreservationManifest::tableIds($manifest) as $name => $id) {
                DB::table('source_tables')->insert(['id' => $id, 'name' => $name]);
            }
        });
    }

    public function down(): void
    {
        // RESTRICT references stop rollback if imported data still uses a catalog.
        // The following support-table rollback removes these static catalogs.
        DB::transaction(function () {
            DB::table('source_tables')->whereIn('id', array_values(PreservationManifest::tableIds(PreservationManifest::load())))->delete();
            DB::table('sources')->whereIn('id', array_keys(PreservationManifest::CITIES))->delete();
            DB::table('cities')->whereIn('id', array_keys(PreservationManifest::CITIES))->delete();
        });
    }
};
