<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use App\Services\SourceIdentity;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The task board (`TaskBoard_Form`): one row per project with a done / not done cell for every task of the board, across the
 * permitted cities. Cells can be toggled by a staff member who is responsible for the task (the desktop's `task_user`); the
 * responsibility of a historical source user is carried to the web account through its reviewed legacy link. `tasks.manage_all`
 * lets an administrator toggle any task of a city, because a web-only account has no source user to be assigned.
 */
final class TaskBoardService
{
    public const PER_PAGE = 50;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private SourceIdentity $identity, private NotificationService $notices) {}

    /** Cities where the account may read the task board. */
    public function cities(WebAccount $account): Collection
    {
        $view = $this->access->cities($account, 'applications.view')->pluck('id')->all();

        return $this->access->cities($account, 'tasks.view')->filter(fn ($city) => in_array($city->id, $view, true))->values();
    }

    /**
     * @param  array{kind?: ?string, cities?: list<int>, q?: ?string, state?: ?int, donor?: ?string, type?: ?string, tasks?: array<int, string>}  $filters
     */
    public function board(WebAccount $account, array $filters): array
    {
        $permitted = $this->cities($account);
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $kind = ($filters['kind'] ?? 'f') === 'a' ? 'a' : 'f';
        $selected = array_values(array_intersect($filters['cities'] ?? [], $permitted->pluck('id')->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->all();
        $columns = TaskBoardColumns::for($kind);
        $this->audit->record($account->id, 'tasks.board', 'succeeded');

        $context = [];
        foreach ($ids as $city) {
            $context[$city] = $this->context($city, $kind);
        }

        $mpRef = $this->tables->relation('microproject_state', 'MicroProject_ID');
        $stateRef = $this->tables->relation('microproject_state', 'State_ID');
        $donorRef = $this->tables->relation('microproject', 'MP_Donor');
        $typeRef = $this->tables->relation('microproject', 'MP_Type_ID');
        $tmpProject = $this->tables->relation('task_microproject', 'MicroProject_ID');
        $tmpTask = $this->tables->relation('task_microproject', 'Task_ID');
        $wanted = array_filter($filters['tasks'] ?? [], fn ($flag, $legacy) => isset($columns[(int) $legacy]) && in_array($flag, ['0', '1'], true), ARRAY_FILTER_USE_BOTH);

        $query = $this->tables->query('microproject')->whereIn('p.city_id', $ids);
        $query->where(function (Builder $any) use ($ids, $context, $filters, $mpRef, $stateRef, $donorRef, $typeRef, $tmpProject, $tmpTask, $wanted) {
            foreach ($ids as $city) {
                $ctx = $context[$city];
                $any->orWhere(function (Builder $q) use ($city, $ctx, $filters, $mpRef, $stateRef, $donorRef, $typeRef, $tmpProject, $tmpTask, $wanted) {
                    $q->where('p.city_id', $city)->whereExists(function (Builder $s) use ($ctx, $filters, $mpRef, $stateRef) {
                        $stateIds = $ctx['stateIds'];
                        if (! empty($filters['state'])) {
                            $stateIds = array_values(array_intersect($stateIds, $ctx['statesByLegacy'][(int) $filters['state']] ?? []));
                        }
                        $s->selectRaw('1')->from('op_microproject_state as s')->whereColumn('s.city_id', 'p.city_id')->whereColumn('s.source_id', 'p.source_id')
                            ->whereColumn('s.'.$mpRef['resolved_column'], 'p.id')->where('s.'.$mpRef['state_column'], 'resolved')->where('s.c_Current_State', 1)
                            ->where('s.'.$stateRef['state_column'], 'resolved')->whereIn('s.'.$stateRef['resolved_column'], $stateIds === [] ? [0] : $stateIds);
                    });
                    foreach (['donor' => [$donorRef, 'donors'], 'type' => [$typeRef, 'types']] as $key => [$ref, $catalog]) {
                        if (! empty($filters[$key])) {
                            $match = $ctx[$catalog][(string) $filters[$key]] ?? [];
                            $q->where('p.'.$ref['state_column'], 'resolved')->whereIn('p.'.$ref['resolved_column'], $match === [] ? [0] : $match);
                        }
                    }
                    foreach ($wanted as $legacy => $flag) {
                        $taskRow = $ctx['tasks'][(int) $legacy] ?? null;
                        if ($taskRow === null) {
                            $q->whereRaw('1 = 0');

                            continue;
                        }
                        $q->whereExists(function (Builder $t) use ($tmpProject, $tmpTask, $taskRow, $flag) {
                            $t->selectRaw('1')->from('op_task_microproject as t')->whereColumn('t.city_id', 'p.city_id')->whereColumn('t.source_id', 'p.source_id')
                                ->whereColumn('t.'.$tmpProject['resolved_column'], 'p.id')->where('t.'.$tmpProject['state_column'], 'resolved')
                                ->where('t.'.$tmpTask['state_column'], 'resolved')->where('t.'.$tmpTask['resolved_column'], $taskRow);
                            $flag === '1' ? $t->where('t.c_State', '<>', 0) : $t->where('t.c_State', 0);
                        });
                    }
                });
            }
        });
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->whereRaw("p.c_MP_Name LIKE ? ESCAPE '!'", [$this->like($search)]);
                if (ctype_digit($search)) {
                    $q->orWhere('p.c_MP_ID', (int) $search);
                }
                $q->orWhereExists(function (Builder $s) use ($search) {
                    $link = $this->tables->relation('person_microproject', 'MicroProject_ID');
                    $person = $this->tables->relation('person_microproject', 'Person_ID');
                    $like = $this->like($search);
                    $s->selectRaw('1')->from('op_person_microproject as l')->join('op_person as pe', fn ($j) => $j->on('pe.id', '=', 'l.'.$person['resolved_column'])->on('pe.city_id', '=', 'l.city_id'))
                        ->whereColumn('l.city_id', 'p.city_id')->whereColumn('l.source_id', 'p.source_id')->whereColumn('l.'.$link['resolved_column'], 'p.id')
                        ->where('l.'.$link['state_column'], 'resolved')->where('l.'.$person['state_column'], 'resolved')
                        ->where(fn ($n) => $n->whereRaw("pe.c_P_FirstName LIKE ? ESCAPE '!'", [$like])->orWhereRaw("pe.c_P_LastName LIKE ? ESCAPE '!'", [$like])->orWhereRaw("pe.c_P_FatherName LIKE ? ESCAPE '!'", [$like])->orWhereRaw("pe.c_P_Mobile LIKE ? ESCAPE '!'", [$like]));
                });
            });
        }

        $rows = $query->orderByDesc('p.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id')->paginate(self::PER_PAGE)->withQueryString();
        $rows->setCollection($this->decorate($account, $rows->getCollection(), $context, $columns, $kind));

        return [
            'rows' => $rows, 'cities' => $permitted, 'kind' => $kind, 'filters' => $filters,
            'columns' => $this->labels($columns, $context), 'options' => $this->options($context),
            'responsible' => $this->responsible($context, $columns), 'shown' => $ids,
        ];
    }

    /**
     * Toggles one task of one project. The submitted `expected` state is the one the page showed: a stale or repeated submission is
     * refused. Completing "مشتريات" notifies the city's communication staff; clearing it marks the project's notices seen.
     */
    public function toggle(WebAccount $account, int $projectId, int $legacyTask, int $expected, int $done): void
    {
        // Checked first, outside the transaction, so that a refusal is audited; checked again under the lock.
        $this->authorize($account, $projectId, $legacyTask, true);
        DB::transaction(function () use ($account, $projectId, $legacyTask, $expected, $done) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            [$project, $task] = $this->authorize($account, $projectId, $legacyTask, false);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $projectRef = $this->tables->relation('task_microproject', 'MicroProject_ID');
            $taskRef = $this->tables->relation('task_microproject', 'Task_ID');
            $cells = DB::table('op_task_microproject')->where('city_id', $city)->where('source_id', $source)
                ->where($projectRef['resolved_column'], $projectId)->where($projectRef['state_column'], 'resolved')
                ->where($taskRef['resolved_column'], $task->id)->where($taskRef['state_column'], 'resolved')->orderBy('id')->lockForUpdate()->get();
            abort_if($cells->isEmpty(), 404);
            abort_unless(((int) ($cells->first()->c_State ?? 0) !== 0 ? 1 : 0) === $expected && $expected !== $done, 409, 'The task changed. Reload before saving.');
            DB::table('op_task_microproject')->whereIn('id', $cells->pluck('id')->all())->update(['c_State' => $done, 'c_Date' => now()->toDateString().' 00:00:00']);
            if ($legacyTask === TaskBoardColumns::PURCHASES) {
                if ($done === 1) {
                    $name = $this->names([$projectId])[$projectId] ?? '';
                    $sender = $this->notices->senderFor($account, $source);
                    foreach ($this->notices->recipients($source, 'communication') as $recipient) {
                        $this->notices->add($city, $source, $project, now()->toDateString(), 'انتهاء المشتريات', $name, NotificationService::PURCHASES_DONE, $recipient, $sender);
                    }
                } else {
                    $this->notices->setSeenForProject($project, NotificationService::PURCHASES_DONE, 1);
                }
            }
            $this->audit->record($account->id, 'tasks.toggled', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    /**
     * The project and task row of a toggle the account may make: the project is on the board of the task's kind, the account has
     * the city's view and manage permissions and is responsible for the task (or may manage every task).
     *
     * @return array{0: object, 1: object}
     */
    private function authorize(WebAccount $account, int $projectId, int $legacyTask, bool $audit): array
    {
        $project = $this->tables->query('microproject')->where('p.id', $projectId)->first();
        abort_if($project === null, 404);
        $city = (int) $project->city_id;
        $source = (int) $project->source_id;
        $kind = isset(TaskBoardColumns::FUNDED[$legacyTask]) ? 'f' : (isset(TaskBoardColumns::APPLICATION[$legacyTask]) ? 'a' : null);
        abort_if($kind === null, 404);
        $deny = function () use ($account, $audit): never {
            if ($audit) {
                $this->audit->record($account->id, 'access.denied', 'denied');
            }
            abort(403);
        };
        foreach (['applications.view', 'tasks.view', 'tasks.manage'] as $capability) {
            $this->access->check($account, $city, $capability) || $deny();
        }
        $task = $this->tables->catalog('task', $city, $source)->first(fn ($row) => (int) ($row->c_T_ID ?? 0) === $legacyTask);
        abort_if($task === null, 404);
        $this->access->check($account, $city, 'tasks.manage_all') || $this->assigned($account, $city, $source, [(int) $task->id])->contains((int) $task->id) || $deny();
        abort_unless($this->onBoard($project, $kind), 404);

        return [$project, $task];
    }

    /** Per city: its source, the catalog ids the board needs and the states the board lists. */
    private function context(int $city, string $kind): array
    {
        $source = $this->tables->source($city);
        $states = $this->tables->catalog('state', $city, $source);
        $byLegacy = [];
        foreach ($states as $state) {
            $byLegacy[(int) $state->c_ID][] = (int) $state->id;
        }
        $listed = $kind === 'f' ? TaskBoardColumns::FUNDED_STATES : null;
        $stateIds = $states->filter(fn ($state) => $listed === null || in_array((int) $state->c_ID, $listed, true))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $group = fn (Collection $rows, string $column) => $rows->filter(fn ($row) => ($row->{$column} ?? '') !== '')->groupBy(fn ($row) => (string) $row->{$column})->map(fn ($g) => $g->pluck('id')->map(fn ($id) => (int) $id)->all())->all();
        $donors = $this->tables->catalog('donor', $city, $source);
        $types = $this->tables->catalog('microprojecttype', $city, $source);
        $tasks = [];
        foreach ($this->tables->catalog('task', $city, $source) as $task) {
            $tasks[(int) $task->c_T_ID] ??= (int) $task->id;
        }

        return [
            'source' => $source, 'states' => $states->keyBy('id'), 'stateIds' => $stateIds, 'statesByLegacy' => $byLegacy, 'tasks' => $tasks,
            'taskNames' => $this->tables->catalog('task', $city, $source)->mapWithKeys(fn ($task) => [(int) $task->c_T_ID => (string) $task->c_T_Name])->all(),
            'donorRows' => $donors, 'donors' => $group($donors, 'c_Name_abb'), 'typeRows' => $types, 'types' => $group($types, 'c_Name'),
        ];
    }

    private function labels(array $columns, array $context): array
    {
        $labels = [];
        foreach ($columns as $legacy => $fallback) {
            $name = null;
            foreach ($context as $ctx) {
                $name ??= ($ctx['taskNames'][$legacy] ?? '') !== '' ? $ctx['taskNames'][$legacy] : null;
            }
            $labels[$legacy] = $name ?? $fallback;
        }

        return $labels;
    }

    private function options(array $context): array
    {
        $states = [];
        $donors = [];
        $types = [];
        foreach ($context as $ctx) {
            foreach ($ctx['states'] as $state) {
                if (in_array((int) $state->id, $ctx['stateIds'], true)) {
                    $states[(int) $state->c_ID] ??= app()->isLocale('ar') ? ($state->c_Name_ar ?? $state->c_Name_en) : ($state->c_Name_en ?? $state->c_Name_ar);
                }
            }
            $donors += array_fill_keys(array_keys($ctx['donors']), true);
            $types += array_fill_keys(array_keys($ctx['types']), true);
        }
        ksort($states);
        ksort($donors);
        ksort($types);

        return ['states' => $states, 'donors' => array_keys($donors), 'types' => array_keys($types)];
    }

    /** Whether the project's current state lists it on the board of this task kind. */
    private function onBoard(object $project, string $kind): bool
    {
        $mpRef = $this->tables->relation('microproject_state', 'MicroProject_ID');
        $stateRef = $this->tables->relation('microproject_state', 'State_ID');
        $current = DB::table('op_microproject_state')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($mpRef['resolved_column'], $project->id)->where($mpRef['state_column'], 'resolved')->where('c_Current_State', 1)->where($stateRef['state_column'], 'resolved')
            ->pluck($stateRef['resolved_column'])->map(fn ($id) => (int) $id)->all();
        if ($current === []) {
            return false;
        }
        if ($kind === 'a') {
            return true;
        }
        $legacy = $this->tables->catalog('state', (int) $project->city_id, (int) $project->source_id)->filter(fn ($state) => in_array((int) $state->id, $current, true))->pluck('c_ID')->map(fn ($id) => (int) $id);

        return $legacy->contains(fn ($id) => in_array($id, TaskBoardColumns::FUNDED_STATES, true));
    }

    /**
     * Ids of the given task rows the account is responsible for: assigned (`task_user`) to a source user that the account is linked to.
     *
     * @param  list<int>  $taskIds
     * @return Collection<int, int>
     */
    private function assigned(WebAccount $account, int $city, int $source, array $taskIds): Collection
    {
        $taskRef = $this->tables->relation('task_user', 'Task_ID');
        $userRef = $this->tables->relation('task_user', 'User_ID');

        return DB::table('op_task_user as tu')->where('tu.city_id', $city)->where('tu.source_id', $source)
            ->whereIn('tu.'.$taskRef['resolved_column'], $taskIds === [] ? [0] : $taskIds)->where('tu.'.$taskRef['state_column'], 'resolved')->where('tu.'.$userRef['state_column'], 'resolved')
            ->whereIn('tu.'.$userRef['resolved_column'], function (Builder $q) use ($account, $source) {
                $q->select('wp.source_record_id')->from('account_legacy_links as l')->join('web_principals as wp', 'wp.id', '=', 'l.principal_id')
                    ->where('l.account_id', $account->id)->whereNull('l.revoked_at')->whereNull('wp.disabled_at')->where('l.source_id', $source)->where('wp.source_id', $source);
            })->pluck('tu.'.$taskRef['resolved_column'])->map(fn ($id) => (int) $id)->unique()->values();
    }

    /** City → legacy task → names of the historical source users responsible for it. */
    private function responsible(array $context, array $columns): array
    {
        $taskRef = $this->tables->relation('task_user', 'Task_ID');
        $userRef = $this->tables->relation('task_user', 'User_ID');
        $result = [];
        foreach ($context as $city => $ctx) {
            $legacyByRow = array_flip(array_intersect_key($ctx['tasks'], $columns));
            if ($legacyByRow === []) {
                continue;
            }
            $links = $this->tables->query('task_user')->where('p.city_id', $city)->where('p.source_id', $ctx['source'])
                ->whereIn('p.'.$taskRef['resolved_column'], array_keys($legacyByRow))->where('p.'.$taskRef['state_column'], 'resolved')->where('p.'.$userRef['state_column'], 'resolved')->orderBy('p.id')->get();
            $names = $links->isEmpty() ? collect() : $this->identity->publishedUsers($ctx['source'])->whereIn('r.id', $links->pluck($userRef['resolved_column'])->unique()->all())->pluck('u.UserName', 'r.id');
            foreach ($links as $link) {
                $name = $names->get((int) $link->{$userRef['resolved_column']});
                if ($name !== null) {
                    $result[$city][$legacyByRow[(int) $link->{$taskRef['resolved_column']}]][] = (string) $name;
                }
            }
        }

        return $result;
    }

    private function decorate(WebAccount $account, Collection $projects, array $context, array $columns, string $kind): Collection
    {
        $ids = $projects->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($ids === []) {
            return $projects;
        }
        $mpRef = $this->tables->relation('microproject_state', 'MicroProject_ID');
        $stateRef = $this->tables->relation('microproject_state', 'State_ID');
        $current = [];
        foreach ($this->tables->query('microproject_state')->whereIn('p.'.$mpRef['resolved_column'], $ids)->where('p.'.$mpRef['state_column'], 'resolved')->where('p.c_Current_State', 1)->where('p.'.$stateRef['state_column'], 'resolved')->orderBy('p.id')->get() as $row) {
            $current[(int) $row->{$mpRef['resolved_column']}] ??= (int) $row->{$stateRef['resolved_column']};
        }
        $loanRef = $this->tables->relation('loan', 'MicroProject_ID');
        $loans = [];
        if ($kind === 'f') {
            foreach ($this->tables->query('loan')->whereIn('p.'.$loanRef['resolved_column'], $ids)->where('p.'.$loanRef['state_column'], 'resolved')->get() as $loan) {
                $day = substr((string) ($loan->c_Loan_DateTaken ?? ''), 0, 10);
                $project = (int) $loan->{$loanRef['resolved_column']};
                if ($day !== '' && ($loans[$project] ?? $day) >= $day) {
                    $loans[$project] = $day;
                }
            }
        }
        $people = $this->people($ids);
        $tmpProject = $this->tables->relation('task_microproject', 'MicroProject_ID');
        $tmpTask = $this->tables->relation('task_microproject', 'Task_ID');
        $cells = [];
        foreach ($this->tables->query('task_microproject')->whereIn('p.'.$tmpProject['resolved_column'], $ids)->where('p.'.$tmpProject['state_column'], 'resolved')->where('p.'.$tmpTask['state_column'], 'resolved')->orderBy('p.id')->get() as $cell) {
            $cells[(int) $cell->{$tmpProject['resolved_column']}][(int) $cell->{$tmpTask['resolved_column']}] ??= $cell;
        }
        $donorRef = $this->tables->relation('microproject', 'MP_Donor');
        $typeRef = $this->tables->relation('microproject', 'MP_Type_ID');
        $assigned = [];
        $manage = [];
        foreach ($context as $city => $ctx) {
            $manage[$city] = $this->access->check($account, $city, 'tasks.manage');
            $assigned[$city] = ! $manage[$city] ? collect() : ($this->access->check($account, $city, 'tasks.manage_all')
                ? collect($ctx['tasks'])->values() : $this->assigned($account, $city, $ctx['source'], array_values(array_intersect_key($ctx['tasks'], $columns))));
        }

        return $projects->map(function ($project) use ($context, $columns, $current, $loans, $people, $cells, $donorRef, $typeRef, $assigned, $manage) {
            $ctx = $context[(int) $project->city_id];
            $state = isset($current[(int) $project->id]) ? $ctx['states']->get($current[(int) $project->id]) : null;
            $donor = $project->{$donorRef['state_column']} === 'resolved' ? $ctx['donorRows']->firstWhere('id', (int) $project->{$donorRef['resolved_column']}) : null;
            $type = $project->{$typeRef['state_column']} === 'resolved' ? $ctx['typeRows']->firstWhere('id', (int) $project->{$typeRef['resolved_column']}) : null;
            $project->state_name = $state === null ? null : (app()->isLocale('ar') ? ($state->c_Name_ar ?? $state->c_Name_en) : ($state->c_Name_en ?? $state->c_Name_ar));
            $project->state_legacy = $state === null ? null : (int) $state->c_ID;
            $project->donor_name = $donor?->c_Name_abb;
            $project->type_name = $type?->c_Name;
            $project->people_names = $people[(int) $project->id]['names'] ?? [];
            $project->mobile = $people[(int) $project->id]['mobile'] ?? null;
            $project->loan_day = $loans[(int) $project->id] ?? null;
            $project->cells = [];
            foreach ($columns as $legacy => $_) {
                $taskRow = $ctx['tasks'][$legacy] ?? null;
                $cell = $taskRow === null ? null : ($cells[(int) $project->id][$taskRow] ?? null);
                $project->cells[$legacy] = [
                    'assigned' => $cell !== null, 'done' => $cell !== null && (int) ($cell->c_State ?? 0) !== 0,
                    'manageable' => $cell !== null && $manage[(int) $project->city_id] && $assigned[(int) $project->city_id]->contains($taskRow),
                ];
            }

            return $project;
        });
    }

    /** @return array<int, string> project id → distinct linked people, joined as the desktop's GROUP_CONCAT */
    private function names(array $projectIds): array
    {
        return array_map(fn ($entry) => implode(',', $entry['names']), $this->people($projectIds));
    }

    /** @return array<int, array{names: list<string>, mobile: ?string}> */
    private function people(array $projectIds): array
    {
        $projectRef = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');
        $links = $this->tables->query('person_microproject')->whereIn('p.'.$projectRef['resolved_column'], $projectIds)->where('p.'.$projectRef['state_column'], 'resolved')
            ->where('p.'.$personRef['state_column'], 'resolved')->orderBy('p.id')->get();
        $links = $links->sortBy(fn ($link) => ['مستفيد' => 0, 'شريك' => 1][$link->c_PersonType ?? ''] ?? 2)->values();
        $people = $links->isEmpty() ? collect() : $this->tables->query('person')->whereIn('p.id', $links->pluck($personRef['resolved_column'])->unique()->all())->get()->keyBy('id');
        $result = [];
        foreach ($links as $link) {
            $person = $people->get((int) $link->{$personRef['resolved_column']});
            $project = (int) $link->{$projectRef['resolved_column']};
            if ($person === null) {
                continue;
            }
            $name = trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? '').' ابن/ة '.($person->c_P_FatherName ?? ''));
            $result[$project]['names'] ??= [];
            if (! in_array($name, $result[$project]['names'], true)) {
                $result[$project]['names'][] = $name;
            }
            $result[$project]['mobile'] ??= ($person->c_P_Mobile ?? '') !== '' ? (string) $person->c_P_Mobile : null;
        }

        return $result;
    }

    /** A contains-pattern for `LIKE … ESCAPE '!'`, which behaves the same on MySQL and SQLite. */
    private function like(string $value): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value).'%';
    }
}
