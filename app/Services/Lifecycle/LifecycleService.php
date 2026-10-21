<?php

declare(strict_types=1);

namespace App\Services\Lifecycle;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Project state history and the lifecycle side effects of the desktop workflow.
 * Catalog states are addressed by their source-qualified English name; identifier 0 (Rejected) is a valid state.
 */
final class LifecycleService
{
    public const ON_HOLD = 'On Hold';

    public const WAITING = 'Waiting';

    public const ACCEPTED = 'Accepted';

    public const FINANCED = 'Financed';

    public const WITHDREW = 'Withdrew';

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    private function authorize(WebAccount $account, int $city, ?string $capability = null): void
    {
        if (! $this->access->check($account, $city, 'applications.view') || ($capability !== null && ! $this->access->check($account, $city, $capability))) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
    }

    private function project(int $id): object
    {
        $row = $this->tables->query('microproject')->where('p.id', $id)->first();
        abort_if($row === null, 404);

        return $row;
    }

    private function catalogState(object $project, string $nameEn): object
    {
        $state = $this->tables->catalog('state', (int) $project->city_id, (int) $project->source_id)->first(fn ($row) => $row->c_Name_en === $nameEn);
        abort_if($state === null, 409, 'The "'.$nameEn.'" state is missing from this city catalog.');

        return $state;
    }

