<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\Fields;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Operational\OperationalTables;
use App\Services\Staff\ProjectNotices;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The closing visit of a grant-funded rehabilitation project (`VClosing_Rehab_Form`, `Classes/VisitRehab.cs`): a visit of kind `R-cl`
 * plus a `rehabvisit` row of fifteen answers, each a stored choice value and a free-text note.
 * The desktop's edit path never saved the answers (its SQL string was overwritten before execution); the web saves edits properly.
 * The visitors (`visit_user`, historical source users) are not recorded by the web, so the desktop's "at least one visitor" rule is dropped.
 */
final class RehabVisitService
{
    use RetiresRecords;

    public const KIND = 'R-cl';

    public const CLOSING_TASK = 31;

    /** Question slots: ratings 0/1/2, ratings that may be "not applicable" (stored -1), and yes/no where "yes" is stored as 0. */
    public const RATING = 'rating';

    public const RATING_NA = 'rating_na';

    public const YES_NO = 'yes_no';

    public const QUESTIONS = [
        1 => self::RATING, 2 => self::RATING, 3 => self::RATING, 4 => self::RATING,
        5 => self::RATING_NA, 6 => self::RATING_NA, 7 => self::RATING_NA, 8 => self::RATING_NA,
        9 => self::RATING_NA, 10 => self::RATING_NA, 11 => self::RATING_NA, 12 => self::RATING_NA,
        13 => self::YES_NO, 14 => self::YES_NO, 15 => self::YES_NO,
    ];

