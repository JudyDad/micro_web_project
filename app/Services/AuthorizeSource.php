<?php

namespace App\Services;

use App\Models\WebPrincipal;
use Illuminate\Support\Facades\DB;

final class AuthorizeSource
{
    public function __construct(private SourceIdentity $identity) {}

    public function check(WebPrincipal $principal, int $sourceId): bool
    {
        return $principal->source_id === $sourceId && $this->identity->activeUser($principal) !== null
            && DB::table('principal_capabilities')->where('source_id', $sourceId)->where('principal_id', $principal->getKey())
                ->whereRaw('BINARY capability = ?', ['projects.view'])->whereNull('revoked_at')->exists();
    }
}
