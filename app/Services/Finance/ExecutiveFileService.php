<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Operational\OperationalTables;
use App\Services\Staff\NotificationService;
use App\Services\Staff\ProjectNotices;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Executive (legal enforcement) files of a project (ExecutiveFiles_Form). Files are created and edited in place; deletion is not offered. */
final class ExecutiveFileService
{
    public const MAX_MONTHS = 100;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private ProjectNotices $projectNotices, private NotificationService $notices) {}

    private function authorize(WebAccount $account, int $city, bool $write = false): void
    {
        foreach (['applications.view', 'executive_files.view', ...($write ? ['executive_files.manage'] : [])] as $capability) {
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

    private function files(object $project): Collection
    {
        $ref = $this->tables->relation('exefile', 'MicroProject_ID');

        return $this->tables->query('exefile')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$ref['resolved_column'], $project->id)->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function version(Collection $files): string
    {
        return hash('sha256', json_encode($files->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->all(), JSON_THROW_ON_ERROR));
    }

    /** Current date plus months with .NET `AddMonths` semantics: the day is clamped to the end of the target month. */
    public static function expiry(?string $current, mixed $months): ?string
    {
        $day = Fields::day($current);
        if (! Fields::isDate($day) || ! is_numeric($months)) {
            return null;
        }

        return CarbonImmutable::createFromFormat('!Y-m-d', $day)->addMonthsNoOverflow((int) $months)->toDateString();
    }

    public function show(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $files = $this->files($project);
        $canManage = $this->access->check($account, $city, 'executive_files.manage');
        $this->audit->record($account->id, 'executive_files.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        return [
            'project' => $project, 'files' => $files->map(function ($row) {
                $row = clone $row;
                $row->expires = self::expiry($row->c_ExeF_CurrentDate ?? null, $row->c_ExeF_NumOfMonths ?? null);

                return $row;
            }), 'canManage' => $canManage, 'canCreate' => $canManage && $files->isEmpty(), 'version' => $this->version($files),
        ];
    }

    /** $input: number, begin_date, current_date, impound_date, impound_type, months. A new file is only created for a project without one. */
    public function save(WebAccount $account, int $projectId, ?int $fileId, array $input, string $version): int
    {
        return DB::transaction(function () use ($account, $projectId, $fileId, $input, $version) {
            DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, true);
            $files = $this->files($project);
            abort_unless(hash_equals($this->version($files), $version), 409, 'Executive files changed. Reload before saving.');
            $file = null;
            if ($fileId === null) {
                abort_if($files->isNotEmpty(), 409, 'This project already has an executive file; open it to edit.');
            } else {
                $file = $files->firstWhere('id', $fileId);
                abort_if($file === null, 404);
            }
            $payload = $this->payload($file, $input);
            if ($file === null) {
                $fileId = $this->tables->insert('exefile', $city, (int) $project->source_id, $payload + $this->tables->link('exefile', 'MicroProject_ID', $projectId));
            } elseif ($payload !== []) {
                DB::table('op_exefile')->where('id', $file->id)->update($payload);
            }
            $stored = $this->files($project)->firstWhere('id', $fileId);
            $this->projectNotices->executiveFile($project, self::expiry($stored->c_ExeF_CurrentDate ?? null, $stored->c_ExeF_NumOfMonths ?? null), $file === null, $this->notices->senderFor($account, (int) $project->source_id));
            $this->audit->record($account->id, 'executive_files.saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return (int) $fileId;
        });
    }

    private function fail(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('finance.errors.'.$key)]);
    }

    private function payload(?object $file, array $input): array
    {
        $create = $file === null;
        $get = fn (string $key) => is_string($input[$key] ?? null) ? trim($input[$key]) : '';
        $payload = [];

        $number = $get('number');
        if ($create || $number !== (string) ($file->c_ExeF_No ?? '')) {
            ($number !== '' || ! $create) && mb_strlen($number) <= 50 || $this->fail('number', 'exe_number');
            $payload['c_ExeF_No'] = $number;
        }
        $begin = $get('begin_date');
        if ($create || $begin !== Fields::day($file->c_ExeF_BeginDate ?? null)) {
            Fields::isDate($begin) || $this->fail('begin_date', 'date');
            $payload['c_ExeF_BeginDate'] = $begin;
        }
        $current = $get('current_date');
        if ($create && $current === '') {
            // A new file starts with its current date equal to the begin date (ExeF_BeginDate_dateTimePicker_ValueChanged).
            $current = $begin;
        }
        if ($create || $current !== Fields::day($file->c_ExeF_CurrentDate ?? null)) {
            Fields::isDate($current) || $this->fail('current_date', 'date');
            $payload['c_ExeF_CurrentDate'] = $current;
        }
        $impound = $get('impound_date');
        if ($create || $impound !== Fields::day($file->c_ExeF_ImpoundDate ?? null)) {
            Fields::isDate($impound) || $this->fail('impound_date', 'date');
            $payload['c_ExeF_ImpoundDate'] = $impound;
        }
        $type = is_string($input['impound_type'] ?? null) ? $input['impound_type'] : '';
        if ($create || $type !== (string) ($file->c_ExeF_ImpoundType ?? '')) {
            mb_strlen($type) <= 100 || $this->fail('impound_type', 'exe_type');
            $payload['c_ExeF_ImpoundType'] = $type;
        }
        $months = $get('months') === '' ? '0' : $get('months');
        if ($create || $months !== (string) ($file->c_ExeF_NumOfMonths ?? '')) {
            (preg_match('/\A\d{1,3}\z/', $months) === 1 && (int) $months <= self::MAX_MONTHS) || $this->fail('months', 'exe_months');
            $payload['c_ExeF_NumOfMonths'] = (int) $months;
        }

        return $payload;
    }
}
