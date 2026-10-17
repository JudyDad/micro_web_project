<?php

namespace App\Http\Middleware;

use App\Models\WebPrincipal;
use App\Services\AuditTrail;
use App\Services\AuthorizeSource;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireProjectAccess
{
    public function __construct(private AuthorizeSource $authorization, private AuditTrail $audit) {}

    public function handle(Request $request, Closure $next): Response
    {
        $principal = $request->user();
        abort_unless($principal instanceof WebPrincipal, 403);
        $source = filter_var($request->route('source'), FILTER_VALIDATE_INT);
        if ($source === false || ! $this->authorization->check($principal, $source)) {
            $this->audit->record($principal->source_id, $principal->id, 'access.denied', 'denied');
            abort(403);
        }

        return $next($request);
    }
}
