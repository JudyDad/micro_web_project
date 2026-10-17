<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\WebPrincipal;
use App\Services\AuditTrail;
use App\Services\AuthenticatePrincipal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController
{
    public function show(): View
    {
        return view('auth.login', ['cities' => DB::table('sources as s')->join('cities as c', 'c.id', '=', 's.city_id')->select(['s.id', 'c.name_en', 'c.name_ar'])->orderBy('s.id')->get()]);
    }

    public function login(LoginRequest $request, AuthenticatePrincipal $authenticate): RedirectResponse
    {
        $data = $request->validated();
        $principal = $authenticate->attempt((int) $data['source_id'], $data['username'], $data['password']);
        if ($principal === null) {
            return back()->withErrors(['username' => __('projects.login_error')])->withInput($request->only('source_id', 'username'));
        }
        Auth::login($principal, false);
        $request->session()->regenerate();

        // Never trust an intended source URL from a previous session or login.
        return redirect()->route('cities.index', ['locale' => app()->getLocale()]);
    }

    public function logout(Request $request, AuditTrail $audit): RedirectResponse
    {
        $principal = $request->user();
        if ($principal instanceof WebPrincipal) {
            $audit->record($principal->source_id, $principal->id, 'logout.succeeded', 'succeeded', 'principal', $principal->id);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login', ['locale' => app()->getLocale()]);
    }
}