    /** Stored value => language key under `field.rehab.choice`. */
    public const DOMAINS = [
        self::RATING => [0 => 'bad', 1 => 'acceptable', 2 => 'excellent'],
        self::RATING_NA => [0 => 'bad', 1 => 'acceptable', 2 => 'excellent', -1 => 'na'],
        self::YES_NO => [0 => 'yes', 1 => 'no'],
    ];

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private LifecycleService $lifecycle, private VisitService $visits, private ProjectNotices $projectNotices) {}

    private function authorize(WebAccount $account, int $city, bool $write = false, ?string $extra = null): void
    {
        foreach (['applications.view', 'visits.view', ...($write ? ['visits.manage'] : []), ...($extra === null ? [] : [$extra])] as $capability) {
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

    private function visit(object $project): ?object
    {
        $ref = $this->tables->relation('visit', 'MicroProject_ID');

        return $this->tables->query('visit')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$ref['resolved_column'], $project->id)->where('p.'.$ref['state_column'], 'resolved')->where('p.c_Kind', self::KIND)->orderBy('p.id')->first();
    }

    private function answers(?object $visit): ?object
    {
        if ($visit === null) {
            return null;
        }
        $ref = $this->tables->relation('rehabvisit', 'Visit_ID');

        return $this->tables->query('rehabvisit')->where('p.'.$ref['resolved_column'], $visit->id)->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->first();
    }

    private function version(?object $visit, ?object $answers, object $project): string
    {
        $strip = fn (?object $row) => $row === null ? null : collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all();

        return hash('sha256', json_encode([$strip($visit), $strip($answers), $project->id], JSON_THROW_ON_ERROR));
    }

    public function form(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $visit = $this->visit($project);
        $answers = $this->answers($visit);
        $class = $this->visits->classify($project);
        $canManage = $this->access->check($account, $city, 'visits.manage');
        abort_if($visit === null && (! $class['rehab'] || ! $canManage), 404);
        $this->audit->record($account->id, 'visits.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        return [
            'project' => $project, 'visit' => $visit, 'answers' => $answers, 'canManage' => $canManage,
            'canDelete' => $visit !== null && $this->mayRetire($account, $city, 'visits.delete', $visit), 'version' => $this->version($visit, $answers, $project),
        ];
    }

    /** $input: date, q{n}_value ('na' or a stored number), q{n}_text for n in 1–15. Returns the visit id. */
    public function save(WebAccount $account, int $projectId, array $input, string $version): int
    {
        return DB::transaction(function () use ($account, $projectId, $input, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            $visit = $this->visit($project);
            $answers = $this->answers($visit);
            abort_unless(hash_equals($this->version($visit, $answers, $project), $version), 409, 'The rehabilitation visit changed. Reload before saving.');
            if ($visit === null) {
                $this->visits->classify($project)['rehab'] || $this->fail('visit', 'not_rehab');
            }
            $actor = mb_substr((string) $account->username, 0, 50);
            $create = $visit === null;

            $date = trim(Fields::text($input['date'] ?? ''));
            if ($create && $date === '') {
                $date = now()->toDateString();
            }
            $visitPayload = [];
            if ($create || $date !== Fields::day($visit->c_Date ?? null)) {
                Fields::isDate($date) || $this->fail('date', 'date');
                $visitPayload['c_Date'] = $date;
            }
            $answerPayload = [];
            foreach (self::QUESTIONS as $n => $type) {
                $value = $this->value($input, $n, $type);
                $text = Fields::text($input['q'.$n.'_text'] ?? '');
                mb_strlen($text) <= 65535 || $this->fail('q'.$n.'_text', 'long');
                if ($create || $value !== (int) ($answers->{'c_Ans'.$n.'_Value'} ?? -1)) {
                    $answerPayload['c_Ans'.$n.'_Value'] = $value;
                }
                if ($create || $text !== (string) ($answers->{'c_Ans'.$n} ?? '')) {
                    $answerPayload['c_Ans'.$n] = $text;
                }
            }

            if ($create) {
                $visitId = $this->tables->insert('visit', $city, $source, $visitPayload + ['c_Kind' => self::KIND, 'c_Type' => '', 'c_Created_By' => $actor] + $this->tables->link('visit', 'MicroProject_ID', $projectId));
                $this->tables->insert('rehabvisit', $city, $source, $answerPayload + $this->tables->link('rehabvisit', 'Visit_ID', $visitId));
            } else {
                $visitId = (int) $visit->id;
                if ($visitPayload !== [] || $answerPayload !== []) {
                    DB::table('op_visit')->where('id', $visitId)->update($visitPayload + ['c_Edited_By' => $actor]);
                }
                if ($answerPayload !== []) {
                    $answers === null
                        ? $this->tables->insert('rehabvisit', $city, $source, $answerPayload + $this->tables->link('rehabvisit', 'Visit_ID', $visitId))
                        : DB::table('op_rehabvisit')->where('id', $answers->id)->update($answerPayload);
                }
            }
            $this->completeClosingTask($project);
            $this->lifecycle->enterNamed($project, LifecycleService::FINISHED);
            $this->projectNotices->clearProject($project);
            $this->audit->record($account->id, 'visits.saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return $visitId;
        });
    }

    /** The submitted choice of question $n; every question must be answered, as the desktop required. */
    private function value(array $input, int $n, string $type): int
    {
        $raw = $input['q'.$n.'_value'] ?? null;
        Fields::blank($raw) && $this->fail('q'.$n.'_value', 'rehab_unanswered');
        $raw = is_string($raw) || is_int($raw) ? (string) $raw : '';
        if ($raw === 'na' && $type === self::RATING_NA) {
            return -1;
        }
        (preg_match('/\A\d\z/', $raw) === 1 && array_key_exists((int) $raw, self::DOMAINS[$type])) || $this->fail('q'.$n.'_value', 'choice');

        return (int) $raw;
    }

    /** Deletes a visit created in the web with its answers. As in the desktop, the closing task and project state are left as they are. */
    public function delete(WebAccount $account, int $projectId, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, false, 'visits.delete');
            $visit = $this->visit($project);
            $answers = $this->answers($visit);
            abort_unless(hash_equals($this->version($visit, $answers, $project), $version), 409, 'The rehabilitation visit changed. Reload before deleting.');
            abort_if($visit === null, 404);
            $this->retire($account, $city, 'visit', $visit, function () use ($answers) {
                if ($answers !== null) {
                    $this->tables->isWeb($answers) || $this->fail('delete', 'imported');
                    $this->tables->deleteWeb('rehabvisit', $answers);
                }
            });
            $this->audit->record($account->id, 'visits.deleted', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    /** `update task_microproject set State = 1 ... Task_ID = 31`: only an existing assignment is touched. */
    private function completeClosingTask(object $project): void
    {
        $task = $this->tables->catalog('task', (int) $project->city_id, (int) $project->source_id)->first(fn ($row) => (int) ($row->c_T_ID ?? 0) === self::CLOSING_TASK);
        if ($task === null) {
            return;
        }
        $projectRef = $this->tables->relation('task_microproject', 'MicroProject_ID');
        $taskRef = $this->tables->relation('task_microproject', 'Task_ID');
        DB::table('op_task_microproject')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($projectRef['resolved_column'], $project->id)->where($taskRef['resolved_column'], $task->id)
            ->update(['c_State' => 1, 'c_Date' => now()->format('Y-m-d H:i:s')]);
    }
}
