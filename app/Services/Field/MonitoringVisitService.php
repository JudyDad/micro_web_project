<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\Fields;
use App\Services\Finance\FinanceService;
use App\Services\Finance\Money;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Operational\OperationalTables;
use App\Services\Staff\ProjectNotices;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Monitoring and evaluation (M&E) visits (`V_ME_Other_Form`, `V_ME_Taxi_Form`, `Classes/M_and_E.cs`).
 * A visit belongs to one person of a funded project (beneficiary or partner), has a number, a net profit and one answer per
 * catalog question of its kind (`o` other projects, `v` vehicles). The result is the sum of the answers' marks plus the mark of the profit range.
 * The visitors and their evaluation marks (`mevisit_user`, historical source users) are not recorded by the web.
 */
final class MonitoringVisitService
{
    use RetiresRecords;

    /** Phase task per visit number (`TasksOfProjects`: 26–30 for visits 1–5). */
    private const TASKS = [1 => 26, 2 => 27, 3 => 28, 4 => 29, 5 => 30];

    public function __construct(
        private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private LifecycleService $lifecycle, private VisitService $visits, private ProjectNotices $projectNotices,
    ) {}

    private function authorize(WebAccount $account, int $city, bool $write = false, ?string $extra = null): void
    {
        foreach (['applications.view', 'me_visits.view', ...($write ? ['me_visits.manage'] : []), ...($extra === null ? [] : [$extra])] as $capability) {
            if (! $this->access->check($account, $city, $capability)) {
                $this->audit->record($account->id, 'access.denied', 'denied');
                abort(403);
            }
        }
    }

    private function project(int $id): object
    {
        $row = $this->tables->query('microproject')->where('p.id', $id)->first();
        abort_if($row === null, 404);

        return $row;
    }

