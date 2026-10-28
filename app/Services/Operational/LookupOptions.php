<?php

declare(strict_types=1);

namespace App\Services\Operational;

use App\Services\Applications\PaperOrder;
use Illuminate\Support\Collection;

/**
 * Lookup lists the way the desktop binds them: sorted by name for the tables it sorts by name and by original id for
 * the rest (Application_New_Form.cs:832–845, `Bind_ComboBox` DisplayMember "name"), labelled by the name column of the
 * current locale, with the parent id of cascading lists so the form can filter them as the desktop does.
 */
final class LookupOptions
{
    private const LABEL_COLUMNS = ['c_Name', 'c_Name_ar', 'c_Name_en', 'c_Priest_Name', 'c_W_Name', 'c_T_Name', 'c_Level_Description'];

    /** The display label of one lookup row, in the current locale. */
    public static function label(object $row, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $preferred = $locale === 'ar' ? ['c_Name_ar', 'c_Name', 'c_Priest_Name', 'c_W_Name', 'c_T_Name', 'c_Name_en'] : ['c_Name_en', 'c_Name', 'c_Name_ar', 'c_Priest_Name', 'c_W_Name', 'c_T_Name'];
        foreach ($preferred as $column) {
            if (isset($row->{$column}) && trim((string) $row->{$column}) !== '') {
                return (string) $row->{$column};
            }
        }
        $parts = [];
        foreach ((array) $row as $name => $value) {
            if (str_starts_with($name, 'c_') && ! preg_match('/(?:^c_ID$|_ID$)/i', $name) && $value !== null && $value !== '') {
                $parts[] = (string) $value;
            }
        }

        return implode(' · ', $parts) ?: '#'.$row->id;
    }

    private static function nameColumn(string $table): string
    {
        return match ($table) {
            'priest' => 'c_Priest_Name', 'work' => 'c_W_Name', 'parish' => 'c_Name_ar', default => 'c_Name',
        };
    }

    private static function rawIdColumn(string $table): ?string
    {
        return match ($table) {
            'priest' => 'c_Priest_ID', 'work' => 'c_W_ID', 'level' => 'c_Level_ID', 'task' => 'c_T_ID', 'user' => null, default => 'c_ID',
        };
    }

    /** @param Collection<int, object> $rows */
    public static function sortRows(Collection $rows, string $table): Collection
    {
        if (in_array($table, PaperOrder::SORT_BY_NAME, true)) {
            $column = self::nameColumn($table);

            return $rows->sortBy(fn ($row) => (string) ($row->{$column} ?? ''), SORT_STRING)->values();
        }
        $raw = self::rawIdColumn($table);

        return $rows->sortBy(fn ($row) => [(int) ($raw ? ($row->{$raw} ?? PHP_INT_MAX) : 0), (int) $row->id])->values();
    }
}
