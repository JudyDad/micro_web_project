<?php

declare(strict_types=1);

namespace App\Services\Field;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\Finance\Fields;
use App\Services\Operational\OperationalTables;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Profession meetings (`ProfessionMeetings_Form`, `ProfessionMeetings_Schema.sql`): a survey of the practitioners of one profession
 * (career) in a city, followed by the meeting minutes. The survey questions, options and minute sections come from the city's catalog.
 *
 * The desktop deleted and re-inserted every detail row on each save. The web instead synchronizes the detail rows in place: unchanged
 * rows are not touched, changed rows are updated, new rows are inserted, and removed rows are deleted (created in the web) or hidden
 * (imported), so a preserved row is never destroyed by an edit. The registrars (`*_UserID`, historical source users) are not recorded.
 */
final class ProfessionMeetingService
{
    use RetiresRecords;

    public const PER_PAGE = 30;

    /** Competition levels of question 6, stored by name and sort order (`SaveCompetitionRows`). */
    public const LEVELS = [1 => 'منافسة شديدة', 2 => 'منافسة متوسطة', 3 => 'منافسة ضعيفة'];

    public const DEFAULT_CHALLENGES = ['الوضع الاقتصادي', 'اليد العاملة', 'التسويق', 'مكان المشروع', 'نقص المعدات', 'تنفيذ مرحلة من مراحل عملك خارج مكان العمل'];

    /** Child tables of a meeting, in the order the desktop clears them. */
    private const DETAILS = ['profession_meeting_answer', 'profession_meeting_option_answer', 'profession_meeting_competition', 'profession_meeting_season', 'profession_meeting_brand',
        'profession_meeting_equipment', 'profession_meeting_challenge', 'profession_meeting_participant', 'profession_meeting_attendee', 'profession_meeting_minute'];

