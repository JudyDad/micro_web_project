<?php

declare(strict_types=1);

namespace App\Services\Sharing;

/** Independent immutable archive evidence, not an assertion from converter code. */
interface CatalogSourceProof
{
    /**
     * verified, full_table_sha256, schema_contract_sha256, sources[city] containing
     * archive/sql/frozen_manifest SHA-256 and the exact snapshot_id.
     */
    public function verify(array $group, array $readersByCity): array;
}
