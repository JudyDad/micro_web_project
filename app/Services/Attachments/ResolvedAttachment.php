<?php

declare(strict_types=1);

namespace App\Services\Attachments;

/** Server-resolved identifiers/path, supplied only after current owner authorization. */
final readonly class ResolvedAttachment
{
    public function __construct(
        public AttachmentReference $reference,
        public int $cityId,
        public string $preservationTable,
        public string $column,
        public int $recordId,
        public int $sourceId,
        public int $snapshotId,
        public int $ownerProjectRecordId,
        public ?string $rawPath,
    ) {}
}
