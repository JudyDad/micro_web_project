<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\FinanceService;
use App\Services\Finance\LoanMath;
use App\Services\Finance\Money;
use App\Services\Operational\OperationalTables;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * "Loans and Payments" (`Search_Form.Loan_bind`, `Select.BuildLoanReportQuery`): one row per loan with the project, the people, the
 * amounts in both currencies and the instalment figures. Every figure is per loan and per currency; nothing is summed across currencies.
 * Formulas follow the desktop report, including its use of the stored loan rate for the remaining instalments.
 */
final class LoanSearch extends SearchMode
{
    use FiltersProjects;

    public const FINISHED = 'Finished';

    public function __construct(AccountAccess $access, AccountAuditTrail $audit, OperationalTables $tables, private FinanceService $finance)
    {
        parent::__construct($access, $audit, $tables);
    }

    public function key(): string
    {
        return 'loans';
    }

    protected function capabilities(): array
    {
        return ['finance.view', 'applications.view'];
    }

    protected function exportCapabilities(): array
    {
        return ['reports.export', 'finance.export'];
    }

    public function filters(): array
    {
        return ['q', 'state', 'donor', 'group', 'request', 'loan', 'classify', 'funded'];
    }

    public function headings(): array
    {
        return [
            'City', 'Project number', 'Beneficiaries', 'Mobile', 'Career', 'Category', 'Sub category', 'Project status', 'Loan date', 'Currency', 'Exchange rate',
            'Loan (SP)', 'Loan (USD)', 'Return rate %', 'Amount to return', 'Instalment', 'Paid amount', 'Remaining amount', 'Total instalments', 'Paid instalments',
            'Remaining instalments', 'First instalment date', 'Last paid date', 'Due instalment date', 'Months late', 'Unpaid instalments', 'Note', 'Donor', 'Group', 'Receipt number',
        ];
    }

    public function numeric(): array
    {
        return [1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 24, 25];
    }

    protected function query(array $cityIds, array $filters): Builder
    {
        $loanProject = $this->tables->relation('loan', 'MicroProject_ID');
        $ref = fn (string $key) => $this->tables->relation('microproject', $key);
        $query = $this->tables->query('loan')
            ->join('op_microproject as m', fn ($j) => $j->on('m.id', '=', 'p.'.$loanProject['resolved_column'])->on('m.city_id', '=', 'p.city_id')->on('m.source_id', '=', 'p.source_id'))
            ->where('p.'.$loanProject['state_column'], 'resolved')->whereIn('p.city_id', $cityIds)
            ->addSelect([
                'm.id as project_id', 'm.c_MP_ID as project_number', 'm.c_MP_DateOfRequest as request_date',
                'm.'.$ref('MP_Donor')['resolved_column'].' as donor_id', 'm.'.$ref('DonorGroup_ID')['resolved_column'].' as group_id',
                'm.'.$ref('Career_ID')['resolved_column'].' as career_id', 'm.'.$ref('Category_ID')['resolved_column'].' as category_id',
                'm.'.$ref('SubCategory_ID')['resolved_column'].' as subcategory_id',
            ])->selectRaw('('.$this->currentStateSql().') as current_state_id');

        $this->applyProjectFilters($query, $cityIds, $filters);
        $this->applyClassificationFilters($query, $cityIds, $filters);
        // The funded amount in USD (an SP loan is converted by its exchange rate), rounded to two decimals as in the desktop. A blank end is
        // open: the desktop turns a blank end into 0, which makes a range with only a "from" empty.
        $usd = "round(case p.c_Currency when 'SP' then p.c_Loan_Amount / p.c_ExchangeRate when 'USD' then p.c_Loan_Amount end, 2)";
        foreach ([['funded_from', '>='], ['funded_to', '<=']] as [$key, $operator]) {
            if (($filters[$key] ?? '') !== '') {
                $query->whereRaw("$usd $operator (? + 0)", [(float) $filters[$key]]);
            }
        }
        foreach ([['loan_from', '>=', 'p.c_Loan_DateTaken'], ['loan_to', '<=', 'p.c_Loan_DateTaken']] as [$key, $operator, $column]) {
            if (($filters[$key] ?? '') !== '') {
                $query->where($column, $operator, $filters[$key]);
            }
        }

        return $query;
    }

    protected function order(Builder $query): Builder
    {
        return $query->orderBy('m.c_MP_ID')->orderBy('p.city_id')->orderBy('p.id');
    }

    protected function lines(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }
        $cityIds = $rows->pluck('city_id')->unique()->map(fn ($id) => (int) $id)->all();
        $paymentLoan = $this->tables->relation('payment', 'Loan_ID');
        $payments = $this->tables->query('payment')->whereIn('p.'.$paymentLoan['resolved_column'], $rows->pluck('id')->all())->where('p.'.$paymentLoan['state_column'], 'resolved')
            ->orderBy('p.id')->get()->groupBy(fn ($row) => (int) $row->{$paymentLoan['resolved_column']});
        $people = $this->people($rows->pluck('project_id')->unique()->all());
        $states = $this->catalog('state', $cityIds);
        $catalogs = ['donor' => $this->catalog('donor', $cityIds), 'group' => $this->catalog('donorgroup', $cityIds), 'career' => $this->catalog('career', $cityIds),
            'category' => $this->catalog('category', $cityIds), 'subcategory' => $this->catalog('subcategory', $cityIds)];
        $name = fn (string $catalog, $id) => $id === null ? '' : (string) ($catalogs[$catalog]->get((int) $id)->c_Name ?? '');
        $now = CarbonImmutable::now();

