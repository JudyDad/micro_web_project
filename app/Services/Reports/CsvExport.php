<?php

declare(strict_types=1);

namespace App\Services\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** Streams rows as a UTF-8 CSV that Excel opens with Arabic text intact; cells that could run as formulas are defused. */
final class CsvExport
{
    /**
     * @param  list<string>  $headings
     * @param  iterable<list<scalar|null>>  $rows
     */
    public function stream(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($headings, $rows) {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");
            $this->line($out, $headings);
            foreach ($rows as $row) {
                $this->line($out, $row);
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.preg_replace('/[^A-Za-z0-9._-]/', '_', $filename).'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** @param  resource  $out */
    private function line($out, array $cells): void
    {
        fputcsv($out, array_map(fn ($cell) => $this->cell($cell), $cells), ',', '"', '', "\r\n");
    }

    public function cell(mixed $value): string
    {
        $text = $value === null ? '' : (string) $value;
        // A lone "-" or "+" is a common placeholder and cannot start a formula, so it is left as it is.
        if ($text !== '' && strpbrk($text[0], "=+-@\t\r") !== false && ! preg_match('/\A-?\d+(\.\d+)?\z/', $text) && ! in_array($text, ['-', '+'], true)) {
            return "'".$text;
        }

        return $text;
    }
}
