<?php

declare(strict_types=1);

namespace App\Services\Field;

/**
 * The four questionnaires of the opening and closing visits (`VOpening_Other_Form`, `VOpening_Taxi_Form`, `VClosing_Form`).
 * Each question has a free-text answer (`AnsN`) and, for most, a stored choice value (`AnsN_Value`); -1 means unanswered.
 * Questions without a choice (and the unused seventh slot) keep -1, as the desktop saves them.
 */
final class VisitKinds
{
    public const UNSET = -1;

    /** Choice domains: stored value => language key under `field.visit.choice`. */
    private const YES_NO = [1 => 'yes', 0 => 'no'];

    private const PERCENT = [1 => 'p25', 2 => 'p50', 3 => 'p75', 4 => 'p100'];

    /** The closing questionnaire stores the radio button's digit, so "yes" is 0 on its first question. */
    private const CLOSE_1 = [0 => 'yes', 1 => 'no'];

    private const CLOSE_2 = [0 => 'debts', 1 => 'no_change', 2 => 'good', 3 => 'excellent'];

    private const CLOSE_3 = [0 => 'declined', 1 => 'same', 2 => 'improved'];

    private const CLOSE_5 = [0 => 'sell', 1 => 'no', 2 => 'yes', 3 => 'expand'];

    /** Office-monitoring (beneficiary experience) rating: stored 0, 1, 2. */
    public const RATING = [0 => 'bad', 1 => 'acceptable', 2 => 'excellent'];

    public const KINDS = [
        'O-op' => [
            'phase' => 'opening', 'task' => 25, 'vehicle' => false, 'place' => true, 'finishes' => false, 'office' => 6, 'other_comments' => true,
            'questions' => [1 => self::YES_NO, 2 => self::YES_NO, 3 => self::YES_NO, 4 => self::PERCENT, 5 => self::YES_NO, 6 => self::YES_NO, 7 => self::YES_NO],
        ],
        'V-op' => [
            'phase' => 'opening', 'task' => 25, 'vehicle' => true, 'place' => false, 'finishes' => false, 'office' => 6, 'other_comments' => true,
            'questions' => [1 => self::YES_NO, 2 => self::YES_NO, 3 => self::PERCENT, 4 => self::YES_NO, 5 => self::YES_NO, 6 => self::YES_NO],
        ],
        'O-cl' => [
            'phase' => 'closing', 'task' => 31, 'vehicle' => false, 'place' => false, 'finishes' => true, 'office' => 4, 'other_comments' => false,
            'questions' => [1 => self::CLOSE_1, 2 => self::CLOSE_2, 3 => self::CLOSE_3, 4 => null, 5 => self::CLOSE_5, 6 => null],
        ],
        'V-cl' => [
            'phase' => 'closing', 'task' => 31, 'vehicle' => false, 'place' => false, 'finishes' => true, 'office' => 4, 'other_comments' => false,
            'questions' => [1 => self::CLOSE_1, 2 => self::CLOSE_2, 3 => self::CLOSE_3, 4 => null, 5 => self::CLOSE_5, 6 => null],
        ],
    ];

    public static function has(string $kind): bool
    {
        return isset(self::KINDS[$kind]);
    }

    public static function definition(string $kind): array
    {
        return self::KINDS[$kind] ?? abort(404);
    }

    /** Language group holding the question wording of a kind: opening visits differ by project kind, closing visits share one text. */
    public static function wording(string $kind): string
    {
        return self::definition($kind)['phase'] === 'closing' ? 'closing' : ($kind === 'V-op' ? 'opening_taxi' : 'opening_other');
    }

    /** The kind an eligible project records: vehicle projects (sub-category 1 or 2) use the taxi questionnaires. */
    public static function kindFor(string $phase, bool $vehicle): string
    {
        return ($vehicle ? 'V' : 'O').'-'.($phase === 'opening' ? 'op' : 'cl');
    }
}
