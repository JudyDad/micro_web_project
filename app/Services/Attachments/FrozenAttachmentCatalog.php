<?php

declare(strict_types=1);

namespace App\Services\Attachments;

use App\Services\AccountAuditTrail;
use RuntimeException;

/** Temporary read-only catalog adapter; does not imply a physical file-catalog schema. */
final class FrozenAttachmentCatalog implements AttachmentCatalog
{
    private const COLUMNS = [
        'file.File_Content' => 'blob', 'image.image_NewPath' => 'external',
        'backgrounds.Image' => 'blob', 'manualfile.Content' => 'blob',
        'manualfile.Path' => 'external', 'user.ProfilePicture' => 'blob',
    ];

    public function find(string $opaqueReference): ?AttachmentReference
    {
        if (config('attachments.enabled') !== true || ! preg_match('/\A[a-f0-9]{32}\z/', $opaqueReference)) {
            return null;
        }
        $path = config('attachments.catalog_path');
        $pin = config('attachments.catalog_sha256');
        $decision = config('attachments.decision_locator');
        if (! is_string($path) || ! is_file($path) || is_link($path) || ! is_string($pin) || ! preg_match('/\A[a-f0-9]{64}\z/', $pin)
            || ! is_string($decision) || ! AccountAuditTrail::validDecision($decision)) {
            throw new RuntimeException('Reviewed attachment catalog configuration is unavailable.');
        }
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Private attachment catalog is unreadable.');
        }
        try {
            $bytes = stream_get_contents($handle, 16 * 1024 * 1024 + 1);
        } finally {
            fclose($handle);
        }
        if ($bytes === false || strlen($bytes) > 16 * 1024 * 1024 || ! hash_equals($pin, hash('sha256', $bytes))) {
            throw new RuntimeException('Private attachment catalog hash does not match its reviewed pin.');
        }
        $catalog = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        if (($catalog['version'] ?? null) !== 1 || ($catalog['decision_locator'] ?? null) !== $decision
            || ! is_array($catalog['references'] ?? null) || ! array_is_list($catalog['references']) || count($catalog['references']) > 50000) {
            throw new RuntimeException('Private attachment catalog has an unsupported reviewed format.');
        }
        $seen = [];
        $match = null;
        foreach ($catalog['references'] as $entry) {
            $reference = $this->reference($entry, $decision, $pin);
            if (isset($seen[$reference->id])) {
                throw new RuntimeException('Private attachment catalog reuses an opaque reference.');
            }
            $seen[$reference->id] = true;
            if ($reference->id === $opaqueReference) {
                $match = $reference;
            }
        }

        return $match;
    }

    private function reference(mixed $entry, string $decision, string $pin): AttachmentReference
    {
        if (! is_array($entry) || ! is_string($entry['id'] ?? null) || ! preg_match('/\A[a-f0-9]{32}\z/', $entry['id'])) {
            throw new RuntimeException('Private attachment reference has an invalid opaque identity.');
        }
        foreach (['source_id', 'snapshot_id', 'source_record_id'] as $field) {
            if (! is_int($entry[$field] ?? null) || $entry[$field] < 1) {
                throw new RuntimeException('Private attachment reference has invalid provenance.');
            }
        }
        $owner = $entry['owner_project_record_id'] ?? null;
        $length = $entry['expected_length'] ?? null;
        $hash = $entry['expected_sha256'] ?? null;
        $alias = $entry['storage_alias'] ?? null;
        $pathHash = $entry['source_path_sha256'] ?? null;
        $kind = $entry['kind'] ?? null;
        $table = $entry['table'] ?? null;
        $column = $entry['column'] ?? null;
        if (! is_string($table) || ! is_string($column) || (self::COLUMNS[$table.'.'.$column] ?? null) !== $kind
            || ($owner !== null && (! is_int($owner) || $owner < 1))
            || ($length !== null && (! is_int($length) || $length < 0))
            || ($hash !== null && (! is_string($hash) || ! preg_match('/\A[a-f0-9]{64}\z/', $hash)))
            || ($pathHash !== null && (! is_string($pathHash) || ! preg_match('/\A[a-f0-9]{64}\z/', $pathHash)))
            || ($alias !== null && (! is_string($alias) || ! preg_match('/\A[a-z][a-z0-9_-]{0,31}\z/', $alias)))
            || ($kind === 'blob' && ($alias !== null || $pathHash !== null)) || ($kind === 'external' && $alias === null)
            || ! in_array($entry['state'] ?? null, ['verified', 'missing', 'unreadable', 'unsafe', 'null', 'conflict', 'changed', 'unverified', 'unresolved_owner'], true)
            || (($entry['state'] ?? null) === 'verified' && ($hash === null || $length === null || ($kind === 'external' && $pathHash === null)))) {
            throw new RuntimeException('Private attachment reference has invalid storage or verification metadata.');
        }

        return new AttachmentReference($entry['id'], $entry['source_id'], $entry['snapshot_id'], $entry['source_record_id'],
            $table, $column, $owner, $kind, $alias, $entry['state'], $hash, $length, $decision, $pin, $pathHash);
    }
}
