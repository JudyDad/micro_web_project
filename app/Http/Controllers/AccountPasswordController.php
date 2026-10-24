<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccountPasswords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

final class AccountPasswordController
{
    private function rules(): array
    {
        return ['password' => ['required', 'string', 'confirmed', 'max:4096']];
    }

    /** Logged in: change the password without giving the old one. */
    public function edit(Request $request, string $locale, AccountPasswords $passwords): View
    {
        return view('account.password', ['forced' => $passwords->mustChange($request->user())]);
    }

    public function update(Request $request, string $locale, AccountPasswords $passwords): RedirectResponse
    {
        $data = $request->validate($this->rules());
        try {
            $passwords->change($request->user(), $data['password']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['password' => __('account.errors.'.$e->getMessage())]);
        }

        return redirect()->route('projects.index', ['locale' => $locale])->with('status', __('account.changed'));
    }

    /** Not logged in: user name and e-mail on file open the reset page. */
    public function forgot(): View
    {
        return view('auth.forgot');
    }

    public function request(Request $request, string $locale, AccountPasswords $passwords): RedirectResponse
    {
        $data = $request->validate(['username' => ['required', 'string', 'max:200'], 'email' => ['required', 'string', 'max:190']]);
        $token = $passwords->requestReset($data['username'], $data['email']);
        if ($token === null) {
            return back()->withErrors(['username' => __('account.no_match')])->withInput($request->only('username'));
        }

        return redirect()->route('auth.reset', ['locale' => $locale, 'token' => $token]);
    }

    public function reset(string $locale, string $token, AccountPasswords $passwords): View
    {
        return view('auth.reset', ['token' => $token, 'valid' => $passwords->validToken($token)]);
    }

    public function resetStore(Request $request, string $locale, string $token, AccountPasswords $passwords): RedirectResponse
    {
        $data = $request->validate($this->rules());
        try {
            $done = $passwords->reset($token, $data['password']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['password' => __('account.errors.'.$e->getMessage())]);
        }
        if (! $done) {
            return redirect()->route('auth.forgot', ['locale' => $locale])->withErrors(['username' => __('account.expired')]);
        }

        return redirect()->route('auth.login', ['locale' => $locale])->with('status', __('account.reset_done'));
    }
}
