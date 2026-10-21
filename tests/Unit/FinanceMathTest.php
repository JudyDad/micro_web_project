<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Finance\ExecutiveFileService;
use App\Services\Finance\LoanMath;
use App\Services\Finance\Money;
use PHPUnit\Framework\TestCase;

final class FinanceMathTest extends TestCase
{
    public function test_normalize_strips_grouping_and_maps_arabic_digits(): void
    {
        self::assertSame('1234567.5', Money::normalize(' 1,234,567.5 '));
        self::assertSame('1234.50', Money::normalize('١٬٢٣٤٫٥٠'));
        self::assertSame('', Money::normalize('  '));
    }

    public function test_decimal_validation_is_exact(): void
    {
        self::assertTrue(Money::isDecimal('10.25', 2));
        self::assertFalse(Money::isDecimal('10.255', 2));
        self::assertFalse(Money::isDecimal('-1', 2));
        self::assertTrue(Money::isDecimal('-1', 2, true));
        self::assertFalse(Money::isDecimal('1.5', 0, true));
        self::assertFalse(Money::isDecimal('1e3', 2));
        self::assertFalse(Money::isDecimal('', 2));
        self::assertFalse(Money::isDecimal('.5', 2));
    }

    public function test_rounding_is_half_away_from_zero(): void
    {
        self::assertSame('0.3', Money::round('0.25', 1));
        self::assertSame('-0.3', Money::round('-0.25', 1));
        self::assertSame('2.0', Money::round('1.95', 1));
        self::assertSame('0.0', Money::round('-0.04', 1));
        self::assertSame('10.00', Money::round('9.995', 2));
    }

    public function test_canonical_form_drops_noise_but_never_changes_a_value(): void
    {
        self::assertSame('1200', Money::canonical('1200.00'));
        self::assertSame('0.1', Money::canonical(0.1));
        self::assertSame('0', Money::canonical('-0'));
        self::assertSame('1500.5', Money::canonical(1500.5));
        self::assertNull(Money::canonical(null));
        self::assertNull(Money::canonical(''));
        self::assertSame('1234567.89', Money::canonical('1234567.89'));
    }

    public function test_single_precision_and_whole_unit_limits(): void
    {
        self::assertTrue(Money::survivesFloat('1500.5'));
        self::assertTrue(Money::survivesFloat('16777216'));
        self::assertFalse(Money::survivesFloat('1234567.89'));
        self::assertTrue(Money::survivesFloat('0.1'), 'MySQL reads a FLOAT 0.1 back as 0.1');
        self::assertFalse(Money::survivesFloat('99999.99'));
        self::assertTrue(Money::isWholeFloatAmount('-16777216'));
        self::assertFalse(Money::isWholeFloatAmount('16777217'));
        self::assertFalse(Money::isWholeFloatAmount('12.5'));
    }

    public function test_derived_loan_figures_match_the_desktop_n1_labels(): void
    {
        self::assertSame(['count' => '10.0', 'rate' => '120.0'], LoanMath::derived('1000', '1200', '120'));
        self::assertSame(['count' => '3.3', 'rate' => '33.3'], LoanMath::derived('3000', '1000', '300'));
        self::assertSame(['count' => '0.0', 'rate' => '0.0'], LoanMath::derived('1000', '0', '120'));
        self::assertSame(['count' => '0.0', 'rate' => '0.0'], LoanMath::derived('1000', '1200', '0'));
    }

    public function test_opposite_amount_follows_the_desktop_branches(): void
    {
        self::assertSame('2.50', LoanMath::opposite('SP', '37500', '15000'));
        self::assertSame('0.00', LoanMath::opposite('SP', '37500', '0'), 'SP with no rate multiplies by zero');
        self::assertSame('15000000.00', LoanMath::opposite('USD', '1000', '15000'));
        self::assertSame('0.33', LoanMath::opposite('SP', '5', '15'));
    }

    public function test_summary_is_exact_and_flags_the_last_instalments(): void
    {
        $payments = [['amount' => '100', 'paid' => true], ['amount' => '100', 'paid' => true], ['amount' => '100', 'paid' => false], ['amount' => '-20', 'paid' => true]];
        $summary = LoanMath::summary('300', '100', $payments);
        self::assertSame('180', $summary['paid_amount']);
        self::assertSame(3, $summary['paid_count'], 'refunds count as paid rows like the desktop');
        self::assertSame('120', $summary['remaining']);
        self::assertSame('3.0', $summary['all_count']);
        self::assertSame('1.2', $summary['remaining_count']);
        self::assertTrue($summary['near_end'], '120 left is below one and a half instalments of 100');

        $over = LoanMath::summary('200', '100', [['amount' => '100', 'paid' => true], ['amount' => '150', 'paid' => true]]);
        self::assertSame('-50', $over['remaining']);
        self::assertSame('0', $over['remaining_clamped']);
        self::assertTrue($over['near_end']);

        $boundary = LoanMath::summary('250', '100', [['amount' => '100', 'paid' => true]]);
        self::assertSame('150', $boundary['remaining']);
        self::assertFalse($boundary['near_end'], 'exactly one and a half instalments is not below the threshold');
        self::assertNull(LoanMath::summary('0', '100', [])['all_count']);
        self::assertFalse(LoanMath::summary('100', '0', [])['near_end']);
    }

    public function test_executive_expiry_uses_dotnet_month_arithmetic(): void
    {
        self::assertSame('2026-02-28', ExecutiveFileService::expiry('2026-01-31', 1));
        self::assertSame('2028-02-29', ExecutiveFileService::expiry('2027-08-31', 6));
        self::assertSame('2026-05-01', ExecutiveFileService::expiry('2026-05-01', '0'));
        self::assertNull(ExecutiveFileService::expiry('', 3));
        self::assertNull(ExecutiveFileService::expiry('2026-05-01', null));
    }
}