    private function fail(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('field.errors.'.$key)]);
    }

    /** People linked to the project with their link type, beneficiary first. */
    private function people(object $project): Collection
    {
        $projectRef = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');
        $links = $this->tables->query('person_microproject')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$projectRef['resolved_column'], $project->id)->where('p.'.$personRef['state_column'], 'resolved')->orderBy('p.id')->get();
        $people = $links->isEmpty() ? collect() : $this->tables->query('person')->whereIn('p.id', $links->pluck($personRef['resolved_column'])->unique()->all())->get()->keyBy('id');

        return $links->map(function ($link) use ($people, $personRef) {
            $person = $people->get((int) $link->{$personRef['resolved_column']});

            return $person === null ? null : (object) [
                'id' => (int) $person->id, 'type' => (string) ($link->c_PersonType ?? ''),
                'name' => trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? '').' ابن/ة '.($person->c_P_FatherName ?? '')),
            ];
        })->filter()->unique('id')->sortBy(fn ($p) => $p->type === 'مستفيد' ? 0 : 1)->values();
    }

    private function visitRows(Collection $people, bool $deleted = false): Collection
    {
        if ($people->isEmpty()) {
            return collect();
        }
        $ref = $this->tables->relation('mevisit', 'Person_ID');

        return $this->tables->query('mevisit', $deleted)->whereIn('p.'.$ref['resolved_column'], $people->pluck('id')->all())->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function answerRows(Collection $visits): Collection
    {
        if ($visits->isEmpty()) {
            return collect();
        }
        $ref = $this->tables->relation('mevisit_answer', 'Visit_ID');

        return $this->tables->query('mevisit_answer')->whereIn('p.'.$ref['resolved_column'], $visits->pluck('id')->all())->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function personOf(object $visit): int
    {
        return (int) $visit->{$this->tables->relation('mevisit', 'Person_ID')['resolved_column']};
    }

    private function version(Collection $visits, Collection $answers, object $project): string
    {
        $strip = fn (Collection $rows) => $rows->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->values()->all();

        return hash('sha256', json_encode([$strip($visits), $strip($answers), $project->id], JSON_THROW_ON_ERROR));
    }

    /** Catalog questions of a kind with their answers: [{question, answers: [{id, name, mark}]}]; only questions that have answers are asked. */
    private function questionnaire(object $project, string $kind): Collection
    {
        $city = (int) $project->city_id;
        $source = (int) $project->source_id;
        $ref = $this->tables->relation('meanswer', 'Question_ID');
        $answers = $this->tables->catalog('meanswer', $city, $source)->filter(fn ($a) => $a->{$ref['state_column']} === 'resolved')->groupBy(fn ($a) => (int) $a->{$ref['resolved_column']});

        return $this->tables->catalog('mequestion', $city, $source)->filter(fn ($q) => str_contains(mb_strtolower((string) ($q->c_Type ?? '')), $kind))
            ->sortBy(fn ($q) => (int) ($q->c_ID ?? $q->id))->map(fn ($q) => (object) [
                'id' => (int) $q->id, 'name' => (string) ($q->c_Name ?? ''),
                'answers' => ($answers->get((int) $q->id) ?? collect())->sortBy(fn ($a) => (int) ($a->c_ID ?? $a->id))->map(fn ($a) => (object) ['id' => (int) $a->id, 'name' => (string) ($a->c_Name ?? ''), 'mark' => (int) ($a->c_Mark ?? 0)])->values(),
            ])->filter(fn ($q) => $q->answers->isNotEmpty())->values();
    }

    private function funded(object $project): bool
    {
        $current = $this->lifecycle->current($this->lifecycle->history($project));

        return $current !== null && in_array($current->state_name_en, FinanceService::FUNDED, true);
    }

    /** The project's M&E overview: people, their visits and who can still get a new one. */
    public function hub(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $people = $this->people($project);
        $visits = $this->visitRows($people);
        $class = $this->visits->classify($project);
        $canManage = $this->access->check($account, $city, 'me_visits.manage');
        $creatable = $canManage && ! $class['rehab'] && $this->funded($project);
        $this->audit->record($account->id, 'me_visits.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        $canRestore = $this->mayRestore($account, $city, 'me_visits.delete');

        return [
            'canRestore' => $canRestore, 'deleted' => $canRestore ? $this->visitRows($people, true)->sortBy(fn ($v) => (int) $v->c_Number)->values() : collect(),
            'project' => $project, 'version' => $this->version($visits, $this->answerRows($visits), $project), 'rehab' => $class['rehab'], 'funded' => $this->funded($project), 'creatable' => $creatable, 'canManage' => $canManage,
            'people' => $people->map(function ($person) use ($visits) {
                $person->visits = $visits->filter(fn ($v) => $this->personOf($v) === $person->id)->sortBy(fn ($v) => (int) $v->c_Number)->values();
                $person->next = 1 + (int) $person->visits->map(fn ($v) => (int) $v->c_Number)->max();

                return $person;
            }),
        ];
    }

    /** A blank form for a person ($visitId null) or an existing visit. */
    public function form(WebAccount $account, int $projectId, ?int $personId, ?int $visitId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $people = $this->people($project);
        $visits = $this->visitRows($people);
        $answers = $this->answerRows($visits);
        $visit = $visitId === null ? null : $visits->firstWhere('id', $visitId);
        abort_if($visitId !== null && $visit === null, 404);
        $personId ??= $this->personOf($visit);
        $person = $people->firstWhere('id', $personId);
        abort_if($person === null, 404);
        $canManage = $this->access->check($account, $city, 'me_visits.manage');
        $class = $this->visits->classify($project);
        if ($visit === null) {
            abort_unless($canManage && ! $class['rehab'] && $this->funded($project), 404);
        }
        $kind = $visit !== null ? (string) $visit->c_Kind : ($class['vehicle'] ? 'v' : 'o');
        $existing = $visit === null ? collect() : $answers->filter(fn ($a) => (int) $a->{$this->tables->relation('mevisit_answer', 'Visit_ID')['resolved_column']} === (int) $visit->id);
        $this->audit->record($account->id, 'me_visits.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        return [
            'project' => $project, 'person' => $person, 'visit' => $visit, 'kind' => $kind, 'questions' => $this->questionnaire($project, $kind),
            'given' => $existing->keyBy(fn ($a) => (int) $a->{$this->tables->relation('mevisit_answer', 'Question_ID')['resolved_column']}),
            'answerRef' => $this->tables->relation('mevisit_answer', 'Answer_ID')['resolved_column'],
            'next' => 1 + (int) $visits->filter(fn ($v) => $this->personOf($v) === $personId)->map(fn ($v) => (int) $v->c_Number)->max(),
            'canManage' => $canManage, 'canDelete' => $visit !== null && $this->mayRetire($account, $city, 'me_visits.delete', $visit),
            'version' => $this->version($visits, $answers, $project), 'mark' => $visit === null ? null : (int) ($visit->c_Result ?? 0),
        ];
    }

    /**
     * Records or edits a visit. $input: number, date, profit, ans1, indicators, notes, a{questionId} (answer id), n{questionId} (note).
     * Returns the visit id.
     */
    public function save(WebAccount $account, int $projectId, int $personId, ?int $visitId, array $input, string $version): int
    {
        return DB::transaction(function () use ($account, $projectId, $personId, $visitId, $input, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            $people = $this->people($project);
            $visits = $this->visitRows($people);
            $answers = $this->answerRows($visits);
            abort_unless(hash_equals($this->version($visits, $answers, $project), $version), 409, 'Visits changed. Reload before saving.');
            abort_if($people->firstWhere('id', $personId) === null, 404);
            $visit = $visitId === null ? null : $visits->firstWhere('id', $visitId);
            abort_if($visitId !== null && ($visit === null || $this->personOf($visit) !== $personId), 404);
            $class = $this->visits->classify($project);
            if ($visit === null) {
                $class['rehab'] && $this->fail('visit', 'rehab');
                $this->funded($project) || $this->fail('visit', 'not_funded');
            }
            $kind = $visit !== null ? (string) $visit->c_Kind : ($class['vehicle'] ? 'v' : 'o');
            $actor = mb_substr((string) $account->username, 0, 50);

            $payload = $this->payload($visit, $visits, $personId, $kind, $input);
            $chosen = $this->chosenAnswers($project, $kind, $input);

            if ($visit === null) {
                $visitId = $this->tables->insert('mevisit', $city, $source, $payload + ['c_Kind' => $kind, 'c_Type' => '', 'c_Result' => 0, 'c_Created_By' => $actor, 'c_Ans2' => '']
                    + $this->tables->link('mevisit', 'Person_ID', $personId));
                $number = (int) $payload['c_Number'];
                if (isset(self::TASKS[$number])) {
                    $this->completeTask($project, self::TASKS[$number], 1);
                }
                // The notice of this visit is done. Visit 4 stands for the schedule's MFinal (the desktop only cleared M4).
                $letter = strtoupper($kind);
                $name = (string) ($people->firstWhere('id', $personId)->name ?? '');
                foreach ($number === 4 ? ['M4', 'MFinal'] : ['M'.$number] as $label) {
                    $this->projectNotices->visitDone($project, 'Visit '.$label.'-'.$letter, $name);
                }
            } else {
                $visitId = (int) $visit->id;
                if ($payload !== []) {
                    DB::table('op_mevisit')->where('id', $visitId)->update($payload + ['c_Edited_By' => $actor]);
                }
            }
            $this->writeAnswers($project, $visitId, $chosen);
            $profit = array_key_exists('c_Profit', $payload) ? (string) $payload['c_Profit'] : Money::canonical($visit->c_Profit ?? null) ?? '0';
            $result = $this->result($project, $visitId, $profit);
            if ($visit === null || (int) ($visit->c_Result ?? 0) !== $result || $visit->c_Result === null) {
                DB::table('op_mevisit')->where('id', $visitId)->update(['c_Result' => $result]);
            }
            $this->audit->record($account->id, 'me_visits.saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return $visitId;
        });
    }

    private function payload(?object $visit, Collection $visits, int $personId, string $kind, array $input): array
    {
        $create = $visit === null;
        $payload = [];
        $text = fn (string $key) => Fields::text($input[$key] ?? '');

        $number = trim($text('number'));
        if ($create || $number !== (string) ($visit->c_Number ?? '')) {
            (preg_match('/\A[1-9]\d?\z/', $number) === 1) || $this->fail('number', 'me_number');
            $visits->contains(fn ($v) => $this->personOf($v) === $personId && (string) $v->c_Kind === $kind && (string) $v->c_Number === $number && (int) $v->id !== (int) ($visit->id ?? 0))
                && $this->fail('number', 'me_number_taken');
            $payload['c_Number'] = $number;
        }
        $date = trim($text('date'));
        if ($create && $date === '') {
            $date = now()->toDateString();
        }
        if ($create || $date !== Fields::day($visit->c_Date ?? null)) {
            Fields::isDate($date) || $this->fail('date', 'date');
            $payload['c_Date'] = $date;
        }
        $profit = Money::normalize($text('profit'));
        $stored = $create ? null : Money::canonical($visit->c_Profit ?? null);
        if ($create || $stored === null || Money::canonical($profit) !== $stored) {
            ($profit !== '' && preg_match('/\A-?\d{1,12}(?:\.\d{1,4})?\z/', $profit) === 1) || $this->fail('profit', 'me_profit');
            $payload['c_Profit'] = Money::canonical($profit) ?? '0';
        }
        foreach (['ans1' => 'c_Ans1', 'indicators' => 'c_Indicators', 'notes' => 'c_Notes'] as $key => $column) {
            $value = $text($key);
            if ($create || $value !== (string) ($visit->{$column} ?? '')) {
                mb_strlen($value) <= 65535 || $this->fail($key, 'long');
                $payload[$column] = $value;
            }
        }

        return $payload;
    }

    /**
     * Validated answers as [questionId => [answerId, note]]. Every catalog question of the kind must be answered on every save, as the
     * desktop requires; stored answers of questions outside the catalog are never touched.
     */
    private function chosenAnswers(object $project, string $kind, array $input): array
    {
        $chosen = [];
        foreach ($this->questionnaire($project, $kind) as $question) {
            $raw = $input['a'.$question->id] ?? null;
            Fields::blank($raw) && $this->fail('answers', 'me_unanswered');
            $answer = $question->answers->firstWhere('id', (int) $raw);
            $answer !== null || $this->fail('a'.$question->id, 'choice');
            $note = Fields::text($input['n'.$question->id] ?? '');
            mb_strlen($note) <= 65535 || $this->fail('n'.$question->id, 'long');
            $chosen[$question->id] = [$answer->id, $note];
        }

        return $chosen;
    }

    /** Inserts missing answers and updates changed ones; stored answers of questions outside the catalog are left alone. */
    private function writeAnswers(object $project, int $visitId, array $chosen): void
    {
        $visitRef = $this->tables->relation('mevisit_answer', 'Visit_ID')['resolved_column'];
        $questionRef = $this->tables->relation('mevisit_answer', 'Question_ID')['resolved_column'];
        $answerRef = $this->tables->relation('mevisit_answer', 'Answer_ID')['resolved_column'];
        $stored = DB::table('op_mevisit_answer')->where($visitRef, $visitId)->get()->keyBy($questionRef);
        foreach ($chosen as $questionId => [$answerId, $note]) {
            $row = $stored->get($questionId);
            if ($row === null) {
                $this->tables->insert('mevisit_answer', (int) $project->city_id, (int) $project->source_id, ['c_Notes' => $note]
                    + $this->tables->link('mevisit_answer', 'Visit_ID', $visitId) + $this->tables->link('mevisit_answer', 'Question_ID', $questionId) + $this->tables->link('mevisit_answer', 'Answer_ID', $answerId));
            } elseif ((int) $row->{$answerRef} !== $answerId || (string) ($row->c_Notes ?? '') !== $note) {
                DB::table('op_mevisit_answer')->where('id', $row->id)->update(['c_Notes' => $note] + $this->tables->link('mevisit_answer', 'Answer_ID', $answerId));
            }
        }
    }

    /** Sum of the marks of the visit's answers plus the mark of the active profit range (`Get_Mark_Of_Profit`). */
    private function result(object $project, int $visitId, string $profit): int
    {
        $marks = $this->tables->catalog('meanswer', (int) $project->city_id, (int) $project->source_id)->keyBy('id');
        $visitRef = $this->tables->relation('mevisit_answer', 'Visit_ID')['resolved_column'];
        $answerRef = $this->tables->relation('mevisit_answer', 'Answer_ID')['resolved_column'];
        $sum = 0;
        foreach (DB::table('op_mevisit_answer')->where($visitRef, $visitId)->get() as $row) {
            $sum += (int) ($marks->get((int) $row->{$answerRef})->c_Mark ?? 0);
        }
        $range = $this->tables->catalog('meprofitmarks', (int) $project->city_id, (int) $project->source_id)
            ->first(fn ($r) => (int) ($r->c_IsActive ?? 0) === 1 && Money::compare((string) Money::canonical($r->c_Min ?? 0), $profit) <= 0 && Money::compare((string) Money::canonical($r->c_Max ?? 0), $profit) >= 0);
        $range !== null || $this->fail('profit', 'me_profit_range');

        return $sum + (int) $range->c_Mark;
    }

    /** Deletes a web-created visit with its answers and resets the phase task (visit numbers 1–5). */
    public function delete(WebAccount $account, int $projectId, int $visitId, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $visitId, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, false, 'me_visits.delete');
            $people = $this->people($project);
            $visits = $this->visitRows($people);
            $answers = $this->answerRows($visits);
            abort_unless(hash_equals($this->version($visits, $answers, $project), $version), 409, 'Visits changed. Reload before deleting.');
            $visit = $visits->firstWhere('id', $visitId);
            abort_if($visit === null, 404);
            $visitRef = $this->tables->relation('mevisit_answer', 'Visit_ID')['resolved_column'];
            $this->retire($account, $city, 'mevisit', $visit, function () use ($answers, $visitRef, $visitId) {
                foreach ($answers->filter(fn ($a) => (int) $a->{$visitRef} === $visitId) as $answer) {
                    $this->tables->isWeb($answer) || $this->fail('delete', 'imported');
                    $this->tables->deleteWeb('mevisit_answer', $answer);
                }
            });
            if (isset(self::TASKS[(int) $visit->c_Number])) {
                $this->completeTask($project, self::TASKS[(int) $visit->c_Number], 0);
            }
            $this->audit->record($account->id, 'me_visits.deleted', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    /** Makes a hidden imported M&E visit visible again. */
    public function restore(WebAccount $account, int $projectId, int $visitId): void
    {
        DB::transaction(function () use ($account, $projectId, $visitId) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, false, 'me_visits.delete');
            $this->requireImportedDelete($account, $city);
            abort_if($this->visitRows($this->people($project), true)->firstWhere('id', $visitId) === null, 404);
            $this->tables->unhide('mevisit', $visitId);
            $this->audit->record($account->id, 'me_visits.restored', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    private function completeTask(object $project, int $legacyTaskId, int $state): void
    {
        $task = $this->tables->catalog('task', (int) $project->city_id, (int) $project->source_id)->first(fn ($row) => (int) ($row->c_T_ID ?? 0) === $legacyTaskId);
        if ($task === null) {
            return;
        }
        $projectRef = $this->tables->relation('task_microproject', 'MicroProject_ID');
        $taskRef = $this->tables->relation('task_microproject', 'Task_ID');
        DB::table('op_task_microproject')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($projectRef['resolved_column'], $project->id)->where($taskRef['resolved_column'], $task->id)
            ->update(['c_State' => $state, 'c_Date' => now()->format('Y-m-d H:i:s')]);
    }
}
