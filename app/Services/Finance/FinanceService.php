<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Lifecycle\LifecycleService;
use App\Services\Operational\OperationalTables;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Donor assignment, loans, payment schedules and the finance-driven project states (Loans_Form, AdminPanel donor rules).
 * Money-changing writes run in one transaction under the project row lock and carry a version of everything the page showed.
 */
final class FinanceService
{
    /** States in which a project is funded: the loan form finds them and the donor is mandatory (AdminPanel_From:1335–1383). */
    public const FUNDED = [LifecycleService::ACCEPTED, LifecycleService::FINANCED, LifecycleService::FINISHED, 'Canceled', 'Closed'];

    public const LOAN_TASK = 'تسليم كامل المبلغ و تحديد الأقساط';

    public const PAID = 'Paid';

    public const NOT_PAID = 'Not Paid';

    private const MAX_INTEGER_DIGITS = 10;

    /** @var array<string, array{returned: string, payment: string, single: bool}> */
    private array $columns = [];

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables, private LifecycleService $lifecycle) {}

    private function authorize(WebAccount $account, int $city, bool $write = false): void
    {
        foreach (['applications.view', 'finance.view', ...($write ? ['finance.manage'] : [])] as $capability) {
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

    private function lock(int $projectId): void
    {
        DB::table('op_microproject')->where('id', $projectId)->lockForUpdate()->first();
    }

    private function fail(string $field, string $key): never
    {
        throw ValidationException::withMessages([$field => __('finance.errors.'.$key)]);
    }

    /** The float/double storage variant the approved mapping gives this city's loan amounts. */
    private function amountColumns(int $sourceId): array
    {
        if (isset($this->columns[$sourceId])) {
            return $this->columns[$sourceId];
        }
        $city = DB::table('sources as s')->join('cities as c', 'c.id', '=', 's.city_id')->where('s.id', $sourceId)->value('c.name_en');
        foreach (OperationalManifest::load()['mappings'] as $mapping) {
            if ($mapping['source_table'] !== 'loan' || $mapping['city'] !== $city) {
                continue;
            }
            $found = [];
            foreach ($mapping['columns'] as $column) {
                if ($column['source_column'] === 'Loan_ReturnedAmount') {
                    $found['returned'] = $column['target_column'];
                    $found['single'] = str_starts_with(strtolower($column['source_definition']), 'float');
                }
                if ($column['source_column'] === 'Loan_PaymentsAmount') {
                    $found['payment'] = $column['target_column'];
                }
            }
            if (count($found) === 3) {
                return $this->columns[$sourceId] = $found;
            }
        }
        abort(409, 'No approved loan amount mapping exists for this city.');
    }

    private function loanRows(object $project): Collection
    {
        $ref = $this->tables->relation('loan', 'MicroProject_ID');

        return $this->tables->query('loan')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->where('p.'.$ref['resolved_column'], $project->id)->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get();
    }

    private function paymentRows(object $project, Collection $loans): Collection
    {
        if ($loans->isEmpty()) {
            return collect();
        }
        $ref = $this->tables->relation('payment', 'Loan_ID');

        return $this->tables->query('payment')->where('p.city_id', $project->city_id)->where('p.source_id', $project->source_id)
            ->whereIn('p.'.$ref['resolved_column'], $loans->pluck('id')->all())->where('p.'.$ref['state_column'], 'resolved')->orderBy('p.id')->get()
            ->map(function ($row) use ($ref) {
                $row->loan_id = (int) $row->{$ref['resolved_column']};

                return $row;
            });
    }

    private function linked(object $project, string $table, string $sourceTable, string $key): ?object
    {
        $ref = $this->tables->relation($table, $key);

        return $project->{$ref['state_column']} === 'resolved'
            ? $this->tables->find($sourceTable, (int) $project->{$ref['resolved_column']}, (int) $project->city_id, (int) $project->source_id) : null;
    }

    private function strip(Collection $rows): array
    {
        return $rows->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->all();
    }

    private function version(object $project, Collection $history, Collection $loans, Collection $payments): string
    {
        $donor = $this->tables->relation('microproject', 'MP_Donor');
        $group = $this->tables->relation('microproject', 'DonorGroup_ID');

        return hash('sha256', json_encode([
            [$project->{$donor['resolved_column']}, $project->{$donor['state_column']}, $project->{$group['resolved_column']}, $project->{$group['state_column']}],
            $history->map(fn ($r) => [$r->id, $r->state_id, $r->c_Current_State])->all(), $this->strip($loans), $this->strip($payments),
        ], JSON_THROW_ON_ERROR));
    }

    /** Why finance writes are refused for the project, or null when allowed. */
    private function ineligible(?object $current, ?object $donor): ?string
    {
        if ($current === null || ! in_array($current->state_name_en, self::FUNDED, true)) {
            return 'state';
        }

        return $donor === null ? 'donor' : null;
    }

    /** Decimal strings of one loan's stored amounts (the city's variant, else whichever variant holds a value). */
    private function amounts(object $loan, array $columns): array
    {
        $variants = fn (string $key) => [$columns[$key], str_contains($columns[$key], '76a7e234') ? str_replace('76a7e234', '2ce06a9e', $columns[$key]) : str_replace('2ce06a9e', '76a7e234', $columns[$key])];
        $read = function (string $key) use ($loan, $variants) {
            foreach ($variants($key) as $column) {
                if (isset($loan->{$column})) {
                    return Money::canonical($loan->{$column}) ?? '0';
                }
            }

            return '0';
        };

        return [
            'amount' => Money::canonical($loan->c_Loan_Amount ?? null) ?? '0', 'rate_exchange' => Money::canonical($loan->c_ExchangeRate ?? null) ?? '0',
            'returned' => $read('returned'), 'payment' => $read('payment'),
        ];
    }

    /** Read model for the project finance page. */
    public function show(WebAccount $account, int $projectId): array
    {
        $project = $this->project($projectId);
        $city = (int) $project->city_id;
        $this->authorize($account, $city);
        $history = $this->lifecycle->history($project);
        $current = $this->lifecycle->current($history);
        $loans = $this->loanRows($project);
        $payments = $this->paymentRows($project, $loans);
        $donor = $this->linked($project, 'microproject', 'donor', 'MP_Donor');
        $group = $this->linked($project, 'microproject', 'donorgroup', 'DonorGroup_ID');
        $columns = $this->amountColumns((int) $project->source_id);
        $canManage = $this->access->check($account, $city, 'finance.manage');
        $funded = $current !== null && in_array($current->state_name_en, self::FUNDED, true);
        $this->audit->record($account->id, 'finance.view', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

        return [
            'project' => $project, 'current' => $current, 'donor' => $donor, 'group' => $group, 'funded' => $funded,
            'ineligible' => $this->ineligible($current, $donor), 'canManage' => $canManage, 'single' => $columns['single'],
            'version' => $this->version($project, $history, $loans, $payments),
            'loans' => $loans->map(fn ($loan) => $this->loanModel($loan, $payments, $columns)),
            'donors' => $canManage && $funded ? $this->tables->catalog('donor', $city, (int) $project->source_id) : collect(),
            'groups' => $canManage ? $this->groups($project, $donor) : collect(),
        ];
    }

    private function loanModel(object $loan, Collection $payments, array $columns): array
    {
        $values = $this->amounts($loan, $columns);
        $rows = $payments->where('loan_id', (int) $loan->id)->values()->map(function ($row) {
            $row = clone $row;
            $row->amount = Money::canonical($row->c_Pay_Amount ?? null) ?? '0';
            $row->is_paid = ($row->c_Pay_IsPaid ?? null) === self::PAID;

            return $row;
        });
        $currency = (string) ($loan->c_Currency ?? '');

        return [
            'row' => $loan, 'values' => $values, 'payments' => $rows,
            'summary' => LoanMath::summary($values['returned'], $values['payment'], $rows->map(fn ($r) => ['amount' => $r->amount, 'paid' => $r->is_paid])->all()),
            'opposite' => in_array($currency, ['USD', 'SP'], true) ? LoanMath::opposite($currency, $values['amount'], $values['rate_exchange']) : null,
            'last_paid' => $rows->isNotEmpty() && $rows->last()->is_paid,
        ];
    }

    /** Groups of the project's donor; when that donor has none (or there is no donor) every group of the city (Loans_Form:353–365). */
    private function groups(object $project, ?object $donor): Collection
    {
        $ref = $this->tables->relation('donorgroup', 'Donor_ID');
        $all = $this->tables->catalog('donorgroup', (int) $project->city_id, (int) $project->source_id);
        $own = $donor === null ? collect() : $all->filter(fn ($g) => $g->{$ref['state_column']} === 'resolved' && (int) $g->{$ref['resolved_column']} === (int) $donor->id);

        return ($own->isNotEmpty() ? $own : $all)->values();
    }

    /** Sets or clears the project donor under the funded-state rules. */
    public function assignDonor(WebAccount $account, int $projectId, ?int $donorId, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $donorId, $version) {
            $this->lock($projectId);
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $this->authorize($account, $city, true);
            $history = $this->lifecycle->history($project);
            $loans = $this->loanRows($project);
            abort_unless(hash_equals($this->version($project, $history, $loans, $this->paymentRows($project, $loans)), $version), 409, 'Finance data changed. Reload before saving.');
            $current = $this->lifecycle->current($history);
            $funded = $current !== null && in_array($current->state_name_en, self::FUNDED, true);
            $existing = $this->linked($project, 'microproject', 'donor', 'MP_Donor');
            if ($donorId === null) {
                if ($funded) {
                    $this->fail('donor_id', 'donor_required');
                }
                if ($existing !== null) {
                    DB::table('op_microproject')->where('id', $projectId)->update($this->tables->link('microproject', 'MP_Donor', null));
                }
            } else {
                if (! $funded) {
                    $this->fail('donor_id', 'donor_not_allowed');
                }
                $donor = $this->tables->find('donor', $donorId, $city, (int) $project->source_id);
                if ($donor === null) {
                    $this->fail('donor_id', 'donor_missing');
                }
                if ($existing === null || (int) $existing->id !== (int) $donor->id) {
                    DB::table('op_microproject')->where('id', $projectId)->update($this->tables->link('microproject', 'MP_Donor', (int) $donor->id));
                }
            }
            $this->audit->record($account->id, 'finance.donor_assigned', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    /** Adds a donor group to the city catalog (AddNewGroup); an identical group of the same donor is refused so a double submit adds one. */
    public function createGroup(WebAccount $account, int $projectId, string $name, int $donorId): int
    {
        return DB::transaction(function () use ($account, $projectId, $name, $donorId) {
            $this->lock($projectId);
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            if ($name === '' || mb_strlen($name) > 50) {
                $this->fail('group_name', 'group_name');
            }
            $donor = $this->tables->find('donor', $donorId, $city, $source);
            if ($donor === null) {
                $this->fail('group_donor_id', 'donor_missing');
            }
            $ref = $this->tables->relation('donorgroup', 'Donor_ID');
            foreach ($this->tables->catalog('donorgroup', $city, $source) as $group) {
                if ($group->c_Name === $name && $group->{$ref['state_column']} === 'resolved' && (int) $group->{$ref['resolved_column']} === (int) $donor->id) {
                    $this->fail('group_name', 'group_exists');
                }
            }
            $id = $this->tables->insert('donorgroup', $city, $source, ['c_Name' => $name] + $this->tables->link('donorgroup', 'Donor_ID', (int) $donor->id));
            $this->audit->record($account->id, 'finance.group_created', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return $id;
        });
    }

    /**
     * Creates or edits a loan. $input: loan_id?, currency, exchange_rate, amount, date, returned, payment, note, receipt, group_id?.
     * Creation also moves the project to Financed and completes the loan task; both are skipped when already done.
     */
    public function saveLoan(WebAccount $account, int $projectId, array $input, string $version): int
    {
        return DB::transaction(function () use ($account, $projectId, $input, $version) {
            $this->lock($projectId);
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            $history = $this->lifecycle->history($project);
            $loans = $this->loanRows($project);
            abort_unless(hash_equals($this->version($project, $history, $loans, $this->paymentRows($project, $loans)), $version), 409, 'Finance data changed. Reload before saving.');
            $current = $this->lifecycle->current($history);
            $refusal = $this->ineligible($current, $this->linked($project, 'microproject', 'donor', 'MP_Donor'));
            if ($refusal !== null) {
                throw ValidationException::withMessages(['loan' => __('finance.errors.ineligible_'.$refusal)]);
            }
            $columns = $this->amountColumns($source);
            $loanId = Fields::blank($input['loan_id'] ?? null) ? null : (int) $input['loan_id'];
            $loan = null;
            if ($loanId === null) {
                abort_if($loans->isNotEmpty(), 409, 'This project already has a loan; open it to edit.');
            } else {
                $loan = $loans->firstWhere('id', $loanId);
                abort_if($loan === null, 404);
            }
            $stored = $loan === null ? null : $this->amounts($loan, $columns);
            $payload = $this->loanPayload($loan, $stored, $input, $columns);
            $groupId = Fields::blank($input['group_id'] ?? null) ? null : (int) $input['group_id'];
            $groupPayload = $groupId === null ? [] : $this->groupPayload($project, $groupId);

            if ($loan === null) {
                $loanId = $this->tables->insert('loan', $city, $source, $payload + $this->tables->link('loan', 'MicroProject_ID', $projectId));
            } elseif ($payload !== []) {
                DB::table('op_loan')->where('id', $loan->id)->update($payload);
            }
            if ($groupPayload !== []) {
                DB::table('op_microproject')->where('id', $projectId)->update($groupPayload);
            }
            if ($loan === null) {
                $this->lifecycle->enterNamed($project, LifecycleService::FINANCED);
                $this->completeLoanTask($project);
            }
            $this->audit->record($account->id, 'finance.loan_saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);

            return (int) $loanId;
        });
    }

    /** Only supplied, changed fields are validated and written, so legacy values survive an unrelated edit. */
    private function loanPayload(?object $loan, ?array $stored, array $input, array $columns): array
    {
        $create = $loan === null;
        $get = fn (string $key) => is_string($input[$key] ?? null) ? trim($input[$key]) : '';
        $payload = [];

        $currency = $get('currency');
        if ($create || $currency !== (string) ($loan->c_Currency ?? '')) {
            in_array($currency, ['USD', 'SP'], true) || $this->fail('currency', 'currency');
            $payload['c_Currency'] = $currency;
        }
        $date = $get('date');
        if ($create && $date === '') {
            $date = now()->toDateString();
        }
        if ($create || $date !== Fields::day($loan->c_Loan_DateTaken ?? null)) {
            Fields::isDate($date) || $this->fail('date', 'date');
            $payload['c_Loan_DateTaken'] = $date;
        }

        $money = function (string $key, string $storedKey, string $column, int $digits = self::MAX_INTEGER_DIGITS, bool $positive = false, bool $single = false) use ($get, $create, $stored, &$payload) {
            $raw = Money::normalize($get($key));
            $value = $raw === '' ? '0' : $raw;
            if (! $create && Money::isDecimal($value, 8) && Money::compare($value, $stored[$storedKey] ?? '0') === 0) {
                return $stored[$storedKey] ?? '0';
            }
            if (! Money::isDecimal($value, 2) || strlen(explode('.', $value)[0]) > $digits) {
                $this->fail($key, $key === 'amount' ? 'amount' : ($key === 'exchange_rate' ? 'rate' : $key));
            }
            if ($positive && Money::isZero($value)) {
                $this->fail($key, 'amount');
            }
            if ($single && ! Money::survivesFloat($value)) {
                $this->fail($key, 'float');
            }
            $payload[$column] = Money::canonical($value) ?? '0';

            return Money::canonical($value) ?? '0';
        };
        $amount = $money('amount', 'amount', 'c_Loan_Amount', positive: true);
        $this->rateField($get, $create, $stored, $payload);
        $returned = $money('returned', 'returned', $columns['returned'], single: $columns['single']);
        $payment = $money('payment', 'payment', $columns['payment'], single: $columns['single']);

        if ($create || $amount !== ($stored['amount'] ?? null) || $returned !== ($stored['returned'] ?? null) || $payment !== ($stored['payment'] ?? null)) {
            $derived = LoanMath::derived($amount, $returned, $payment);
            if ($create || Money::compare(Money::canonical($loan->c_Loan_PaymentsCount ?? null) ?? '0', $derived['count'], 1) !== 0) {
                $payload['c_Loan_PaymentsCount'] = $derived['count'];
            }
            if ($create || Money::compare(Money::canonical($loan->c_Loan_Rate ?? null) ?? '0', $derived['rate'], 1) !== 0) {
                $payload['c_Loan_Rate'] = $derived['rate'];
            }
        }
        foreach (['note' => ['c_Loan_Note', 65535, 'note'], 'receipt' => ['c_Loan_ReceiptID', 50, 'receipt']] as $key => [$column, $max, $error]) {
            $text = is_string($input[$key] ?? null) ? $input[$key] : '';
            if ($create || $text !== (string) ($loan->{$column} ?? '')) {
                mb_strlen($text) <= $max || $this->fail($key, $error);
                $payload[$column] = $text;
            }
        }

        return $payload;
    }

    private function rateField(callable $get, bool $create, ?array $stored, array &$payload): void
    {
        $raw = Money::normalize($get('exchange_rate'));
        $value = $raw === '' ? '0' : $raw;
        if (! $create && Money::isDecimal($value, 8) && Money::compare($value, $stored['rate_exchange'] ?? '0') === 0) {
            return;
        }
        if (! Money::isDecimal($value, 2) || strlen(explode('.', $value)[0]) > self::MAX_INTEGER_DIGITS) {
            $this->fail('exchange_rate', 'rate');
        }
        $payload['c_ExchangeRate'] = Money::canonical($value) ?? '0';
    }

    /** Group and the group's donor, as the loan form writes them (Loans_Form:945–963). */
    private function groupPayload(object $project, int $groupId): array
    {
        $group = $this->tables->find('donorgroup', $groupId, (int) $project->city_id, (int) $project->source_id);
        if ($group === null) {
            $this->fail('group_id', 'group_missing');
        }
        $donor = $this->tables->relation('donorgroup', 'Donor_ID');
        $payload = $this->tables->link('microproject', 'DonorGroup_ID', (int) $group->id);
        if ($group->{$donor['state_column']} === 'resolved') {
            $payload += $this->tables->link('microproject', 'MP_Donor', (int) $group->{$donor['resolved_column']});
        }

        return $payload;
    }

    /** `Update_Task_MicroProject`: updates the project's existing assignment of the loan task, nothing otherwise. */
    private function completeLoanTask(object $project): void
    {
        $task = $this->tables->catalog('task', (int) $project->city_id, (int) $project->source_id)->first(fn ($row) => $row->c_T_Name === self::LOAN_TASK);
        if ($task === null) {
            return;
        }
        $projectRef = $this->tables->relation('task_microproject', 'MicroProject_ID');
        $taskRef = $this->tables->relation('task_microproject', 'Task_ID');
        DB::table('op_task_microproject')->where('city_id', $project->city_id)->where('source_id', $project->source_id)
            ->where($projectRef['resolved_column'], $project->id)->where($taskRef['resolved_column'], $task->id)
            ->update(['c_State' => 1, 'c_Date' => now()->format('Y-m-d H:i:s')]);
    }

    /**
     * Saves the instalment rows of one loan. Each row: id?, amount, due_date, paid, received_date, note.
     * Rows equal to the stored values are neither validated nor written.
     */
    public function savePayments(WebAccount $account, int $projectId, int $loanId, array $rows, string $version): void
    {
        DB::transaction(function () use ($account, $projectId, $loanId, $rows, $version) {
            $this->lock($projectId);
            $project = $this->project($projectId);
            $city = (int) $project->city_id;
            $source = (int) $project->source_id;
            $this->authorize($account, $city, true);
            $history = $this->lifecycle->history($project);
            $loans = $this->loanRows($project);
            $payments = $this->paymentRows($project, $loans);
            abort_unless(hash_equals($this->version($project, $history, $loans, $payments), $version), 409, 'Finance data changed. Reload before saving.');
            $refusal = $this->ineligible($this->lifecycle->current($history), $this->linked($project, 'microproject', 'donor', 'MP_Donor'));
            if ($refusal !== null) {
                throw ValidationException::withMessages(['payments' => __('finance.errors.ineligible_'.$refusal)]);
            }
            $loan = $loans->firstWhere('id', $loanId);
            abort_if($loan === null, 404);
            $existing = $payments->where('loan_id', $loanId)->keyBy('id');

            foreach (array_values($rows) as $index => $row) {
                $id = Fields::blank($row['id'] ?? null) ? null : (int) $row['id'];
                if ($id === null && Fields::blank($row['amount'] ?? null) && Fields::blank($row['due_date'] ?? null) && Fields::blank($row['note'] ?? null)) {
                    continue;
                }
                $stored = $id === null ? null : $existing->get($id);
                abort_if($id !== null && $stored === null, 422, 'Select instalments of this loan.');
                $this->writePayment($project, $loan, $stored, $row, $index);
            }
            $this->finishWhenRepaid($project, $loan);
            $this->audit->record($account->id, 'finance.payments_saved', 'succeeded', $city, subjectType: 'application_entity', subjectId: $projectId);
        });
    }

    private function writePayment(object $project, object $loan, ?object $stored, array $row, int $index): void
    {
        $field = fn (string $key) => 'rows.'.$index.'.'.$key;
        $amount = Money::normalize(Fields::text($row['amount'] ?? ''));
        $due = trim(Fields::text($row['due_date'] ?? ''));
        $paid = in_array($row['paid'] ?? '0', ['1', 1, true, 'on', self::PAID], true);
        $receivedInput = trim(Fields::text($row['received_date'] ?? ''));
        $note = Fields::text($row['note'] ?? '');

        if ($stored !== null) {
            $same = Money::canonical($amount) === (Money::canonical($stored->c_Pay_Amount ?? null) ?? '0') && $due === Fields::day($stored->c_Pay_DueDate ?? null)
                && $paid === (($stored->c_Pay_IsPaid ?? null) === self::PAID) && $receivedInput === Fields::day($stored->c_Pay_RecievedOnDate ?? null)
                && $note === (string) ($stored->c_Pay_Note ?? '');
            if ($same) {
                return;
            }
        }
        if (! Money::isDecimal($amount, 0, true) || ! Money::isWholeFloatAmount($amount)) {
            $this->fail($field('amount'), 'payment_amount');
        }
        Fields::isDate($due) || $this->fail($field('due_date'), 'payment_due');
        if ($receivedInput !== '' && ! Fields::isDate($receivedInput)) {
            $this->fail($field('received_date'), 'payment_due');
        }
        mb_strlen($note) <= 65535 || $this->fail($field('note'), 'note');
        // Desktop: an unpaid row (or a paid row without a received date) records the due date as received date.
        $payload = [
            'c_Pay_Amount' => Money::canonical($amount) ?? '0', 'c_Pay_DueDate' => $due, 'c_Pay_IsPaid' => $paid ? self::PAID : self::NOT_PAID,
            'c_Pay_RecievedOnDate' => $paid && $receivedInput !== '' ? $receivedInput : $due, 'c_Pay_Note' => $note,
        ];
        if ($stored === null) {
            $this->tables->insert('payment', (int) $project->city_id, (int) $project->source_id, $payload + $this->tables->link('payment', 'Loan_ID', (int) $loan->id));
        } else {
            DB::table('op_payment')->where('id', $stored->id)->update($payload);
        }
    }

    /** SavePayments_button_Click:1125–1145: a paid last row with less than one and a half instalments left finishes the project. */
    private function finishWhenRepaid(object $project, object $loan): void
    {
        $loans = $this->loanRows($project);
        $payments = $this->paymentRows($project, $loans);
        $model = $this->loanModel($loans->firstWhere('id', $loan->id), $payments, $this->amountColumns((int) $project->source_id));
        if ($model['last_paid'] && $model['summary']['near_end']) {
            $this->lifecycle->enterNamed($project, LifecycleService::FINISHED);
        }
    }
}
