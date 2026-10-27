<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Models\WebAccount;
use App\Services\AccessRoles;
use App\Services\Operational\OperationalTables;
use App\Services\SourceIdentity;
use Illuminate\Support\Facades\DB;

/**
 * Copies what the desktop kept on its `user` records into the web model of the single database, for the accounts whose reviewed link
 * names that user: roles and duties, e-mail, task responsibilities and the user's notices. It runs once per city after migration and may
 * be repeated: nothing is duplicated and nothing existing is changed. After it, the old city databases and the preserved `user` tables are
 * no longer read at runtime. An operator runs it deliberately (`php artisan staff:adopt-legacy`); it is not reachable from the web.
 */
final class LegacyStaffAdopter
{
    /** Desktop role id → web role (6, Out of Service, adopts nothing). */
    public const ROLES = [1 => 'administrator', 8 => 'administrator', 5 => 'administrator', 2 => 'data-entry', 3 => 'financial', 7 => 'legal', 9 => 'media', 4 => 'guest'];

    /** Desktop flag column → web duty role. */
    public const DUTIES = ['IsME' => 'monitoring', 'IsCommunication' => 'communication', 'IsCashier' => 'financial'];

    public function __construct(private SourceIdentity $identity, private AccessRoles $roles, private OperationalTables $tables) {}

    /**
     * @return array{accounts: int, roles: int, emails: int, tasks: int, notices: int, shared: int}
     */
    public function adopt(?int $city = null, string $decision = 'adopt:legacy'): array
    {
        $total = ['accounts' => 0, 'roles' => 0, 'emails' => 0, 'tasks' => 0, 'notices' => 0, 'shared' => 0];
        foreach (DB::table('sources')->when($city !== null, fn ($q) => $q->where('city_id', $city))->orderBy('id')->get(['id', 'city_id']) as $source) {
            foreach ($this->adoptSource((int) $source->id, (int) $source->city_id, $decision) as $key => $count) {
                $total[$key] += $count;
            }
        }

        return $total;
    }

    /** @return array{accounts: int, roles: int, emails: int, tasks: int, notices: int, shared: int} */
    private function adoptSource(int $source, int $city, string $decision): array
    {
        $result = ['accounts' => 0, 'roles' => 0, 'emails' => 0, 'tasks' => 0, 'notices' => 0, 'shared' => 0];
        $links = DB::table('account_legacy_links as l')->join('web_principals as wp', 'wp.id', '=', 'l.principal_id')->join('web_accounts as a', 'a.id', '=', 'l.account_id')
            ->where('l.source_id', $source)->whereNull('l.revoked_at')->whereNull('wp.disabled_at')->whereNull('a.disabled_at')->get(['l.account_id', 'wp.source_record_id']);
        if ($links->isEmpty()) {
            return $result;
        }
        $accountOf = $links->pluck('account_id', 'source_record_id')->map(fn ($id) => (int) $id)->all();
        $users = $this->identity->publishedUsers($source)->whereIn('r.id', array_keys($accountOf))
            ->get(['r.id as record', 'u.UserRoleID', 'u.IsME', 'u.IsCommunication', 'u.IsCashier', 'u.Email'])->keyBy('record');

        DB::transaction(function () use (&$result, $source, $city, $decision, $accountOf, $users) {
            foreach ($accountOf as $record => $accountId) {
                $user = $users->get($record);
                if ($user === null) {
                    continue;
                }
                $account = WebAccount::findOrFail($accountId);
                $result['accounts']++;
                $wanted = array_filter([self::ROLES[(int) $user->UserRoleID] ?? null, ...array_values(array_filter(self::DUTIES, fn ($role, $flag) => (int) ($user->{$flag} ?? 0) === 1, ARRAY_FILTER_USE_BOTH))]);
                foreach (array_unique($wanted) as $role) {
                    if (! in_array($role, $this->roles->roles($account, $city), true)) {
                        $this->roles->assign($account, $city, $role, $decision);
                        $result['roles']++;
                    }
                }
                $email = mb_strtolower(trim((string) ($user->Email ?? '')));
                if ($email !== '' && mb_strlen($email) <= 190 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
                    && DB::table('web_accounts')->where('id', $accountId)->whereNull('email')->exists() && DB::table('web_accounts')->where('email', $email)->doesntExist()) {
                    DB::table('web_accounts')->where('id', $accountId)->update(['email' => $email]);
                    $result['emails']++;
                }
            }
            $result['tasks'] = $this->tasks($source, $city, $accountOf);
            [$result['notices'], $result['shared']] = $this->notices($source, $city, $accountOf);
        });

        return $result;
    }