        return $rows->map(function ($loan) use ($payments, $people, $states, $name, $now) {
            $values = $this->finance->loanAmounts($loan);
            $currency = (string) ($loan->c_Currency ?? '');
            $rate = $values['rate_exchange'];
            $amount = $values['amount'];
            $returned = $values['returned'];
            $instalment = $values['payment'];
            $list = ($payments[(int) $loan->id] ?? collect())->map(fn ($row) => [
                'amount' => Money::canonical($row->c_Pay_Amount ?? null) ?? '0', 'paid' => ($row->c_Pay_IsPaid ?? null) === FinanceService::PAID,
                'due' => $this->day($row->c_Pay_DueDate ?? null), 'received' => $this->day($row->c_Pay_RecievedOnDate ?? null),
            ]);
            $summary = LoanMath::summary($returned, $instalment, $list->map(fn ($p) => ['amount' => $p['amount'], 'paid' => $p['paid']])->all());
            $state = $states->get((int) ($loan->current_state_id ?? 0));
            $stateEnglish = (string) ($state->c_Name_en ?? '');
            $unpaid = $list->where('paid', false);
            $lastDue = $unpaid->pluck('due')->filter()->max();
            $sp = $currency === 'SP' ? $amount : ($currency === 'USD' && ! Money::isZero($rate) ? Money::round(bcmul($amount, $rate, 12), 2) : '');
            $usd = $currency === 'USD' ? $amount : ($currency === 'SP' && ! Money::isZero($rate) ? Money::round(bcdiv($amount, $rate, 12), 2) : '');
            // `Round((Loan_Amount × Loan_Rate / 100) − paid, clamped at 0) ÷ instalment`: the desktop report uses the stored percentage here.
            $dueByRate = bcsub(bcdiv(bcmul($amount, Money::canonical($loan->c_Loan_Rate ?? null) ?? '0', 8), '100', 8), $summary['paid_amount'], 8);
            // As in the desktop, both remaining figures are blank until at least one instalment is paid (its SQL sums the paid instalments, which is NULL for none).
            $nonePaid = $summary['paid_count'] === 0;
            $remainingCount = $nonePaid || Money::isZero($instalment) ? '' : Money::round(bcdiv(bccomp($dueByRate, '0', 8) < 0 ? '0' : $dueByRate, $instalment, 12), 2);
            $remainingAmount = $nonePaid ? '' : $summary['remaining_clamped'];
            $late = Money::isZero($summary['remaining_clamped']) ? 0
                : ($lastDue === null || $stateEnglish === self::FINISHED ? '' : (int) CarbonImmutable::parse($lastDue)->diffInMonths($now->addMonth(), false));

            return [
                $loan->city_name_en, $loan->project_number, $people[(int) $loan->project_id]['names'] ?? '', $people[(int) $loan->project_id]['mobile'] ?? '',
                $name('career', $loan->career_id), $name('category', $loan->category_id), $name('subcategory', $loan->subcategory_id), $this->stateName($state),
                $this->day($loan->c_Loan_DateTaken ?? null), $currency, $rate, $sp, $usd,
                Money::isZero($amount) ? '' : Money::round(bcdiv(bcmul($returned, '100', 8), $amount, 12), 2), $returned, $instalment,
                $summary['paid_amount'], $remainingAmount, Money::isZero($instalment) ? '' : Money::round(bcdiv($returned, $instalment, 12), 2), $summary['paid_count'],
                $remainingCount, $list->pluck('due')->filter()->min() ?? '', $list->where('paid', true)->pluck('received')->filter()->max() ?? '',
                $stateEnglish === 'Financed' ? ($lastDue ?? '') : '', $late, $unpaid->count(), $loan->c_Loan_Note, $name('donor', $loan->donor_id), $name('group', $loan->group_id), $loan->c_Loan_ReceiptID,
            ];
        })->all();
    }

    private function day(mixed $value): ?string
    {
        $text = $value === null ? '' : substr((string) $value, 0, 10);

        return $text === '' || str_starts_with($text, '0000') ? null : $text;
    }

    /** @return array<int, array{names: string, mobile: string}> project id → every linked person's name, and the first mobile */
    private function people(array $projectIds): array
    {
        $linkProject = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $linkPerson = $this->tables->relation('person_microproject', 'Person_ID');
        $links = $this->tables->query('person_microproject')->whereIn('p.'.$linkProject['resolved_column'], $projectIds)->where('p.'.$linkPerson['state_column'], 'resolved')->orderBy('p.id')->get();
        $persons = $this->tables->query('person')->whereIn('p.id', $links->pluck($linkPerson['resolved_column'])->unique()->all())->get()->keyBy('id');
        $result = [];
        foreach ($links as $link) {
            $person = $persons->get((int) $link->{$linkPerson['resolved_column']});
            if ($person === null) {
                continue;
            }
            $project = (int) $link->{$linkProject['resolved_column']};
            $name = trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? '').' ابن/ة '.($person->c_P_FatherName ?? ''));
            $result[$project]['names'] = isset($result[$project]['names']) ? $result[$project]['names'].', '.$name : $name;
            $result[$project]['mobile'] ??= (string) ($person->c_P_Mobile ?? '');
            if ($result[$project]['mobile'] === '') {
                $result[$project]['mobile'] = (string) ($person->c_P_Mobile ?? '');
            }
        }

        return $result;
    }
}
