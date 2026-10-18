<?php

declare(strict_types=1);

namespace App\Services\Import;

/** Reads an isolated, verified immutable source; never an application connection. */
interface FrozenSourceReader
{
    /** Frozen hashes, versions, source/city/snapshot and complete table_counts. */
    public function contract(): array;

    /** @return list<string> Every table in the source snapshot, including empty tables. */
    public function tables(): array;

    /**
     * Ordered contiguous occurrences: ordinal, opaque cursor, canonical key,
     * key_kind (primary|ordinal), values (all original columns, string|null).
     */
    public function rows(string $table): iterable;
}
