<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\WebAccount;
use App\Services\AccountAuditTrail;
use App\Services\AuthenticateAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, AuthenticateAccount $authenticate): RedirectResponse
    {
        $data = $request->validated();
        $principal = $authenticate->attempt($data['username'], $data['password']);
        if ($principal === null) {
            return back()->withErrors(['username' => __('projects.login_error')])->withInput($request->only('username'));
        }
        Auth::login($principal, false);
        $request->session()->regenerate();

        // Never trust an intended source URL from a previous session or login.
        return redirect()->route('projects.index', ['locale' => app()->getLocale()]);
    }

    public function logout(Request $request, AccountAuditTrail $audit): RedirectResponse
    {
        $principal = $request->user();
        if ($principal instanceof WebAccount) {
            $audit->record($principal->id, 'logout.succeeded', 'succeeded');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login', ['locale' => app()->getLocale()]);
    }
}
