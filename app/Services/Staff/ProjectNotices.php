<?php

declare(strict_types=1);

namespace App\Services\Staff;

use App\Services\Operational\OperationalTables;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The notices the desktop generated as a side effect of other work (Loans_Form, ExecutiveFiles_Form, AdminPanel_From and the visit
 * forms), now addressed to web accounts through their roles. Every method runs inside the caller's transaction, under the project row
 * lock, and only reads or writes the project's own notices. Nothing is delivered outside the application.
 */
final class ProjectNotices
{
    public function __construct(private OperationalTables $tables, private NoticeService $notices) {}

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

    /** Whether the project has any shared visit notice, seen or not (the guard that stops a second schedule). */
    public function hasVisitNotices(object $project): bool
    {
        return $this->notices->ofProject($project)->whereIn('kind', NoticeService::SHARED)->exists();
    }

    /**
     * Visit notices of a new loan (`Loans_Form.generate_me_*_visits_notifications`), once per project: none is created when the project
     * already has any, or when the city has no account with the monitoring-and-evaluation duty (the desktop refused with a message). They
     * are shared copies carrying the kind letter V (vehicle sub-category 1 or 2) or O. Returns the number created.
     */
    public function generateVisitSchedule(object $project, string $loanDate, ?int $sender): int
    {
        $city = (int) $project->city_id;
        if ($this->hasVisitNotices($project) || ! $this->notices->hasMonitoring($city)) {
            return 0;
        }
        $type = (string) ($this->linked($project, 'MP_Type_ID', 'microprojecttype')->c_Name ?? '');
        $fund = (string) ($this->linked($project, 'MP_FundType_ID', 'fundtype')->c_Name ?? '');
        $letter = $this->letter($project);
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
            $this->notices->add($city, (int) $project->source_id, $project, NoticeService::VISIT, $date->toDateString(), $name, $body, null, $sender);
        }

        return count($visits);
    }

    private function letter(object $project): string
    {
        $sub = $this->linked($project, 'SubCategory_ID', 'subcategory');

        return in_array((int) ($sub !== null ? $sub->c_ID : ($project->c_SubCategory_ID ?? null)), [1, 2], true) ? 'V' : 'O';
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
     * The identifier a payment's notices carry: the desktop's payment number for imported instalments, the instalment's own identifier for
     * instalments created in the web (they have no desktop number).
     */
    public static function paymentKey(object $payment): int
    {
        return (int) ($payment->c_Pay_ID ?? $payment->id);
    }

    /** One notice per financial account (the cashier duty is the financial role) for a new unpaid instalment: dated its due date, the body is its amount. */
    public function paymentDue(object $project, object $payment, string $amount, string $due, ?int $sender): int
    {
        return $this->notices->broadcast((int) $project->city_id, (int) $project->source_id, $project, 'payment', NoticeService::PAYMENT, $due, $this->beneficiary($project), $amount, $sender, self::paymentKey($payment));
    }

    /** `Update_NotificationWithPaymentID`: the instalment's notices follow its amount and due date, and are seen exactly when it is paid. */
    public function paymentChanged(object $project, object $payment, string $amount, string $due, bool $paid): int
    {
        return DB::table('web_notices')->where('city_id', $project->city_id)->where('kind', NoticeService::PAYMENT)->where('pay_key', self::paymentKey($payment))
            ->update(['body' => $amount, 'notice_date' => $due, 'seen' => $paid ? 1 : 0]);
    }

    /**
     * The clearance notice after the last instalment is paid (`Loans_Form.Send_Trophy_Notifications`): a shared notice the day after the last
     * payment and one trophy notice per communication account. Nothing is created when the project already has that clearance notice.
     */
    public function clearance(object $project, string $lastReceived, ?int $sender): bool
    {
        $date = CarbonImmutable::parse($lastReceived)->addDay()->toDateString();
        if ($this->notices->ofProject($project)->where('kind', NoticeService::CLEARANCE)->where('notice_date', $date)->exists()) {
            return false;
        }
        $name = $this->beneficiary($project);
        $city = (int) $project->city_id;
        $source = (int) $project->source_id;
        $this->notices->add($city, $source, $project, NoticeService::CLEARANCE, $date, $name, 'براءة الذمة (Clearance)', null, $sender);
        $this->notices->broadcast($city, $source, $project, 'communication', NoticeService::TROPHY, $date, $name, 'Visit (Trophy)'.$this->letter($project), $sender);

        return true;
    }

    /**
     * The executive-file expiry notice for the legal role, dated the expiry date. On creation each legal account gets one; on an edit the
     * existing notices follow the new date. (The desktop's edit matched `Pay_ID` against the file number while its creation stored -1199,
     * so its edit never changed anything; the web applies the evident intent.)
     */
    public function executiveFile(object $project, ?string $expiry, bool $created, ?int $sender): int
    {
        if ($expiry === null) {
            return 0;
        }
        if (! $created) {
            return $this->notices->ofProject($project)->where('kind', NoticeService::EXECUTIVE_FILE)->update(['notice_date' => $expiry, 'sender_account_id' => $sender]);
        }

        return $this->notices->broadcast((int) $project->city_id, (int) $project->source_id, $project, 'executive_file', NoticeService::EXECUTIVE_FILE, $expiry, $this->beneficiary($project), 'الملف التنفيذي', $sender);
    }

    /** `Update_UserNotification(project, -1)`: every notice of the project is marked seen, whoever it is addressed to. */
    public function clearProject(object $project): int
    {
        return $this->notices->ofProject($project)->update(['seen' => 1]);
    }

    /** The project's shared visit notices of one text are marked seen (optionally only the one titled with the person's name). */
    public function visitDone(object $project, string $body, ?string $name = null): int
    {
        $query = $this->notices->ofProject($project)->where('kind', NoticeService::VISIT)->where('body', $body);
        if ($name !== null) {
            $query->where('title', $name);
        }

        return $query->update(['seen' => 1]);
    }

    /**
     * The state-change notice every account of the city's staff roles receives (`AdminPanel_From`): "تم تبديل حالة المشروع إلى: <state>".
     * Not sent for Rejected or Waiting.
     */
    public function stateChanged(object $project, object $state, ?int $sender): int
    {
        if (in_array($state->c_Name_en ?? '', ['Rejected', 'Waiting'], true)) {
            return 0;
        }
        $body = 'تم تبديل حالة المشروع إلى: '.($state->c_Name_ar ?? $state->c_Name_en ?? '');

        return $this->notices->broadcast((int) $project->city_id, (int) $project->source_id, $project, 'state', NoticeService::STATE, now()->toDateString(), $this->beneficiary($project), $body, $sender);
    }

    private function linked(object $project, string $key, string $table): ?object
    {
        $ref = $this->tables->relation('microproject', $key);

        return $project->{$ref['state_column']} === 'resolved'
            ? $this->tables->find($table, (int) $project->{$ref['resolved_column']}, (int) $project->city_id, (int) $project->source_id) : null;
    }
}
