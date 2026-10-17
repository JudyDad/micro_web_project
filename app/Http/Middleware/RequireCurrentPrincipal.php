<?php

namespace App\Http\Middleware;

use App\Models\WebPrincipal;
use App\Services\AuditTrail;
use App\Services\SourceIdentity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireCurrentPrincipal
{
    public function __construct(private SourceIdentity $identity, private AuditTrail $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $principal = $request->user();
        if (! $principal instanceof WebPrincipal || $this->identity->activeUser($principal) === null) {
            if ($principal instanceof WebPrincipal) {
                $this->audit->record($principal->source_id, $principal->id, 'access.denied', 'denied');
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403);
        }

        return $next($request);
    }
}
