<?php

declare(strict_types=1);

namespace App\Services\Reports;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Streams a table as an Excel workbook (.xlsx, the default) or a CSV, chosen by the `format` query value. */
final class TableExport
{
    public const FORMATS = ['xlsx', 'csv'];

    private const XLSX_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    public function __construct(private CsvExport $csv) {}

    public static function format(?string $requested): string
    {
        return in_array($requested, self::FORMATS, true) ? $requested : 'xlsx';
    }

    /**
     * @param  list<string>  $headings
     * @param  iterable<list<scalar|null>>  $rows
     * @param  list<int>  $numeric  zero-based columns written as numbers when the value is a plain decimal of at most 15 digits
     */
    public function stream(string $format, string $name, array $headings, iterable $rows, array $numeric = [], bool $rightToLeft = false): StreamedResponse
    {
        if ($format === 'csv') {
            return $this->csv->stream($name.'.csv', $headings, $rows);
        }

        return new StreamedResponse(function () use ($headings, $rows, $numeric, $rightToLeft) {
            $options = new Options;
            foreach (array_keys($headings) as $column) {
                $options->setColumnWidth(max(12, min(40, mb_strlen($headings[$column]) + 6)), $column + 1);
            }
            $writer = new Writer($options);
            $writer->openToFile('php://output');
            $writer->getCurrentSheet()->setName('Export')->setSheetView((new SheetView)->setRightToLeft($rightToLeft)->setFreezeRow(2));
            $bold = (new Style)->setFontBold();
            $writer->addRow(Row::fromValues($headings, $bold));
            foreach ($rows as $values) {
                $cells = [];
                foreach (array_values($values) as $index => $value) {
                    $cells[] = $this->cell($value, in_array($index, $numeric, true));
                }
                $writer->addRow(new Row($cells));
            }
            $writer->close();
        }, 200, [
            'Content-Type' => self::XLSX_TYPE,
            'Content-Disposition' => 'attachment; filename="'.preg_replace('/[^A-Za-z0-9._-]/', '_', $name).'.xlsx"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Text stays text (a worksheet never runs it as a formula); only flagged plain decimals become numbers. */
    public function cell(mixed $value, bool $numeric): Cell
    {
        if ($value === null || $value === '') {
            return Cell::fromValue('');
        }
        if ($numeric && preg_match('/\A-?\d{1,15}(\.\d{1,6})?\z/', (string) $value) && strlen(str_replace(['-', '.'], '', (string) $value)) <= 15) {
            return Cell::fromValue(str_contains((string) $value, '.') ? (float) $value : (int) $value);
        }

        return Cell::fromValue((string) $value);
    }
}
