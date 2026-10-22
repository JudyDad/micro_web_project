<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Services\Operational\OperationalTables;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * The notices the desktop generates as a side effect of other work (Loans_Form, ExecutiveFiles_Form, AdminPanel_From and the visit
 * forms). Every method is called inside the caller's transaction, under the project row lock, and only reads or writes the project's
 * own rows. Nothing is delivered outside the application.
 */
final class ProjectNotices
{
    /** The notice kinds the desktop stores in `Pay_ID` besides a payment number. */
    public const CLEARANCE_TROPHY = -10;

    public const EXECUTIVE_FILE = -1199;

    public const STATE_CHANGE = -998;

    public function __construct(private OperationalTables $tables, private NotificationService $notices) {}

    /** "first last ابن/ة father" of the project's beneficiary (the loan form's person name), or ''. */
    public function beneficiary(object $project): string
    {
        $projectRef = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');
        $links = $this->tables->query('person_microproject')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$projectRef['resolved_column'], $project->id)->where('p.'.$projectRef['state_column'], 'resolved')->where('p.'.$personRef['state_column'], 'resolved')->orderBy('p.id')->get();
        $link = $links->first(fn ($row) => ($row->c_PersonType ?? '') === 'مستفيد') ?? $links->first();
        $person = $link === null ? null : $this->tables->query('person')->where('p.id', $link->{$personRef['resolved_column']})->first();

