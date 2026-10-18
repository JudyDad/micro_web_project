<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Import\KeyEncoder;
use App\Services\Import\Readers\FrozenJsonSourceReader;
use Database\Schema\OperationalManifest;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

require_once __DIR__.'/../../database/schema/OperationalManifest.php';

final class FrozenSourceReaderTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'micro-projects-frozen-'.bin2hex(random_bytes(10));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_complete_manifest_preserves_binary_null_empty_and_arabic_payloads(): void
    {
        $manifest = $this->fixture('manualfile', [
            ['ID' => '1', 'Content' => "\0\xff\r\n", 'Path' => 'ملف.pdf'],
            ['ID' => '2', 'Content' => '', 'Path' => ''],
            ['ID' => '3', 'Content' => null, 'Path' => 'NULL differs'],
        ]);
        $reader = new FrozenJsonSourceReader($manifest);
        $rows = iterator_to_array($reader->rows('manualfile'));
        self::assertSame("\0\xff\r\n", $rows[0]['values']['Content']);
        self::assertSame('ملف.pdf', $rows[0]['values']['Path']);
        self::assertSame('', $rows[1]['values']['Content']);
        self::assertNull($rows[2]['values']['Content']);
        self::assertSame([1, 2, 3], array_column($rows, 'ordinal'));
        self::assertSame(122, count($reader->tables()));
        self::assertSame(3, $reader->contract()['table_counts']['manualfile']);
        self::assertSame(hash_file('sha256', $manifest), $reader->contract()['frozen_manifest_sha256']);
    }

    public function test_keyless_identical_occurrences_get_distinct_stable_keys(): void
    {
        $manifest = $this->fixture('t1', [[], []]);
        $reader = new FrozenJsonSourceReader($manifest);
        $rows = iterator_to_array($reader->rows('t1'));
        self::assertNotSame($rows[0]['key'], $rows[1]['key']);
        self::assertSame($rows[0]['values'], $rows[1]['values']);
        self::assertSame(array_column($rows, 'key'), array_column(iterator_to_array($reader->rows('t1')), 'key'));
    }

    public function test_changed_table_bytes_are_rejected_before_import(): void
    {
        $manifest = $this->fixture('manualfile', []);
        $metadata = json_decode(file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);
        file_put_contents($this->directory.DIRECTORY_SEPARATOR.$metadata['files']['manualfile']['file'], "changed\n");
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('checksum');
        new FrozenJsonSourceReader($manifest);
    }

    public function test_path_traversal_is_rejected_even_with_a_valid_file_hash(): void
    {
        $manifest = $this->fixture('manualfile', []);
        $metadata = json_decode(file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);
        $metadata['files']['manualfile']['file'] = '../outside.jsonl';
        file_put_contents($manifest, json_encode($metadata, JSON_THROW_ON_ERROR));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsafe');
        new FrozenJsonSourceReader($manifest);
    }

    public function test_missing_empty_source_table_is_rejected(): void
    {
        $manifest = $this->fixture('manualfile', []);
        $metadata = json_decode(file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);
        unset($metadata['files']['age']);
        file_put_contents($manifest, json_encode($metadata, JSON_THROW_ON_ERROR));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('every city table');
        new FrozenJsonSourceReader($manifest);
    }

    private function fixture(string $populated, array $rows): string
    {
        $header = ['format' => 'micro-projects-frozen-source-v1', 'city' => 'Aleppo', 'source_id' => 1, 'snapshot_id' => 1,
            'archive_sha256' => str_repeat('a', 64), 'sql_sha256' => str_repeat('b', 64),
            'mapping_sha256' => OperationalManifest::SHA256, 'schema_sha256' => OperationalManifest::SHA256,
            'extractor_sha256' => str_repeat('c', 64), 'extractor_version' => 'synthetic-unit-v1',
            'key_encoding_version' => KeyEncoder::VERSION, 'files' => []];
        foreach (OperationalManifest::load()['mappings'] as $mapping) {
            if ($mapping['city'] !== 'Aleppo') {
                continue;
            }
            $name = $mapping['source_table'];
            $bytes = '';
            foreach ($name === $populated ? $rows : [] as $index => $overrides) {
                $values = array_fill_keys(array_column($mapping['columns'], 'source_column'), null);
                foreach ($overrides as $column => $value) {
                    $values[$column] = $value === null ? null : base64_encode($value);
                }
                $bytes .= json_encode(['ordinal' => $index + 1, 'values' => $values], JSON_THROW_ON_ERROR)."\n";
            }
            $filename = 'table-'.$mapping['source_table_id'].'.jsonl';
            file_put_contents($this->directory.DIRECTORY_SEPARATOR.$filename, $bytes);
            $header['files'][$name] = ['file' => $filename, 'rows' => $name === $populated ? count($rows) : 0, 'sha256' => hash('sha256', $bytes)];
        }
        $manifest = $this->directory.DIRECTORY_SEPARATOR.'manifest.json';
        file_put_contents($manifest, json_encode($header, JSON_THROW_ON_ERROR));

        return $manifest;
    }
}