    private const MAX_ROWS = 200;

    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private OperationalTables $tables) {}

    private function authorize(WebAccount $account, int $city, string ...$capabilities): void
    {
        foreach (['profession_meetings.view', ...$capabilities] as $capability) {
            if (! $this->access->check($account, $city, $capability)) {
                $this->audit->record($account->id, 'access.denied', 'denied');
                abort(403);
            }
        }
    }

    private function fail(string $field, string $key, array $replace = []): never
    {
        throw ValidationException::withMessages([$field => __('field.meeting.errors.'.$key, $replace)]);
    }

    public function cities(WebAccount $account, string $capability = 'profession_meetings.view'): Collection
    {
        return $this->access->cities($account, $capability);
    }

    /** @return array{rows: LengthAwarePaginator, cities: Collection, creatable: Collection} */
    public function index(WebAccount $account, array $filters): array
    {
        $permitted = $this->cities($account);
        if ($permitted->isEmpty()) {
            $this->audit->record($account->id, 'access.denied', 'denied');
            abort(403);
        }
        $selected = array_values(array_intersect($filters['cities'] ?? [], $permitted->pluck('id')->all()));
        $ids = $selected !== [] ? $selected : $permitted->pluck('id')->all();
        $this->audit->record($account->id, 'profession_meetings.list', 'succeeded');

        $query = $this->tables->query('profession_meeting')->whereIn('p.city_id', $ids);
        if (! empty($filters['year'])) {
            $query->whereBetween('p.c_MeetingDate', [sprintf('%04d-01-01', $filters['year']), sprintf('%04d-12-31', $filters['year'])]);
        }

        $rows = $query->orderByDesc('p.c_MeetingDate')->orderByDesc('p.id')->paginate(self::PER_PAGE)->withQueryString();
        $careerRef = $this->tables->relation('profession_meeting', 'Career_ID');
        $names = [];
        foreach ($rows->getCollection()->unique(fn ($r) => $r->city_id.'/'.$r->source_id) as $sample) {
            foreach ($this->careers((int) $sample->city_id, (int) $sample->source_id) as $career) {
                $names[$sample->city_id.'/'.$career->id] = $career->c_Name;
            }
        }
        $rows->setCollection($rows->getCollection()->map(function ($row) use ($names, $careerRef) {
            $row->career_name = $names[$row->city_id.'/'.$row->{$careerRef['resolved_column']}] ?? null;

            return $row;
        }));

        return [
            'rows' => $rows,
            'cities' => $permitted, 'creatable' => $this->cities($account, 'profession_meetings.manage'),
        ];
    }

    private function meeting(int $id, bool $deleted = false): object
    {
        $row = $this->tables->query('profession_meeting', $deleted)->where('p.id', $id)->first();
        abort_if($row === null, 404);

        return $row;
    }

    /** All detail rows of a meeting keyed by table. */
    private function details(int $meetingId): array
    {
        $result = [];
        foreach (self::DETAILS as $table) {
            $result[$table] = $this->childRows($table, $meetingId);
        }

        return $result;
    }

    private function childRows(string $table, int $meetingId): Collection
    {
        $ref = $this->tables->relation($table, 'Meeting_ID');
        $query = $this->tables->query($table)->where('p.'.$ref['resolved_column'], $meetingId)->where('p.'.$ref['state_column'], 'resolved');
        if (in_array($table, ['profession_meeting_answer', 'profession_meeting_option_answer', 'profession_meeting_minute'], true)) {
            return $query->orderBy('p.id')->get();
        }

        return $query->orderBy('p.c_Sort_Order')->orderBy('p.id')->get();
    }

    private function version(object $meeting, array $details): string
    {
        $strip = fn (Collection $rows) => $rows->map(fn ($row) => collect((array) $row)->except(['city_name_en', 'city_name_ar'])->all())->values()->all();

        return hash('sha256', json_encode([collect((array) $meeting)->except(['city_name_en', 'city_name_ar'])->all(), array_map($strip, $details)], JSON_THROW_ON_ERROR));
    }

    /** A new meeting's form version: it changes as soon as any meeting is added to the city, so a repeated submission is refused. */
    private function newVersion(int $city): string
    {
        return hash('sha256', 'new|'.$city.'|'.(int) DB::table('op_profession_meeting')->where('city_id', $city)->max('id'));
    }

    /** Catalog of the survey for one city: sections with their questions and options, plus the minute sections. */
    private function catalog(int $city, int $source): array
    {
        $sectionRef = $this->tables->relation('profession_question', 'Section_ID');
        $optionRef = $this->tables->relation('profession_option', 'Question_ID');
        $options = $this->tables->catalog('profession_option', $city, $source)->groupBy(fn ($o) => (int) $o->{$optionRef['resolved_column']});
        $questions = $this->tables->catalog('profession_question', $city, $source)->filter(fn ($q) => (int) ($q->c_Is_Active ?? 1) === 1)
            ->sortBy(fn ($q) => (int) ($q->c_Sort_Order ?? 0))->map(function ($q) use ($options, $sectionRef) {
                $q->options = ($options->get((int) $q->id) ?? collect())->sortBy(fn ($o) => (int) $o->c_Sort_Order)->values();
                $q->section = (int) $q->{$sectionRef['resolved_column']};

                return $q;
            })->values();
        $sections = $this->tables->catalog('profession_section', $city, $source)->sortBy(fn ($s) => (int) $s->c_Sort_Order)->values();

        return ['sections' => $sections, 'questions' => $questions];
    }

    /** Careers of the city with their sub-category, for the profession selector. */
    private function careers(int $city, int $source): Collection
    {
        return $this->tables->catalog('career', $city, $source)->sortBy('c_Name', SORT_STRING)->values();
    }

    /** Read model for a new meeting ($meetingId null, $city given) or an existing one. */
    public function form(WebAccount $account, ?int $meetingId, ?int $city): array
    {
        if ($meetingId !== null) {
            $meeting = $this->meeting($meetingId);
            $city = (int) $meeting->city_id;
        } else {
            abort_if($city === null, 404);
            $meeting = null;
        }
        $this->authorize($account, $city);
        $canManage = $this->access->check($account, $city, 'profession_meetings.manage');
        abort_if($meeting === null && ! $canManage, 404);
        $source = $meeting !== null ? (int) $meeting->source_id : $this->tables->source($city);
        $details = $meeting === null ? [] : $this->details($meetingId);
        $cityRow = DB::table('cities')->where('id', $city)->first();
        $this->audit->record($account->id, 'profession_meetings.view', 'succeeded', $city, subjectType: $meeting === null ? null : 'meeting_entity', subjectId: $meetingId);

        return [
            'meeting' => $meeting, 'city' => $cityRow, 'details' => $details, 'values' => $this->values($meeting, $details), 'catalog' => $this->catalog($city, $source), 'careers' => $this->careers($city, $source),
            'canManage' => $canManage, 'canDelete' => $meeting !== null && $this->mayRetire($account, $city, 'profession_meetings.delete', $meeting),
            'version' => $meeting === null ? $this->newVersion($city) : $this->version($meeting, $details),
            'levels' => self::LEVELS, 'defaultChallenges' => self::DEFAULT_CHALLENGES,
        ];
    }

    /** The stored meeting in the shape the form posts it, to prefill the page. */
    private function values(?object $meeting, array $details): array
    {
        if ($meeting === null) {
            return ['career_id' => '', 'date' => now()->toDateString(), 'place' => '', 'invited' => '', 'attendance' => '', 'oc' => [], 'os' => [], 'ot' => [], 'tx' => [], 'cc' => [], 'cr' => [],
                'season' => [], 'equipment' => [], 'challenge' => [], 'brands' => '', 'part' => [], 'attendees' => [], 'minutes' => []];
        }
        $ref = fn (string $table, string $key) => $this->tables->relation($table, $key)['resolved_column'];
        $values = [
            'career_id' => $meeting->{$ref('profession_meeting', 'Career_ID')}, 'date' => Fields::day($meeting->c_MeetingDate), 'place' => $meeting->c_Place,
            'invited' => $meeting->c_InvitedCount, 'attendance' => $meeting->c_AttendanceCount, 'oc' => [], 'os' => [], 'ot' => [], 'tx' => [], 'cc' => [], 'cr' => [],
            'season' => [], 'equipment' => [], 'challenge' => [], 'part' => [], 'attendees' => [], 'minutes' => [],
        ];
        foreach ($details['profession_meeting_option_answer'] as $row) {
            $id = $row->{$ref('profession_meeting_option_answer', 'Option_ID')};
            $values['oc'][$id] = $row->c_Rank_Value;
            $values['os'][$id] = (int) $row->c_Is_Selected === 1 ? '1' : '';
            $values['ot'][$id] = $row->c_Other_Text;
        }
        foreach ($details['profession_meeting_answer'] as $row) {
            $values['tx'][$row->{$ref('profession_meeting_answer', 'Question_ID')}] = $row->c_Value_Text;
        }
        foreach ($details['profession_meeting_competition'] as $row) {
            $values['cc'][(int) $row->c_Sort_Order] = $row->c_CountValue;
            $values['cr'][(int) $row->c_Sort_Order] = $row->c_Reason;
        }
        foreach ($details['profession_meeting_season'] as $row) {
            $values['season'][] = ['count' => $row->c_Season_Count, 'from' => $row->c_From_Period, 'to' => $row->c_To_Period, 'reason' => $row->c_Reason];
        }
        foreach ($details['profession_meeting_equipment'] as $row) {
            $values['equipment'][] = ['spec' => $row->c_Specialization, 'items' => $row->c_Equipment];
        }
        foreach ($details['profession_meeting_challenge'] as $row) {
            $values['challenge'][] = ['name' => $row->c_Challenge, 'count' => $row->c_Challenge_Count, 'solution' => $row->c_Suggested_Solution];
        }
        $values['brands'] = $details['profession_meeting_brand']->pluck('c_Brand_Name')->implode("\n");
        $numbers = $this->projectNumbers(collect([...$details['profession_meeting_participant'], ...$details['profession_meeting_attendee']]));
        foreach ($details['profession_meeting_participant'] as $row) {
            $values['part'][$row->{$ref('profession_meeting_participant', 'Question_ID')}][] = [
                'project' => $numbers[$row->{$ref('profession_meeting_participant', 'MicroProject_ID')}] ?? '', 'name' => $row->c_Person_Name, 'speciality' => $row->c_Speciality,
            ];
        }
        foreach ($details['profession_meeting_attendee'] as $row) {
            $values['attendees'][] = ['project' => $numbers[$row->{$ref('profession_meeting_attendee', 'MicroProject_ID')}] ?? '', 'name' => $row->c_Attendee_Name, 'speciality' => $row->c_Speciality];
        }
        foreach ($details['profession_meeting_minute'] as $row) {
            $values['minutes'][$row->{$ref('profession_meeting_minute', 'Section_ID')}] = $row->c_Notes;
        }

        return $values;
    }

    /** Legacy project numbers of the projects the given participant/attendee rows point to, keyed by project id. */
    private function projectNumbers(Collection $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            foreach (['profession_meeting_participant', 'profession_meeting_attendee'] as $table) {
                $column = $this->tables->relation($table, 'MicroProject_ID')['resolved_column'];
                if (isset($row->{$column}) && $row->{$column} !== null) {
                    $ids[(int) $row->{$column}] = true;
                    break;
                }
            }
        }

        return $ids === [] ? [] : DB::table('op_microproject')->whereIn('id', array_keys($ids))->pluck('c_MP_ID', 'id')->all();
    }

    private function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function nullable(mixed $value): ?string
    {
        $text = $this->text($value);

        return $text === '' ? null : $text;
    }

    /** A whole-number field: blank is null, anything but digits is refused. */
    private function wholeNumber(mixed $value, string $field): ?int
    {
        $text = $this->text($value);
        if ($text === '') {
            return null;
        }
        (preg_match('/\A\d{1,6}\z/', $text) === 1) || $this->fail($field, 'number');

        return (int) $text;
    }

    /** Rows of a submitted table, dropping completely blank ones. */
    private function rows(mixed $raw, array $keys): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $rows = [];
        foreach (array_slice(array_values($raw), 0, self::MAX_ROWS) as $row) {
            $row = is_array($row) ? $row : [];
            $clean = [];
            foreach ($keys as $key) {
                $clean[$key] = $this->text($row[$key] ?? '');
            }
            if (implode('', $clean) !== '') {
                $rows[] = $clean;
            }
        }

        return $rows;
    }

    /**
     * Saves a meeting. $input: career_id, date, place, invited, attendance, oc/os/ot (option count, selected, text by option id), tx (text
     * by question id), cc/cr (competition count and reason by level), season/equipment/challenge rows, brands (lines), part (participants
     * by question id), attendees, minutes (by section id). Returns the meeting id.
     */
    public function save(WebAccount $account, ?int $meetingId, ?int $city, array $input, string $version): int
    {
        return DB::transaction(function () use ($account, $meetingId, $city, $input, $version) {
            if ($meetingId !== null) {
                DB::table('op_profession_meeting')->where('id', $meetingId)->lockForUpdate()->first();
                $meeting = $this->meeting($meetingId);
                $city = (int) $meeting->city_id;
            } else {
                abort_if($city === null, 404);
                $meeting = null;
            }
            $this->authorize($account, $city, 'profession_meetings.manage');
            $source = $meeting !== null ? (int) $meeting->source_id : $this->tables->source($city);
            $details = $meeting === null ? [] : $this->details($meetingId);
            abort_unless(hash_equals($meeting === null ? $this->newVersion($city) : $this->version($meeting, $details), $version), 409, 'The meeting changed. Reload before saving.');
            $catalog = $this->catalog($city, $source);
            $plan = $this->plan($city, $source, $catalog, $input);

            $headerPayload = $plan['header'];
            if ($meeting === null) {
                $meetingId = $this->tables->insert('profession_meeting', $city, $source, $headerPayload + ['c_Created_At' => now()->format('Y-m-d H:i:s'), 'c_Updated_At' => now()->format('Y-m-d H:i:s')]);
            } else {
                $changed = $this->changes($meeting, $headerPayload);
                if ($changed !== []) {
                    DB::table('op_profession_meeting')->where('id', $meetingId)->update($changed + ['c_Updated_At' => now()->format('Y-m-d H:i:s')]);
                }
            }
            $link = fn (string $table) => $this->tables->link($table, 'Meeting_ID', $meetingId);
            foreach ($plan['details'] as $table => $desired) {
                $existing = $meeting === null ? collect() : $details['profession_meeting_'.$table];
                $this->sync($account, $city, $source, 'profession_meeting_'.$table, $existing, $desired, $link('profession_meeting_'.$table));
            }
            $this->audit->record($account->id, 'profession_meetings.saved', 'succeeded', $city, subjectType: 'meeting_entity', subjectId: $meetingId);

            return $meetingId;
        });
    }

    /** Payload columns whose stored value differs (null-safe, string comparison; dates by calendar day). */
    private function changes(object $row, array $payload): array
    {
        $changed = [];
        foreach ($payload as $column => $value) {
            $stored = $row->{$column} ?? null;
            $same = $column === 'c_MeetingDate' ? Fields::day($stored) === (string) $value : ($stored === null ? null : (string) $stored) === ($value === null ? null : (string) $value);
            if (! $same) {
                $changed[$column] = $value;
            }
        }

        return $changed;
    }

    /**
     * Updates, inserts and retires the rows of one detail table. $desired maps a key to a payload; existing rows are matched by the same
     * key ($existing is keyed by it through {@see rowKey()}).
     */
    private function sync(WebAccount $account, int $city, int $source, string $table, Collection $existing, array $desired, array $link): void
    {
        $byKey = [];
        foreach ($existing as $position => $row) {
            $byKey[$this->rowKey($table, $row, $position)] ??= $row;
        }
        foreach ($desired as $key => $payload) {
            $row = $byKey[$key] ?? null;
            if ($row === null) {
                $this->tables->insert($table, $city, $source, $payload + $link);

                continue;
            }
            $changed = $this->changes($row, $payload);
            if ($changed !== []) {
                DB::table($this->tables->table($table)['name'])->where('id', $row->id)->update($changed);
            }
            unset($byKey[$key]);
        }
        foreach ($byKey as $row) {
            if ($this->tables->isWeb($row)) {
                $this->tables->deleteWeb($table, $row);
            } else {
                $this->tables->hide($table, $row, $account->id);
            }
        }
    }

    /** The key matching an existing detail row to a desired one: its question, option, section or position. */
    private function rowKey(string $table, object $row, int $position): string
    {
        return match ($table) {
            'profession_meeting_answer' => 'q'.$row->{$this->tables->relation($table, 'Question_ID')['resolved_column']},
            'profession_meeting_option_answer' => 'o'.$row->{$this->tables->relation($table, 'Option_ID')['resolved_column']},
            'profession_meeting_minute' => 's'.$row->{$this->tables->relation($table, 'Section_ID')['resolved_column']},
            'profession_meeting_participant' => 'q'.$row->{$this->tables->relation($table, 'Question_ID')['resolved_column']}.'#'.$this->positionInGroup($row, $position),
            default => 'i'.$position,
        };
    }

    private function positionInGroup(object $row, int $position): int
    {
        return (int) ($row->c_Sort_Order ?? $position);
    }

    /** Validates the submission and builds the header payload and the desired detail rows. */
    private function plan(int $city, int $source, array $catalog, array $input): array
    {
        $careerId = Fields::blank($input['career_id'] ?? null) ? null : (int) $input['career_id'];
        $career = $careerId === null ? null : $this->tables->find('career', $careerId, $city, $source);
        $career !== null || $this->fail('career_id', 'career');
        $date = $this->text($input['date'] ?? '');
        $date === '' && ($date = now()->toDateString());
        Fields::isDate($date) || $this->fail('date', 'date');
        $place = $this->text($input['place'] ?? '');
        ($place !== '' && mb_strlen($place) <= 200) || $this->fail('place', 'place');
        $invited = $this->wholeNumber($input['invited'] ?? null, 'invited') ?? 0;
        $attendance = $this->wholeNumber($input['attendance'] ?? null, 'attendance') ?? 0;
        $invited > 0 || $this->fail('invited', 'invited');
        $attendance > 0 || $this->fail('attendance', 'attendance');
        $attendance <= $invited || $this->fail('attendance', 'attendance_over');

        $careerRef = $this->tables->relation('profession_meeting', 'Career_ID');
        $subRef = $this->tables->relation('career', 'SubCategory_ID');
        $subId = $career->{$subRef['state_column']} === 'resolved' ? (int) $career->{$subRef['resolved_column']} : null;
        $header = [
            'c_MeetingDate' => $date, 'c_Place' => $place, 'c_InvitedCount' => $invited, 'c_AttendanceCount' => $attendance, 'c_AbsenceCount' => $invited - $attendance,
        ] + $this->tables->link('profession_meeting', 'Career_ID', $careerId) + $this->tables->link('profession_meeting', 'SubCategory_ID', $subId);

        $details = ['answer' => [], 'option_answer' => [], 'competition' => [], 'season' => [], 'brand' => [], 'equipment' => [], 'challenge' => [], 'participant' => [], 'attendee' => [], 'minute' => []];
        $oc = is_array($input['oc'] ?? null) ? $input['oc'] : [];
        $os = is_array($input['os'] ?? null) ? $input['os'] : [];
        $ot = is_array($input['ot'] ?? null) ? $input['ot'] : [];
        $tx = is_array($input['tx'] ?? null) ? $input['tx'] : [];
        $optionLink = fn (int $optionId) => $this->tables->link('profession_meeting_option_answer', 'Option_ID', $optionId);

        foreach ($catalog['questions'] as $question) {
            $number = (string) $question->c_Number;
            $field = 'question'.$question->id;
            $type = (string) $question->c_Answer_Type;
            if ($type === 'option_number') {
                $total = 0;
                foreach ($question->options as $option) {
                    $count = $this->wholeNumber($oc[$option->id] ?? null, $field) ?? 0;
                    $total += $count;
                    if ($count <= 0) {
                        continue;
                    }
                    $other = $this->nullable($ot[$option->id] ?? null);
                    if ((int) ($option->c_Has_Details ?? 0) === 1 || (int) ($option->c_Is_Other ?? 0) === 1) {
                        $other !== null || $this->fail($field, 'other_text', ['n' => $number]);
                    }
                    $details['option_answer']['o'.$option->id] = ['c_Is_Selected' => 1, 'c_Rank_Value' => $count, 'c_Other_Text' => $other] + $optionLink($option->id);
                }
                $total === $attendance || $this->fail($field, 'total', ['n' => $number]);
            } elseif ($type === 'multi_option') {
                $chosen = 0;
                foreach ($question->options as $option) {
                    if (empty($os[$option->id])) {
                        continue;
                    }
                    $chosen++;
                    $other = $this->nullable($ot[$option->id] ?? null);
                    if ((int) ($option->c_Has_Details ?? 0) === 1 || (int) ($option->c_Is_Other ?? 0) === 1) {
                        $other !== null || $this->fail($field, 'other_text', ['n' => $number]);
                    }
                    $details['option_answer']['o'.$option->id] = ['c_Is_Selected' => 1, 'c_Rank_Value' => null, 'c_Other_Text' => $other] + $optionLink($option->id);
                }
                $chosen > 0 || $this->fail($field, 'answer', ['n' => $number]);
            } elseif ($type === 'text') {
                $value = $this->text($tx[$question->id] ?? '');
                $value !== '' || $this->fail($field, 'answer', ['n' => $number]);
                $details['answer']['q'.$question->id] = ['c_Value_Text' => $value, 'c_Value_Number' => null] + $this->tables->link('profession_meeting_answer', 'Question_ID', (int) $question->id);
            }
        }

        $this->planCompetition($input, $attendance, $details);
        $this->planTables($city, $source, $careerId, $catalog, $input, $attendance, $details);

        $minutes = is_array($input['minutes'] ?? null) ? $input['minutes'] : [];
        foreach ($catalog['sections'] as $section) {
            $text = $this->text($minutes[$section->id] ?? '');
            $text !== '' || $this->fail('minutes', 'minutes');
            $details['minute']['s'.$section->id] = ['c_Notes' => $text] + $this->tables->link('profession_meeting_minute', 'Section_ID', (int) $section->id);
        }

        return ['header' => $header, 'details' => $details];
    }

    private function planCompetition(array $input, int $attendance, array &$details): void
    {
        $counts = is_array($input['cc'] ?? null) ? $input['cc'] : [];
        $reasons = is_array($input['cr'] ?? null) ? $input['cr'] : [];
        $total = 0;
        foreach (self::LEVELS as $order => $level) {
            $count = $this->wholeNumber($counts[$order] ?? null, 'competition') ?? 0;
            $reason = $this->nullable($reasons[$order] ?? null);
            ($count <= 0 || $reason !== null) || $this->fail('competition', 'competition_reason');
            $total += $count;
            $details['competition']['i'.($order - 1)] = ['c_Competition_Level' => $level, 'c_CountValue' => $count, 'c_Reason' => $reason, 'c_Sort_Order' => $order];
        }
        $total === $attendance || $this->fail('competition', 'total', ['n' => '6']);
    }

    private function planTables(int $city, int $source, int $careerId, array $catalog, array $input, int $attendance, array &$details): void
    {
        $questionIds = $catalog['questions']->keyBy(fn ($q) => (string) $q->c_Number);

        $order = 0;
        $total = 0;
        foreach ($this->rows($input['season'] ?? null, ['count', 'from', 'to', 'reason']) as $row) {
            $count = $this->wholeNumber($row['count'], 'season');
            if ($count !== null) {
                ($row['from'] !== '' && $row['to'] !== '' && $row['reason'] !== '') || $this->fail('season', 'season_row');
                $total += $count;
            }
            $key = 'i'.$order;
            $order++;
            $details['season'][$key] = ['c_Season_Count' => $count, 'c_From_Period' => $this->nullable($row['from']), 'c_To_Period' => $this->nullable($row['to']), 'c_Reason' => $this->nullable($row['reason']), 'c_Sort_Order' => $order];
        }
        $total === $attendance || $this->fail('season', 'total', ['n' => '8']);

        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', $this->text($input['brands'] ?? '')) ?: []), fn ($line) => $line !== ''));
        $lines !== [] || $this->fail('brands', 'answer', ['n' => '10']);
        foreach (array_slice($lines, 0, self::MAX_ROWS) as $i => $line) {
            mb_strlen($line) <= 200 || $this->fail('brands', 'long');
            $details['brand']['i'.$i] = ['c_Brand_Name' => $line, 'c_Sort_Order' => $i + 1];
        }

        $order = 0;
        foreach ($this->rows($input['equipment'] ?? null, ['spec', 'items']) as $row) {
            ($row['spec'] !== '' && $row['items'] !== '') || $this->fail('equipment', 'equipment_row');
            $key = 'i'.$order;
            $order++;
            $details['equipment'][$key] = ['c_Specialization' => $row['spec'], 'c_Equipment' => $row['items'], 'c_Sort_Order' => $order];
        }
        $order > 0 || $this->fail('equipment', 'answer', ['n' => '12']);

        $order = 0;
        $total = 0;
        foreach ($this->rows($input['challenge'] ?? null, ['name', 'count', 'solution']) as $row) {
            $count = $this->wholeNumber($row['count'], 'challenge');
            ($count === null || $count <= 0 || $row['solution'] !== '') || $this->fail('challenge', 'challenge_solution');
            $total += $count ?? 0;
            $key = 'i'.$order;
            $order++;
            $details['challenge'][$key] = ['c_Challenge' => $this->nullable($row['name']), 'c_Challenge_Count' => $count, 'c_Suggested_Solution' => $this->nullable($row['solution']), 'c_Sort_Order' => $order];
        }
        $total === $attendance || $this->fail('challenge', 'total', ['n' => '16']);

        foreach (['19', '20', '21', '22'] as $number) {
            $question = $questionIds->get($number);
            if ($question === null) {
                continue;
            }
            $order = 0;
            foreach ($this->rows($input['part'][$question->id] ?? null, ['project', 'name', 'speciality']) as $row) {
                $order++;
                $details['participant']['q'.$question->id.'#'.$order] = ['c_Person_Name' => $this->nullable($row['name']), 'c_Speciality' => $this->nullable($row['speciality']), 'c_Notes' => null, 'c_Sort_Order' => $order]
                    + $this->tables->link('profession_meeting_participant', 'Question_ID', (int) $question->id) + $this->personLinks('profession_meeting_participant', $city, $source, $careerId, $row);
            }
        }

        $order = 0;
        foreach ($this->rows($input['attendees'] ?? null, ['project', 'name', 'speciality']) as $row) {
            $key = 'i'.$order;
            $order++;
            $details['attendee'][$key] = ['c_Attendee_Name' => $this->nullable($row['name']), 'c_Speciality' => $this->nullable($row['speciality']), 'c_Sort_Order' => $order]
                + $this->personLinks('profession_meeting_attendee', $city, $source, $careerId, $row);
        }
        $order === $attendance || $this->fail('attendees', 'attendees_total');
    }

    /**
     * The project (by legacy number, of this city and profession) and, when exactly one person linked to it has the typed name, the person.
     * An unknown project number is refused.
     */
    private function personLinks(string $table, int $city, int $source, int $careerId, array $row): array
    {
        $links = $this->tables->link($table, 'MicroProject_ID', null) + $this->tables->link($table, 'Person_ID', null);
        if ($row['project'] === '') {
            return $links;
        }
        $careerRef = $this->tables->relation('microproject', 'Career_ID');
        $project = $this->tables->query('microproject')->where('p.city_id', $city)->where('p.source_id', $source)->where('p.c_MP_ID', $row['project'])
            ->where('p.'.$careerRef['resolved_column'], $careerId)->first();
        $project !== null || $this->fail('project', 'project_unknown', ['n' => $row['project']]);
        $links = $this->tables->link($table, 'MicroProject_ID', (int) $project->id) + $this->tables->link($table, 'Person_ID', null);

        $projectRef = $this->tables->relation('person_microproject', 'MicroProject_ID');
        $personRef = $this->tables->relation('person_microproject', 'Person_ID');
        $personIds = $this->tables->query('person_microproject')->where('p.'.$projectRef['resolved_column'], $project->id)->where('p.'.$personRef['state_column'], 'resolved')
            ->pluck('p.'.$personRef['resolved_column'])->unique()->all();
        $matches = $personIds === [] ? collect() : $this->tables->query('person')->whereIn('p.id', $personIds)->get()->filter(function ($person) use ($row) {
            $first = trim(($person->c_P_FirstName ?? '').' '.($person->c_P_LastName ?? ''));

            return $row['name'] !== '' && ($row['name'] === $first || $row['name'] === trim($first.' ابن/ة '.($person->c_P_FatherName ?? '')));
        });

        return $matches->count() === 1 ? $this->tables->link($table, 'MicroProject_ID', (int) $project->id) + $this->tables->link($table, 'Person_ID', (int) $matches->first()->id) : $links;
    }

    /** Deletes a web-created meeting with its details, or hides an imported one (needs `imported.delete`). */
    public function delete(WebAccount $account, int $meetingId, string $version): void
    {
        DB::transaction(function () use ($account, $meetingId, $version) {
            DB::table('op_profession_meeting')->where('id', $meetingId)->lockForUpdate()->first();
            $meeting = $this->meeting($meetingId);
            $city = (int) $meeting->city_id;
            $this->authorize($account, $city, 'profession_meetings.delete');
            abort_unless(hash_equals($this->version($meeting, $this->details($meetingId)), $version), 409, 'The meeting changed. Reload before deleting.');
            // Audited while the meeting still exists: the audit trail checks that its subject is a real meeting of the city.
            $this->audit->record($account->id, 'profession_meetings.deleted', 'succeeded', $city, subjectType: 'meeting_entity', subjectId: $meetingId);
            $this->retire($account, $city, 'profession_meeting', $meeting, function () use ($meetingId) {
                foreach ($this->details($meetingId) as $table => $rows) {
                    foreach ($rows as $row) {
                        $this->tables->isWeb($row) || $this->fail('delete', 'imported');
                        $this->tables->deleteWeb($table, $row);
                    }
                }
            });
        });
    }

    /** Hidden imported meetings of the permitted cities, for the administrators who may restore them. */
    public function deleted(WebAccount $account): Collection
    {
        $cities = $this->access->cities($account, 'imported.delete')->pluck('id')->all();
        $cities = array_values(array_intersect($cities, $this->access->cities($account, 'profession_meetings.delete')->pluck('id')->all()));

        return $cities === [] ? collect() : $this->tables->query('profession_meeting', true)->whereIn('p.city_id', $cities)->orderByDesc('p.c_MeetingDate')->get();
    }

    public function restore(WebAccount $account, int $meetingId): void
    {
        DB::transaction(function () use ($account, $meetingId) {
            $meeting = $this->meeting($meetingId, true);
            $city = (int) $meeting->city_id;
            $this->authorize($account, $city, 'profession_meetings.delete');
            $this->requireImportedDelete($account, $city);
            $this->tables->unhide('profession_meeting', $meetingId);
            $this->audit->record($account->id, 'profession_meetings.restored', 'succeeded', $city, subjectType: 'meeting_entity', subjectId: $meetingId);
        });
    }
}