    /** Newest first. Rows of one project, with catalog names resolved. */
    public function history(object $project): Collection
    {
        $mp = $this->tables->relation('microproject_state', 'MicroProject_ID');
        $stateRef = $this->tables->relation('microproject_state', 'State_ID');
        $reasonRef = $this->tables->relation('microproject_state', 'StateReason_ID');
        $states = $this->tables->catalog('state', (int) $project->city_id, (int) $project->source_id)->keyBy('id');
        $reasons = $this->tables->catalog('statereason', (int) $project->city_id, (int) $project->source_id)->keyBy('id');

        return $this->tables->query('microproject_state')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$mp['resolved_column'], $project->id)->where('p.'.$mp['state_column'], 'resolved')
            ->orderByDesc('p.c_Date')->orderByDesc('p.id')->get()
            ->map(function ($row) use ($states, $reasons, $stateRef, $reasonRef) {
                $state = $row->{$stateRef['state_column']} === 'resolved' ? $states->get($row->{$stateRef['resolved_column']}) : null;
                $reason = $row->{$reasonRef['state_column']} === 'resolved' ? $reasons->get($row->{$reasonRef['resolved_column']}) : null;
                $row->state_id = $state?->id;
                $row->state_name_en = $state?->c_Name_en;
                $row->state_name_ar = $state?->c_Name_ar;
                $row->reason_id = $reason?->id;
                $row->reason_name = $reason?->c_Name;

                return $row;
            });
    }

    /** The row flagged current, else the newest row of a project whose legacy rows carry no flag. */
    public function current(Collection $history): ?object
    {
        return $history->first(fn ($row) => (int) $row->c_Current_State === 1) ?? $history->first();
    }

    private function version(object $project, Collection $history): string
    {
        return hash('sha256', json_encode([$project->c_ApplicationState, $history->map(fn ($r) => [$r->id, $r->state_id, $r->reason_id, $r->c_Date, $r->c_Note, $r->c_Current_State])->all()], JSON_THROW_ON_ERROR));
    }

    /** Read model for the project page. */
    public function panel(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $history = $this->history($project);
        $current = $this->current($history);
        $canManage = $this->access->check($account, $city, 'lifecycle.manage');
        $canAccept = $canManage && $this->access->check($account, $city, 'lifecycle.accept');

        return [
            'history' => $history, 'current' => $current, 'version' => $this->version($project, $history),
            'canManage' => $canManage,
            'targets' => $canManage ? $this->targets($project, $current, $canAccept) : collect(),
            'reasons' => $canManage ? $this->tables->catalog('statereason', $city, (int) $project->source_id) : collect(),
        ];
    }

    private function targets(object $project, ?object $current, bool $canAccept): Collection
    {
        return $this->tables->catalog('state', (int) $project->city_id, (int) $project->source_id)
            ->filter(fn ($state) => $this->rejection($current, $state->c_Name_en, $canAccept) === null)->values();
    }

    /** Why a move is refused, or null when allowed. */
    private function rejection(?object $current, ?string $target, bool $canAccept): ?string
    {
        $from = $current?->state_name_en;

        return match (true) {
            $target === self::ON_HOLD => 'on_hold',
            $current !== null && $current->state_name_en === $target => 'same_state',
            $target === self::WAITING && $from === self::ON_HOLD => 'waiting_from_preliminary',
            $target === self::FINANCED && $from === self::ACCEPTED => 'financed_from_accepted',
            $target === self::ACCEPTED && ! $canAccept => 'accept_capability',
            default => null,
        };
    }

    public function transition(WebAccount $account, int $projectId, int $stateId, ?int $reasonId, ?string $note, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $stateId, $reasonId, $note, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, 'lifecycle.manage');
            $history = $this->history($project);
            abort_unless(hash_equals($this->version($project, $history), $version), 409, 'Project state changed. Reload before saving.');
            $state = $this->tables->find('state', $stateId, $city, (int) $project->source_id);
            abort_if($state === null, 422, 'Select a state from this city.');
            $reason = $this->reason($project, $reasonId);
            $refusal = $this->rejection($this->current($history), $state->c_Name_en, $this->access->check($account, $city, 'lifecycle.accept'));
            if ($refusal !== null) {
                throw ValidationException::withMessages(['state_id' => __('lifecycle.refusals.'.$refusal)]);
            }
            $this->enter($project, $state, $reason, $note);
            if ($state->c_Name_en === self::ACCEPTED) {
                $this->assignTasks($project, 'f');
            }
            if ($state->c_Name_en === self::WITHDREW) {
                $this->clearDonor($project);
            }
            $this->audit->record($account->id, 'lifecycle.transitioned', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    /** Reason and note of the state row currently in force. */
    public function updateCurrent(WebAccount $account, int $projectId, ?int $reasonId, ?string $note, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $reasonId, $note, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, 'lifecycle.manage');
            $history = $this->history($project);
            abort_unless(hash_equals($this->version($project, $history), $version), 409, 'Project state changed. Reload before saving.');
            $current = $this->current($history);
            abort_if($current === null, 422, 'The project has no state to update.');
            $reason = $this->reason($project, $reasonId);
            DB::table('op_microproject_state')->where('id', $current->id)->update(['c_Note' => $note] + $this->tables->link('microproject_state', 'StateReason_ID', $reason?->id));
            $this->audit->record($account->id, 'lifecycle.reason_updated', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    private function reason(object $project, ?int $reasonId): ?object
    {
        if ($reasonId === null) {
            return null;
        }
        $reason = $this->tables->find('statereason', $reasonId, (int) $project->city_id, (int) $project->source_id);
        abort_if($reason === null, 422, 'Select a reason from this city.');

        return $reason;
    }

    /** Clears every current flag and records the new state as current. Caller holds the project lock. */
    private function enter(object $project, object $state, ?object $reason, ?string $note): void
    {
        $flag = $this->tables->relation('microproject_state', 'MicroProject_ID');
        DB::table('op_microproject_state')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($flag['resolved_column'], $project->id)->where(fn ($q) => $q->whereNull('c_Current_State')->orWhere('c_Current_State', '<>', 0))->update(['c_Current_State' => 0]);
        $this->tables->insert('microproject_state', (int) $project->city_id, (int) $project->source_id, [
            'c_Current_State' => 1, 'c_Date' => now()->toDateString(), 'c_Note' => $note,
        ] + $this->tables->link('microproject_state', 'MicroProject_ID', (int) $project->id)
            + $this->tables->link('microproject_state', 'State_ID', (int) $state->id)
            + $this->tables->link('microproject_state', 'StateReason_ID', $reason === null ? null : (int) $reason->id));
    }

    /** Desktop assigns every task of the given type once; a retry must not duplicate the assignment. */
    private function assignTasks(object $project, string $type): void
    {
        $projectRef = $this->tables->relation('task_microproject', 'MicroProject_ID');
        $taskRef = $this->tables->relation('task_microproject', 'Task_ID');
        $assigned = DB::table('op_task_microproject')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($projectRef['resolved_column'], $project->id)->pluck($taskRef['resolved_column'])->map(fn ($id) => (int) $id)->all();
        foreach ($this->tables->catalog('task', (int) $project->city_id, (int) $project->source_id) as $task) {
            if ($task->c_T_Type !== $type || in_array((int) $task->id, $assigned, true)) {
                continue;
            }
            $this->tables->insert('task_microproject', (int) $project->city_id, (int) $project->source_id, [
                'c_State' => (int) $task->c_T_ID === 1 ? 1 : 0, 'c_Date' => now()->format('Y-m-d H:i:s'),
            ] + $this->tables->link('task_microproject', 'MicroProject_ID', (int) $project->id) + $this->tables->link('task_microproject', 'Task_ID', (int) $task->id));
        }
    }

    private function clearDonor(object $project): void
    {
        $donor = $this->tables->relation('microproject', 'MP_Donor');
        if ($project->{$donor['state_column']} === 'resolved') {
            DB::table('op_microproject')->where('id', $project->id)->update($this->tables->link('microproject', 'MP_Donor', null));
        }
    }

    /** Preliminary creation: first history row. Called inside the creation transaction. */
    public function recordPreliminary(object $project): void
    {
        $this->enter($project, $this->catalogState($project, self::ON_HOLD), null, null);
    }

    /**
     * Preliminary → full handoff: application state, Waiting history row and the "a" task assignments.
     * Caller holds the project lock and has authorized the change.
     */
    public function enterFull(object $project): void
    {
        DB::table('op_microproject')->where('id', $project->id)->update(['c_ApplicationState' => 'project']);
        $this->enter($project, $this->catalogState($project, self::WAITING), null, null);
        $this->assignTasks($project, 'a');
    }
}
