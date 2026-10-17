<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class AuditTrail
{
    private const SUBJECTS = [
        'login.succeeded' => ['principal'], 'login.denied' => [], 'password.rehashed' => ['principal'], 'logout.succeeded' => ['principal'],
        'projects.list' => [], 'projects.view' => ['project_record'], 'access.denied' => [],
        'capability.granted' => ['capability_grant'], 'capability.revoked' => ['capability_grant'], 'publication.changed' => ['publication'],
        'fixture.created' => [],
    ];

    public function record(int $source, ?int $actor, string $event, string $outcome, ?string $subjectType = null, ?int $subjectId = null, ?string $decision = null): void
    {
        if (! array_key_exists($event, self::SUBJECTS) || ! in_array($outcome, ['succeeded', 'denied', 'failed'], true)
            || (($subjectType === null) !== ($subjectId === null))
            || ($subjectType !== null && (! in_array($subjectType, self::SUBJECTS[$event], true) || $subjectId < 1))
            || ($decision !== null && (strlen($decision) > 1024 || ! preg_match('/\A[a-zA-Z0-9_:\/.\-]+\z/', $decision)))
            || (in_array($event, ['capability.granted', 'capability.revoked', 'publication.changed', 'fixture.created'], true) && $decision === null)) {
            throw new InvalidArgumentException('Invalid audit event metadata.');
        }

        DB::table('audit_events')->insert([
            'source_id' => $source, 'actor_principal_id' => $actor, 'event_type' => $event, 'outcome' => $outcome,
            'subject_type' => $subjectType, 'subject_id' => $subjectId, 'request_id' => null,
            'decision_locator' => $decision, 'occurred_at' => now('UTC')->format('Y-m-d H:i:s.u'),
        ]);
    }
}
