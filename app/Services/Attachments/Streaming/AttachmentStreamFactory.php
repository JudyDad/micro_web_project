<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

use App\Services\Attachments\ResolvedAttachment;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;

class AttachmentStreamFactory
{
    public function open(ResolvedAttachment $attachment): VerifiedAttachmentStream
    {
        $reference = $attachment->reference;
        $maxBytes = config('attachments.max_bytes');
        if (! is_int($maxBytes) || $maxBytes < 1 || $maxBytes > 1024 * 1024 * 1024
            || $reference->expectedLength === null || $reference->expectedSha256 === null) {
            throw new AttachmentReadFailure('unreadable');
        }
        if ($reference->kind === 'blob') {
            $chunks = $this->blob($attachment);
        } else {
            $store = config('attachments.stores.'.$reference->storageAlias);
            if (! is_array($store) || ! in_array($attachment->sourceId, $store['source_ids'] ?? [], true)
                || ($store['mapping_version'] ?? null) !== LegacyPathResolver::VERSION || $attachment->rawPath === null) {
                throw new AttachmentReadFailure('unreadable');
            }
            $path = (new LegacyPathResolver)->resolve($attachment->rawPath);
            $reader = match ($store['driver'] ?? '') {
                'local' => new LocalReadOnlyStore($store['root'] ?? ''),
                'ftps' => new FtpReadOnlyStore(new CurlFtpReadTransport($store), $reference->expectedLength),
                default => throw new AttachmentReadFailure('unreadable'),
            };
            $chunks = $reader->chunks($path);
        }

        return VerifiedAttachmentStream::stage($chunks, $reference->expectedSha256, $reference->expectedLength, $maxBytes);
    }

    private function blob(ResolvedAttachment $attachment): iterable
    {
        require_once __DIR__.'/../../../../database/schema/OperationalManifest.php';
        // Only the reviewed project-file policy can reach this DB reader.
        if ($attachment->reference->table !== 'file' || $attachment->column !== 'File_Content') {
            throw new AttachmentReadFailure('unreadable');
        }
        $source = DB::table('sources as s')->join('cities as c', 'c.id', '=', 's.city_id')
            ->where('s.id', $attachment->sourceId)->first(['c.name_en', 'c.id']);
        if (! $source || (int) $source->id !== $attachment->cityId) {
            throw new AttachmentReadFailure('unreadable');
        }
        $mapping = OperationalManifest::mapping($source->name_en, 'file');
        if ($mapping['preservation_table'] !== $attachment->preservationTable) {
            throw new AttachmentReadFailure('unreadable');
        }
        $query = DB::table($mapping['preservation_table'].' as p')->join('source_records as r', 'r.id', '=', 'p.record_id')
            ->where('r.id', $attachment->recordId)->where('r.source_id', $attachment->sourceId)
            ->where('r.snapshot_id', $attachment->snapshotId)->where('r.table_id', $mapping['source_table_id'])
            ->where('p.origin_source_id', $attachment->sourceId)->where('p.origin_table_id', $mapping['source_table_id']);
        $row = (clone $query)->selectRaw('OCTET_LENGTH(p.File_Content) AS byte_length')->first();
        if (! $row) {
            throw new AttachmentReadFailure('missing');
        }
        if ($row->byte_length === null) {
            throw new AttachmentReadFailure('null_blob');
        }
        if ((string) $row->byte_length !== (string) $attachment->reference->expectedLength) {
            throw new AttachmentReadFailure('changed_during_read');
        }
        for ($offset = 1; $offset <= (int) $row->byte_length; $offset += 65536) {
            $chunk = (clone $query)->selectRaw('SUBSTRING(p.File_Content, ?, 65536) AS payload', [$offset])->first();
            if (! $chunk || ! is_string($chunk->payload) || $chunk->payload === '') {
                throw new AttachmentReadFailure('changed_during_read');
            }
            yield $chunk->payload;
        }
    }
}
