<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class AccountAuditTrail
{
    private const SUBJECTS = [
        'applications.list' => [], 'reports.statistics' => [], 'reports.exported' => [], 'logs.listed' => [], 'lookups.listed' => [], 'reports.search' => [], 'applications.view' => ['application_entity'],
        'applications.created' => ['application_entity'], 'applications.updated' => ['application_entity'],
        'lifecycle.transitioned' => ['application_entity'], 'lifecycle.reason_updated' => ['application_entity'],
        'assessments.view' => ['application_entity'], 'assessments.saved' => ['application_entity'], 'assessments.printed' => ['application_entity'],
        'finance.list' => [], 'finance.view' => ['application_entity'], 'finance.donor_assigned' => ['application_entity'], 'finance.group_created' => ['application_entity'],
        'finance.loan_saved' => ['application_entity'], 'finance.payments_saved' => ['application_entity'],
        'executive_files.view' => ['application_entity'], 'executive_files.saved' => ['application_entity'],
        'purchasing.view' => ['application_entity'], 'purchasing.saved' => ['application_entity'],
        'special_cases.view' => ['application_entity'], 'special_cases.saved' => ['application_entity'],
        'visits.schedule' => [], 'visits.view' => ['application_entity'], 'visits.saved' => ['application_entity'],
        'special_cases.deleted' => ['application_entity'], 'visits.deleted' => ['application_entity'],
        'me_visits.view' => ['application_entity'], 'me_visits.saved' => ['application_entity'], 'me_visits.deleted' => ['application_entity'],
        'special_cases.restored' => ['application_entity'],
        'profession_meetings.list' => [], 'profession_meetings.view' => ['meeting_entity'], 'profession_meetings.saved' => ['meeting_entity'], 'profession_meetings.deleted' => ['meeting_entity'], 'profession_meetings.restored' => ['meeting_entity'], 'visits.restored' => ['application_entity'], 'me_visits.restored' => ['application_entity'],
        'tasks.board' => [], 'tasks.toggled' => ['application_entity'],
        'notifications.list' => [], 'notifications.seen' => [], 'notifications.sent' => [], 'notifications.noted' => [], 'notifications.rescheduled' => [],
        'accounts.list' => [], 'accounts.role_assigned' => ['account'], 'accounts.role_revoked' => ['account'], 'accounts.email_set' => ['account'], 'accounts.tasks_saved' => [],
        'password.changed' => ['account'], 'password.reset_requested' => ['account'], 'password.reset_denied' => [], 'password.reset' => ['account'], 'accounts.view' => ['account'], 'accounts.disabled' => ['account'], 'accounts.enabled' => ['account'],
        'application_draft.saved' => [], 'application_draft.viewed' => [], 'application_draft.discarded' => [],
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
                'application_entity' => DB::table('op_microproject')->where('id', $subjectId)->where('city_id', $city)->exists(),
                'person_entity' => DB::table('op_person')->where('id', $subjectId)->where('city_id', $city)->exists(),
                'meeting_entity' => DB::table('op_profession_meeting')->where('id', $subjectId)->where('city_id', $city)->exists(),
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