        return $person === null ? '' : trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? '').' ابن/ة '.($person->c_P_FatherName ?? ''));
    }

    /** Whether the project has any visit notice, seen or not (`Has_Notifications(project, …, -21)`). */
    public function hasVisitNotices(object $project): bool
    {
        return $this->projectNotices($project)->where('c_Pay_ID', NotificationService::VISIT)->exists();
    }

    /**
     * Visit notices of a new loan (`Loans_Form.generate_me_*_visits_notifications`), once per project: none is created when the project
     * already has any, or when the city has no monitoring-and-evaluation staff (the desktop refused with a message). The notices have
     * no recipient and carry the kind letter V (vehicle sub-category 1 or 2) or O. Returns the number created.
     */
    public function generateVisitSchedule(object $project, string $loanDate, ?object $sender): int
    {
        if ($this->hasVisitNotices($project) || $this->notices->recipients((int) $project->source_id, 'me')->isEmpty()) {
            return 0;
        }
        $type = (string) ($this->linked($project, 'MP_Type_ID', 'microprojecttype')->c_Name ?? '');
        $fund = (string) ($this->linked($project, 'MP_FundType_ID', 'fundtype')->c_Name ?? '');
        $sub = $this->linked($project, 'SubCategory_ID', 'subcategory');
        $letter = in_array((int) ($sub !== null ? $sub->c_ID : ($project->c_SubCategory_ID ?? null)), [1, 2], true) ? 'V' : 'O';
        $opening = CarbonImmutable::parse($loanDate)->startOfDay()->addDays(5);
        $kind = match (true) {
            str_contains($type, 'زراع') => 'agricultural',
            str_contains($type, 'طاقة شمسية') => 'solar',
            str_contains($type, 'ترميم') && str_contains($fund, 'منحة') => 'rehabilitation',
            default => 'standard',
        };
        $suffix = $kind === 'solar' ? ' (Solar)' : '';
        $visits = match ($kind) {
            'rehabilitation' => ['Visit Closing-R' => $opening],
            'agricultural' => ['Visit Opening-'.$letter => $opening] + $this->agriculturalVisits($opening, $letter),
            'solar' => ['Visit Opening-'.$letter.$suffix => $opening] + $this->sixMonthly($opening, ['M1', 'M2'], $letter.$suffix),
            default => ['Visit Opening-'.$letter => $opening] + $this->sixMonthly($opening, ['M1', 'M2', 'M3', 'MFinal'], $letter),
        };
        $name = $this->beneficiary($project);
        foreach ($visits as $body => $date) {
            $this->notices->add((int) $project->city_id, (int) $project->source_id, $project, $date->toDateString(), $body, $name, NotificationService::VISIT, null, $sender);
        }

        return count($visits);
    }

    /**
     * Visits six months apart, each counted from the previous one (so a month-end clamp carries on, as the desktop's chained `AddMonths`).
     *
     * @param  list<string>  $labels
     * @return array<string, CarbonImmutable>
     */
    private function sixMonthly(CarbonImmutable $from, array $labels, string $suffix): array
    {
        $visits = [];
        foreach ($labels as $label) {
            $from = $from->addMonthsNoOverflow(6);
            $visits['Visit '.$label.'-'.$suffix] = $from;
        }

        return $visits;
    }

    /**
     * M1–M5 of an agricultural project: the first M&E visit falls in the April or November nearest two months after the opening, then they
     * alternate seven and five months apart (the desktop's calendar, with .NET month arithmetic).
     *
     * @return array<string, CarbonImmutable>
     */
    private function agriculturalVisits(CarbonImmutable $opening, string $letter): array
    {
        $reference = $opening->addMonthsNoOverflow(2);
        $day = $reference->day === 31 ? 30 : $reference->day;
        if ($reference->month < 4) {
            $first = CarbonImmutable::create($reference->year, 4, $day);
            $steps = [7, 5, 7, 5];
        } elseif ($reference->month < 11) {
            $first = CarbonImmutable::create($reference->year, 11, $day);
            $steps = [5, 7, 5, 7];
        } else {
            $first = CarbonImmutable::create($reference->year + 1, 4, $day);
            $steps = [7, 5, 7, 5];
        }
        $visits = ['Visit M1-'.$letter => $first];
        $date = $first;
        foreach ($steps as $index => $months) {
            $date = $date->addMonthsNoOverflow($months);
            $visits['Visit M'.($index + 2).'-'.$letter] = $date;
        }

        return $visits;
    }

    /**
     * The identifier a payment's notices carry in `Pay_ID`: the desktop's payment number for imported instalments, the instalment's
     * own identifier for instalments created in the web (they have no desktop number).
     */
    public static function paymentKey(object $payment): int
    {
        return (int) ($payment->c_Pay_ID ?? $payment->id);
    }

    /** One notice per financial user (role 3 or cashier) for a new unpaid instalment: dated its due date, the body is its amount. */
    public function paymentDue(object $project, object $payment, string $amount, string $due, ?object $sender): int
    {
        $name = $this->beneficiary($project);
        $count = 0;
        foreach ($this->notices->recipients((int) $project->source_id, 'financial') as $recipient) {
            $this->notices->add((int) $project->city_id, (int) $project->source_id, $project, $due, $amount, $name, self::paymentKey($payment), $recipient, $sender);
            $count++;
        }

        return $count;
    }

    /** `Update_NotificationWithPaymentID`: the instalment's notices follow its amount and due date, and are seen exactly when it is paid. */
    public function paymentChanged(object $project, object $payment, string $amount, string $due, bool $paid): int
    {
        return DB::table('op_user_notification')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where('c_Pay_ID', self::paymentKey($payment))->update(['c_Body' => $amount, 'c_Date' => $due, 'c_Seen' => $paid ? 1 : 0]);
    }

    /**
     * The clearance notice after the last instalment is paid (`Loans_Form.Send_Trophy_Notifications`): a shared notice the day after the last
     * payment and one trophy notice per communication user. Nothing is created when the project already has that clearance notice.
     */
    public function clearance(object $project, string $lastReceived, ?object $sender): bool
    {
        $date = CarbonImmutable::parse($lastReceived)->addDay()->toDateString();
        if ($this->projectNotices($project)->where('c_Pay_ID', NotificationService::VISIT)->where('c_Body', 'براءة الذمة (Clearance)')->where('c_Date', $date)->exists()) {
            return false;
        }
        $name = $this->beneficiary($project);
        $sub = $this->linked($project, 'SubCategory_ID', 'subcategory');
        $letter = in_array((int) ($sub !== null ? $sub->c_ID : ($project->c_SubCategory_ID ?? null)), [1, 2], true) ? 'V' : 'O';
        $this->notices->add((int) $project->city_id, (int) $project->source_id, $project, $date, 'براءة الذمة (Clearance)', $name, NotificationService::VISIT, null, $sender);
        foreach ($this->notices->recipients((int) $project->source_id, 'communication') as $recipient) {
            $this->notices->add((int) $project->city_id, (int) $project->source_id, $project, $date, 'Visit (Trophy)'.$letter, $name, self::CLEARANCE_TROPHY, $recipient, $sender);
        }

        return true;
    }

    /**
     * The executive-file expiry notice for the lawful users (role 7), dated the expiry date. On creation each lawful user gets one; on an
     * edit the existing notices follow the new date. (The desktop's edit matched `Pay_ID` against the file number while its creation stored
     * -1199, so its edit never changed anything; the web applies the evident intent.)
     */
    public function executiveFile(object $project, ?string $expiry, bool $created, ?object $sender): int
    {
        if ($expiry === null) {
            return 0;
        }
        if (! $created) {
            return $this->projectNotices($project)->where('c_Pay_ID', self::EXECUTIVE_FILE)->where('c_Body', 'الملف التنفيذي')->update(['c_Date' => $expiry, 'c_Sender_ID' => $sender?->UserID]);
        }
        $name = $this->beneficiary($project);
        $count = 0;
        foreach ($this->notices->recipients((int) $project->source_id, 'lawful') as $recipient) {
            $this->notices->add((int) $project->city_id, (int) $project->source_id, $project, $expiry, 'الملف التنفيذي', $name, self::EXECUTIVE_FILE, $recipient, $sender);
            $count++;
        }

        return $count;
    }

    /** `Update_UserNotification(project, -1)`: every notice of the project is marked seen, whoever it is addressed to. */
    public function clearProject(object $project): int
    {
        return $this->projectNotices($project)->update(['c_Seen' => 1]);
    }

    /** `Update_MicroUsers_Notification(project, "", body, name, -21, 1)`: the project's visit notices of one text are marked seen. */
    public function visitDone(object $project, string $body, ?string $name = null): int
    {
        $query = $this->projectNotices($project)->where('c_Pay_ID', NotificationService::VISIT)->where('c_Body', $body);
        if ($name !== null) {
            $query->where('c_P_Name', $name);
        }

        return $query->update(['c_Seen' => 1]);
    }

    /**
     * The state-change notice every active user receives (`AdminPanel_From`): "تم تبديل حالة المشروع إلى: <state>", `Pay_ID` -998 minus the
     * recipient's position. Not sent for Rejected or Waiting.
     */
    public function stateChanged(object $project, object $state, ?object $sender): int
    {
        if (in_array($state->c_Name_en ?? '', ['Rejected', 'Waiting'], true)) {
            return 0;
        }
        $name = $this->beneficiary($project);
        $body = 'تم تبديل حالة المشروع إلى: '.($state->c_Name_ar ?? $state->c_Name_en ?? '');
        $recipients = $this->notices->recipients((int) $project->source_id, 'staff')->values();
        foreach ($recipients as $index => $recipient) {
            $this->notices->add((int) $project->city_id, (int) $project->source_id, $project, now()->toDateString(), $body, $name, self::STATE_CHANGE - $index, $recipient, $sender);
        }

        return $recipients->count();
    }

    /** The project's rows of `op_user_notification`, whoever they address. */
    private function projectNotices(object $project): Builder
    {
        $ref = $this->tables->relation('user_notification', 'MicroProject_ID');

        return DB::table('op_user_notification')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($ref['resolved_column'], $project->id)->where($ref['state_column'], 'resolved');
    }

    private function linked(object $project, string $key, string $table): ?object
    {
        $ref = $this->tables->relation('microproject', $key);

        return $project->{$ref['state_column']} === 'resolved'
            ? $this->tables->find($table, (int) $project->{$ref['resolved_column']}, (int) $project->city_id, (int) $project->source_id) : null;
    }
}
