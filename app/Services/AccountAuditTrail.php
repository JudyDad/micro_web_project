<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class AccountAuditTrail
{
    private const SUBJECTS = [
        'people.list' => [], 'people.view' => ['person_entity'],
        'people.created' => ['person_entity', 'family_entity'], 'people.updated' => ['person_entity'],
        'login.succeeded' => ['account'], 'login.denied' => [], 'password.rehashed' => ['account'],
        'logout.succeeded' => ['account'], 'access.denied' => [], 'projects.list' => [], 'projects.view' => ['project_record'],
        'account.provisioned' => ['account'], 'legacy.linked' => ['legacy_link'], 'legacy.revoked' => ['legacy_link'],
        'attachments.download.started' => ['attachment_record'], 'attachments.download.succeeded' => ['attachment_record'],
        'attachments.download.failed' => ['attachment_record'], 'attachments.download.denied' => [],
        'legacy.restored' => ['legacy_link'], 'capability.granted' => ['capability_grant'], 'capability.revoked' => ['capability_grant'],
    ];

    public static function validDecision(string $decision): bool
    {
        return strlen($decision) <= 1024 && preg_match('/\A[a-zA-Z0-9_:\/.\-]+\z/', $decision) === 1;
    }

    public function record(?int $actor, string $event, string $outcome, ?int $city = null, ?int $subjectAccount = null, ?string $subjectType = null, ?int $subjectId = null, ?string $decision = null): void
    {
        if (! array_key_exists($event, self::SUBJECTS) || ! in_array($outcome, ['succeeded', 'denied', 'failed'], true)
            || (($subjectType === null) !== ($subjectId === null))
            || ($subjectType !== null && (! in_array($subjectType, self::SUBJECTS[$event], true) || $subjectId < 1))
            || ($decision !== null && ! self::validDecision($decision))
            || (preg_match('/\A(account\.|legacy\.|capability\.)/', $event) && $decision === null)
            || ($city !== null && ! DB::table('cities')->where('id', $city)->exists())) {
            throw new InvalidArgumentException('Invalid account audit metadata.');
        }
        if ($subjectType !== null) {
            $validSubject = match ($subjectType) {
                'person_entity' => DB::table('op_person')->where('id', $subjectId)->where('city_id', $city)->exists(),
                'family_entity' => DB::table('op_family')->where('id', $subjectId)->where('city_id', $city)->exists(),
                'account' => $subjectAccount === $subjectId && DB::table('web_accounts')->where('id', $subjectId)->exists(),
                'legacy_link' => DB::table('account_legacy_links')->where('id', $subjectId)->where('account_id', $subjectAccount)->exists(),
                'capability_grant' => DB::table('account_city_capabilities')->where('id', $subjectId)->where('account_id', $subjectAccount)->where('city_id', $city)->exists(),
                'project_record' => DB::table('source_records as r')->join('sources as s', 's.id', '=', 'r.source_id')->where('r.id', $subjectId)->where('s.city_id', $city)->where('r.table_id', app(SourceCatalog::class)->tableId('microproject'))->exists(),
                'attachment_record' => DB::table('source_records as r')->join('sources as s', 's.id', '=', 'r.source_id')
                    ->join('source_tables as t', 't.id', '=', 'r.table_id')->where('r.id', $subjectId)->where('s.city_id', $city)
                    ->whereIn('t.name', ['file', 'image'])->exists(),
                default => false,
            };
            if (! $validSubject) {
                throw new InvalidArgumentException('Invalid account audit subject.');
            }
        }
        DB::table('account_audit_events')->insert([
            'actor_account_id' => $actor, 'subject_account_id' => $subjectAccount, 'city_id' => $city,
            'event_type' => $event, 'outcome' => $outcome, 'subject_type' => $subjectType, 'subject_id' => $subjectId,
            'decision_locator' => $decision, 'occurred_at' => now('UTC')->format('Y-m-d H:i:s.u'),
        ]);
    }
}
