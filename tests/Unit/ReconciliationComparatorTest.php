<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Reconciliation\CellComparator;
use App\Services\Reconciliation\SourceKeyInspector;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ReconciliationComparatorTest extends TestCase
{
    public function test_exact_types_do_not_hide_changed_values(): void
    {
        $compare = new CellComparator;
        self::assertTrue($compare->equal('18446744073709551615', '18446744073709551615', 'bigint unsigned NOT NULL'));
        self::assertFalse($compare->equal('18446744073709551615', '18446744073709551614', 'bigint unsigned NOT NULL'));
        self::assertTrue($compare->equal('001.2300', '1.23', 'decimal(20,4)'));
        self::assertFalse($compare->equal('1.2301', '1.2300', 'decimal(20,4)'));
        self::assertFalse($compare->equal('invalid', 'invalid', 'decimal(20,4)'));
        self::assertFalse($compare->equal(null, '', 'longblob'));
        self::assertTrue($compare->equal("\x00\xff", "\x00\xff", 'longblob'));
        self::assertFalse($compare->equal('نص ', 'نص', 'varchar(20)'));
        self::assertFalse($compare->equal("e\u{301}", "\u{e9}", 'varchar(20)'));
        self::assertFalse($compare->equal('0000-00-00', null, 'date'));
        self::assertFalse($compare->equal('-0', 0.0, 'double'));
        self::assertFalse($compare->equal('1', 1.0000000000000002, 'double'));
        self::assertTrue($compare->equal('0.10000000149011612', 0.10000000149011612, 'float'));
        self::assertFalse($compare->equal('0.10000000149011612', 0.10000000894069672, 'float'));
    }

    public function test_ordinal_identity_retains_equal_rows_as_distinct_occurrences(): void
    {
        $inspector = new SourceKeyInspector;
        $sqlHash = str_repeat('ab', 32);
        $prefix = "MPO\x01".hex2bin($sqlHash).pack('N', 2).'t1';
        self::assertSame(1, $inspector->ordinal($prefix.pack('N', 1).'1', $sqlHash, 't1'));
        self::assertSame(2, $inspector->ordinal($prefix.pack('N', 1).'2', $sqlHash, 't1'));
        $this->expectException(RuntimeException::class);
        $inspector->ordinal($prefix.pack('N', 1).'1', str_repeat('cd', 32), 't1');
    }

    public function test_primary_decoder_checks_component_order_definitions_and_truncation(): void
    {
        $columns = ['project' => 'int NOT NULL', 'answer' => 'int NOT NULL'];
        $values = ['project' => '7', 'answer' => '9'];
        $wire = "MPK\x01".pack('N', 2);
        foreach ($columns as $name => $definition) {
            foreach ([$name, $definition] as $part) {
                $wire .= pack('N', strlen($part)).$part;
            }
            $wire .= "\x01".pack('N', strlen($values[$name])).$values[$name];
        }
        $inspector = new SourceKeyInspector;
        self::assertTrue($inspector->primaryMatches($wire, $columns, $values));
        self::assertFalse($inspector->primaryMatches($wire, array_reverse($columns, true), $values));
        self::assertFalse($inspector->primaryMatches($wire, $columns, ['project' => '7', 'answer' => '8']));
        self::assertFalse($inspector->primaryMatches(substr($wire, 0, -1), $columns, $values));
    }
}
