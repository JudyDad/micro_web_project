<?php

declare(strict_types=1);

namespace App\Services\Reports\Search;

/**
 * The search reports available in the web, by key: all 22 of the desktop's modes. Twelve are detail-table lists defined as data in
 * `DetailSearch::DEFINITIONS`; each of the others is a class. A new mode gets the page, the filters and the exports from the registry.
 */
final class SearchModes
{
    /** @var array<string, class-string<SearchMode>> */
    public const MODES = [
        'applications' => ApplicationSearch::class, 'family' => FamilySearch::class, 'loans' => LoanSearch::class, 'attachments' => AttachmentSearch::class,
        'visit-dates' => VisitDatesSearch::class, 'me-visits' => MeVisitSearch::class, 'checklists' => ChecklistSearch::class, 'special-cases' => SpecialCaseSearch::class,
        'educations' => EducationSearch::class, 'meetings' => MeetingSearch::class,
    ];

    public static function keys(): array
    {
        return [...array_keys(self::MODES), ...array_keys(DetailSearch::DEFINITIONS)];
    }

    public static function make(string $key): SearchMode
    {
        if (isset(DetailSearch::DEFINITIONS[$key])) {
            return app()->makeWith(DetailSearch::class, ['key' => $key]);
        }
        abort_unless(isset(self::MODES[$key]), 404);

        return app(self::MODES[$key]);
    }
}
