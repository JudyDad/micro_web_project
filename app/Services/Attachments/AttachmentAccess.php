<?php

declare(strict_types=1);

namespace App\Services\Attachments;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\ProjectReadService;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../../../database/schema/OperationalManifest.php';

final class AttachmentAccess
{
    public function __construct(private AccountAccess $access, private ProjectReadService $projects, private AccountAuditTrail $audit) {}

    public function authorize(WebAccount $account, AttachmentReference $reference): ResolvedAttachment
    {
        abort_unless(config('attachments.enabled') === true && config('attachments.catalog_sha256') === $reference->catalogSha256
            && config('attachments.decision_locator') === $reference->decisionLocator, 404);
        // Inventory coverage is broader than this explicitly reviewed owner policy.
        abort_unless(($reference->table === 'file' && $reference->column === 'File_Content' && $reference->kind === 'blob')
            || ($reference->table === 'image' && $reference->column === 'image_NewPath' && $reference->kind === 'external'), 403);
        abort_unless($reference->ownerProjectRecordId !== null, 403);
        abort_unless($reference->state === 'verified' && $reference->expectedLength !== null && $reference->expectedSha256 !== null, 404);
        $identity = DB::table('source_records as r')->join('sources as s', 's.id', '=', 'r.source_id')
            ->join('cities as c', 'c.id', '=', 's.city_id')->where('r.id', $reference->recordId)->first(['r.*', 's.city_id', 'c.name_en']);
        abort_unless($identity && (int) $identity->source_id === $reference->sourceId && (int) $identity->snapshot_id === $reference->snapshotId, 404);
        $city = (int) $identity->city_id;
        if (! $this->access->check($account, $city, 'attachments.download') || ! $this->access->check($account, $city, 'projects.view')) {
            $this->audit->record($account->id, 'attachments.download.denied', 'denied', city: $city);
            abort(403);
        }
        $mapping = OperationalManifest::mapping($identity->name_en, $reference->table);
        abort_unless((int) $identity->table_id === $mapping['source_table_id'], 404);
        $publication = DB::table('source_publications')->where(['source_id' => $reference->sourceId, 'snapshot_id' => $reference->snapshotId])
            ->whereIn('publication_kind', app()->environment(['local', 'testing']) ? ['synthetic', 'validated'] : ['validated'])->exists();
        abort_unless($publication, 404);
        $fields = ['record_id', 'origin_source_id', 'origin_table_id', 'MicroProject_ID'];
        if ($reference->kind === 'external') {
            $fields[] = 'image_NewPath';
        }
        $attachment = DB::table($mapping['preservation_table'])->where('record_id', $reference->recordId)->first($fields);
        abort_unless($attachment && (int) $attachment->origin_source_id === $reference->sourceId
            && (int) $attachment->origin_table_id === $mapping['source_table_id'] && $attachment->MicroProject_ID !== null, 404);
        if ($reference->kind === 'external') {
            abort_unless(is_string($attachment->image_NewPath) && $reference->sourcePathSha256 !== null
                && hash_equals($reference->sourcePathSha256, hash('sha256', $attachment->image_NewPath)), 404);
        }
        $projectMapping = OperationalManifest::mapping($identity->name_en, 'microproject');
        $owners = DB::table($projectMapping['preservation_table'].' as p')->join('source_records as r', 'r.id', '=', 'p.record_id')
            ->where('r.source_id', $reference->sourceId)->where('r.snapshot_id', $reference->snapshotId)
            ->where('r.table_id', $projectMapping['source_table_id'])->where('p.origin_source_id', $reference->sourceId)
            ->where('p.origin_table_id', $projectMapping['source_table_id'])->where('p.MP_ID', $attachment->MicroProject_ID)
            ->limit(2)->get(['r.id']);
        // Do not infer an owner from a person, filename or an unqualified legacy ID.
        abort_unless($owners->count() === 1 && (int) $owners[0]->id === $reference->ownerProjectRecordId, 404);
        $this->projects->find($account, $reference->ownerProjectRecordId, $reference->sourceId);

        return new ResolvedAttachment($reference, $city, $mapping['preservation_table'], $reference->column,
            $reference->recordId, $reference->sourceId, $reference->snapshotId, $reference->ownerProjectRecordId,
            $reference->kind === 'external' ? $attachment->image_NewPath : null);
    }
}
