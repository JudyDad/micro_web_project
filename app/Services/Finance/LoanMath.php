<?php

declare(strict_types=1);

namespace App\Services\Finance;

/**
 * The desktop loan calculations (`Loans_Form.Calculate_RemainedAmount`, `Select.BuildLoanReportQuery`) on exact decimals.
 * Every figure is per loan and therefore per currency; nothing here converts or sums across currencies.
 */
final class LoanMath
{
    /**
     * Stored payments count and rate. The desktop stores the `N1`-formatted label text, so both are rounded to one decimal.
     * Both are 0 when the returned amount or the instalment is 0 (the desktop leaves them blank).
     *
     * @return array{count: string, rate: string}
     */
    public static function derived(string $amount, string $returned, string $payment): array
    {
        if (Money::isZero($returned) || Money::isZero($payment) || Money::isZero($amount)) {
            return ['count' => '0.0', 'rate' => '0.0'];
        }

        return [
            'count' => Money::round(bcdiv($returned, $payment, 12), 1),
            'rate' => Money::round(bcdiv(bcmul($returned, '100', 8), $amount, 12), 1),
        ];
    }

    /** Amount in the other currency: amount ÷ rate for SP with a non-zero rate, otherwise amount × rate. */
    public static function opposite(string $currency, string $amount, string $rate): string
    {
        if ($currency === 'SP' && ! Money::isZero($rate)) {
            return Money::round(bcdiv($amount, $rate, 12), 2);
        }

        return Money::round(bcmul($amount, $rate, 12), 2);
    }

    /**
     * @param  list<array{amount: string, paid: bool}>  $payments
     * @return array{paid_amount: string, paid_count: int, remaining: string, remaining_clamped: string, all_count: ?string, remaining_count: ?string, near_end: bool}
     */
    public static function summary(string $returned, string $payment, array $payments): array
    {
        $paid = array_values(array_filter($payments, fn ($row) => $row['paid']));
        $paidAmount = Money::sum(array_map(fn ($row) => $row['amount'], $paid));
        $remaining = Money::canonical(bcsub($returned, $paidAmount, 8)) ?? '0';
        $computable = ! Money::isZero($payment) && ! Money::isZero($returned);

        return [
            'paid_amount' => $paidAmount,
            'paid_count' => count($paid),
            'remaining' => $remaining,
            'remaining_clamped' => bccomp($remaining, '0', 8) < 0 ? '0' : $remaining,
            'all_count' => $computable ? Money::round(bcdiv($returned, $payment, 12), 1) : null,
            'remaining_count' => $computable ? Money::round(bcdiv($remaining, $payment, 12), 1) : null,
            // Flagged (and, when the last row is paid, the loan is finished) below one and a half instalments.
            'near_end' => $computable && bccomp($remaining, bcmul($payment, '1.5', 8), 8) < 0,
        ];
    }
}
