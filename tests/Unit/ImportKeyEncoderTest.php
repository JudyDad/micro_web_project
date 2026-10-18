<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Import\ExactValue;
use App\Services\Import\KeyEncoder;
use PHPUnit\Framework\TestCase;

final class ImportKeyEncoderTest extends TestCase
{
    public function test_length_prefixes_preserve_composite_boundaries_null_and_order(): void
    {
        $columns = ['a' => 'varchar(10)', 'b' => 'varchar(10)'];
        $first = KeyEncoder::primary($columns, ['a' => 'ab', 'b' => 'c']);
        $this->assertNotSame($first, KeyEncoder::primary($columns, ['a' => 'a', 'b' => 'bc']));
        $this->assertNotSame(KeyEncoder::primary($columns, ['a' => null, 'b' => '']), KeyEncoder::primary($columns, ['a' => '', 'b' => '']));
        $this->assertNotSame($first, KeyEncoder::primary(array_reverse($columns, true), ['a' => 'ab', 'b' => 'c']));
    }

    public function test_duplicate_payload_occurrences_keep_distinct_ordinal_keys(): void
    {
        $hash = str_repeat('a', 64);
        $this->assertNotSame(KeyEncoder::ordinal($hash, 't1', 1), KeyEncoder::ordinal($hash, 't1', 2));
        $this->assertNotSame(KeyEncoder::ordinal($hash, 't1', 1), KeyEncoder::ordinal(str_repeat('b', 64), 't1', 1));
    }

    public function test_storage_width_and_null_empty_are_distinct(): void
    {
        $this->assertFalse(ExactValue::equal(null, '', 'text'));
        $this->assertFalse(ExactValue::equal('', null, 'text'));
        $this->assertTrue(ExactValue::equal(0.10000000149011612, '0.1', 'float'));
        $this->assertFalse(ExactValue::equal(0.10000000149011612, '0.1', 'double'));
        $this->assertFalse(ExactValue::equal(0.0, '-0', 'double'));
        $this->assertTrue(ExactValue::equal('18446744073709551615', '18446744073709551615', 'bigint unsigned'));
    }
}
