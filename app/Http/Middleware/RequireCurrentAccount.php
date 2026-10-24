<?php

namespace App\Http\Middleware;

use App\Models\WebAccount;
use App\Services\AccountAccess;
use App\Services\AccountAuditTrail;
use App\Services\AccountPasswords;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class RequireCurrentAccount
{
    public function __construct(private AccountAccess $access, private AccountAuditTrail $audit, private AccountPasswords $passwords) {}

    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();
        if (! $account instanceof WebAccount || ! $this->access->active($account)) {
            if ($account instanceof WebAccount) {
                $this->audit->record($account->id, 'access.denied', 'denied');
            }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403);
        }

        // An account created with a one-time password must choose its own before anything else.
        if ($this->passwords->mustChange($account) && ! $request->routeIs('account.password', 'account.password.update')) {
            return redirect()->route('account.password', ['locale' => $request->route('locale') ?? 'ar']);
        }

        return $next($request);
    }
}
