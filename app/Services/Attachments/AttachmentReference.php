<?php

declare(strict_types=1);

namespace App\Services\Attachments;

/** Reviewed private metadata only. Never serialize this object into public responses. */
final readonly class AttachmentReference
{
    public function __construct(
        public string $id,
        public int $sourceId,
        public int $snapshotId,
        public int $recordId,
        public string $table,
        public string $column,
        public ?int $ownerProjectRecordId,
        public string $kind,
        public ?string $storageAlias,
        public string $state,
        public ?string $expectedSha256,
        public ?int $expectedLength,
        public string $decisionLocator,
        public string $catalogSha256,
        public ?string $sourcePathSha256 = null,
    ) {}
}
