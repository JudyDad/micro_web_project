<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class ProjectNumberAllocator
{
    /** The caller must enforce source/city permission before allocating. */
    public function allocate(int $sourceId): string
    {
        return DB::transaction(function () use ($sourceId): string {
            $sequence = DB::table('project_number_sequences')->where('source_id', $sourceId)->lockForUpdate()->first();
            if ($sequence === null) {
                throw new RuntimeException('Project sequence must be initialized from a verified source baseline.');
            }
            $number = (string) $sequence->next_number;
            // Original MP_ID is signed INT. Never allocate a value it cannot hold.
            if (! ctype_digit($number) || strlen($number) > 10 || (int) $number < 1 || (int) $number > 2147483647) {
                throw new RuntimeException('Project number sequence is invalid or exhausted.');
            }
            DB::table('project_number_sequences')->where('source_id', $sourceId)->update(['next_number' => (int) $number + 1]);

            return $number;
        }, attempts: 5);
    }
}