    /** `task_user` rows of linked users become responsibilities of their accounts. */
    private function tasks(int $source, int $city, array $accountOf): int
    {
        $taskRef = $this->tables->relation('task_user', 'Task_ID');
        $userRef = $this->tables->relation('task_user', 'User_ID');
        $added = 0;
        $rows = $this->tables->query('task_user')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.'.$taskRef['state_column'], 'resolved')->where('p.'.$userRef['state_column'], 'resolved')
            ->whereIn('p.'.$userRef['resolved_column'], array_keys($accountOf))->get();
        foreach ($rows as $row) {
            $accountId = $accountOf[(int) $row->{$userRef['resolved_column']}] ?? null;
            $task = (int) $row->{$taskRef['resolved_column']};
            if ($accountId !== null && DB::table('account_city_tasks')->where('account_id', $accountId)->where('task_id', $task)->doesntExist()) {
                DB::table('account_city_tasks')->insert(['account_id' => $accountId, 'city_id' => $city, 'source_id' => $source, 'task_id' => $task, 'assigned_at' => now('UTC')->format('Y-m-d H:i:s.u'), 'revoked_at' => null]);
                $added++;
            }
        }

        return $added;
    }

    /**
     * The notices addressed to linked users, and the shared visit and clearance notices (copied once per city), into `web_notices`.
     *
     * @return array{0: int, 1: int} personal and shared copies made
     */
    private function notices(int $source, int $city, array $accountOf): array
    {
        $projectRef = $this->tables->relation('user_notification', 'MicroProject_ID');
        $userRef = $this->tables->relation('user_notification', 'User_ID');
        $senderRef = $this->tables->relation('user_notification', 'Sender_ID');
        $personal = 0;
        $shared = 0;
        $rows = $this->tables->query('user_notification')->where('p.city_id', $city)->where('p.source_id', $source)->where(function ($q) use ($userRef, $accountOf) {
            $q->where(fn ($mine) => $mine->where('p.'.$userRef['state_column'], 'resolved')->whereIn('p.'.$userRef['resolved_column'], array_keys($accountOf)))
                ->orWhere(fn ($open) => $open->where('p.'.$userRef['state_column'], '<>', 'resolved')->where('p.c_Pay_ID', -21));
        })->orderBy('p.id')->get();
        foreach ($rows as $row) {
            $payId = (int) ($row->c_Pay_ID ?? 0);
            $body = (string) ($row->c_Body ?? '');
            $account = $row->{$userRef['state_column']} === 'resolved' ? ($accountOf[(int) $row->{$userRef['resolved_column']}] ?? null) : null;
            $kind = match (true) {
                $payId === -21 => str_starts_with($body, 'براءة الذمة') ? NoticeService::CLEARANCE : NoticeService::VISIT,
                $payId === -3 => NoticeService::PURCHASES,
                $payId === -10 => NoticeService::TROPHY,
                $payId === -1199 => NoticeService::EXECUTIVE_FILE,
                $payId <= -998 => NoticeService::STATE,
                $payId > 0 => NoticeService::PAYMENT,
                default => NoticeService::MESSAGE,
            };
            if ($account === null && ! in_array($kind, NoticeService::SHARED, true)) {
                continue;
            }
            $project = $row->{$projectRef['state_column']} === 'resolved' ? (int) $row->{$projectRef['resolved_column']} : null;
            $values = [
                'city_id' => $city, 'source_id' => $source, 'account_id' => $account, 'project_id' => $project, 'kind' => $kind, 'notice_date' => substr((string) ($row->c_Date ?? now()->toDateString()), 0, 10),
                'title' => (string) ($row->c_P_Name ?? ''), 'body' => $body,
            ];
            $query = DB::table('web_notices');
            foreach ($values as $column => $value) {
                $value === null ? $query->whereNull($column) : $query->where($column, $value);
            }
            if ($query->exists()) {
                continue;
            }
            $sender = $row->{$senderRef['state_column']} === 'resolved' ? ($accountOf[(int) $row->{$senderRef['resolved_column']}] ?? null) : null;
            DB::table('web_notices')->insert($values + [
                'sender_account_id' => $sender, 'project_number' => $row->c_MicroProject_ID ?? null, 'pay_key' => $kind === NoticeService::PAYMENT ? $payId : null, 'original_date' => null,
                'note' => $row->c_Note ?? null, 'change_reason' => null, 'seen' => (int) ($row->c_Seen ?? 0) === 1 ? 1 : 0, 'created_at' => now('UTC')->format('Y-m-d H:i:s.u'),
            ]);
            $account === null ? $shared++ : $personal++;
        }

        return [$personal, $shared];
    }
}
