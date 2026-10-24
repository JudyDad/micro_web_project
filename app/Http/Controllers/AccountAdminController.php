<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccessRoles;
use App\Services\AccountAccess;
use App\Services\Staff\AccountAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AccountAdminController
{
    public function index(Request $request, string $locale, AccountAdminService $accounts): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'integer', 'min:1'], 'status' => ['nullable', 'in:active,disabled']]);

        return view('admin.accounts', $accounts->index($request->user(), $filters) + ['filters' => $filters]);
    }

    public function create(Request $request, string $locale, AccountAdminService $accounts): View
    {
        return view('admin.new', ['groups' => $accounts->candidates($request->user()), 'cities' => $accounts->cities($request->user()), 'roles' => array_keys(AccessRoles::ROLES)]);
    }

    public function store(Request $request, string $locale, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate([
            'identities' => ['required', 'array', 'min:1', 'max:20'], 'identities.*' => ['string', 'max:40'], 'credential' => ['required', 'string', 'max:40'],
            'username' => ['nullable', 'string', 'max:200'], 'email' => ['nullable', 'string', 'max:190'], 'confirm' => ['nullable', 'in:1'], 'roles' => ['nullable', 'array', 'max:20'], 'roles.*' => ['nullable', 'string', 'max:40'],
        ]);
        $id = $accounts->create($request->user(), $data);

        return redirect()->route('admin.accounts.show', ['locale' => $locale, 'account' => $id])->with('status', __('admin.created_msg'));
    }

    public function show(Request $request, string $locale, string $account, AccountAdminService $accounts): View
    {
        return view('admin.account', $accounts->show($request->user(), $this->id($account)));
    }

    public function capabilities(Request $request, string $locale, string $account, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', 'integer', 'min:1'], 'capabilities' => ['nullable', 'array', 'max:60'], 'capabilities.*' => ['string', 'in:'.implode(',', AccountAccess::CAPABILITIES)]]);
        $result = $accounts->saveCapabilities($request->user(), $this->id($account), (int) $data['city'], $data['capabilities'] ?? []);

        return $this->back($locale, $account)->with('status', __('admin.granted_revoked', $result));
    }

    public function role(Request $request, string $locale, string $account, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', 'integer', 'min:1'], 'role' => ['required', 'string', 'max:40']]);
        $count = $accounts->assignRole($request->user(), $this->id($account), (int) $data['city'], $data['role']);

        return $this->back($locale, $account)->with('status', __('admin.role_applied', ['count' => $count]));
    }

    public function link(Request $request, string $locale, string $account, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['identity' => ['required', 'string', 'regex:/\A[1-9][0-9]{0,9}:[1-9][0-9]{0,18}\z/']]);
        [$source, $record] = array_map('intval', explode(':', $data['identity']));
        $accounts->link($request->user(), $this->id($account), $source, $record);

        return $this->back($locale, $account)->with('status', __('admin.linked'));
    }

    public function linkState(Request $request, string $locale, string $account, string $link, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['revoked' => ['required', 'in:0,1']]);
        $accounts->setLinkRevoked($request->user(), $this->id($account), $this->id($link), $data['revoked'] === '1');

        return $this->back($locale, $account)->with('status', __('admin.link_changed'));
    }

    public function disable(Request $request, string $locale, string $account, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['disabled' => ['required', 'in:0,1']]);
        $accounts->setDisabled($request->user(), $this->id($account), $data['disabled'] === '1');

        return $this->back($locale, $account)->with('status', $data['disabled'] === '1' ? __('admin.disabled_msg') : __('admin.enabled_msg'));
    }

    public function storePlain(Request $request, string $locale, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['username' => ['required', 'string', 'max:200'], 'email' => ['required', 'string', 'max:190'], 'roles' => ['nullable', 'array', 'max:20'], 'roles.*' => ['nullable', 'string', 'max:40']]);
        $created = $accounts->createPlain($request->user(), $data);

        return redirect()->route('admin.accounts.show', ['locale' => $locale, 'account' => $created['id']])->with('status', __('admin.created_msg'))->with('one_time_password', $created['password']);
    }

    public function roleRevoke(Request $request, string $locale, string $account, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', 'integer', 'min:1'], 'role' => ['required', 'string', 'max:40']]);
        $count = $accounts->revokeRole($request->user(), $this->id($account), (int) $data['city'], $data['role']);

        return $this->back($locale, $account)->with('status', __('admin.role_revoked', ['count' => $count]));
    }

    public function email(Request $request, string $locale, string $account, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['email' => ['nullable', 'string', 'max:190']]);
        $accounts->setEmail($request->user(), $this->id($account), $data['email'] ?? null);

        return $this->back($locale, $account)->with('status', __('admin.saved'));
    }

    public function tasks(Request $request, string $locale, AccountAdminService $accounts): View
    {
        $cities = $accounts->cities($request->user());
        abort_if($cities->isEmpty(), 403);
        $city = (int) $request->integer('city', (int) $cities->first()->id);

        return view('admin.tasks', $accounts->taskMatrix($request->user(), $city));
    }

    public function saveTasks(Request $request, string $locale, AccountAdminService $accounts): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', 'integer', 'min:1'], 'assign' => ['nullable', 'array', 'max:200'], 'assign.*' => ['nullable', 'array', 'max:500'], 'assign.*.*' => ['integer', 'min:1']]);
        $result = $accounts->saveTasks($request->user(), (int) $data['city'], $data['assign'] ?? []);

        return redirect()->route('admin.tasks', ['locale' => $locale, 'city' => $data['city']])->with('status', __('admin.tasks_saved', $result));
    }

    public function staff(Request $request, string $locale, AccountAdminService $accounts): View
    {
        $filters = $request->validate(['city' => ['nullable', 'integer', 'min:1']]);

        return view('admin.staff', $accounts->staff($request->user(), $filters) + ['filters' => $filters]);
    }

    private function back(string $locale, string $account): RedirectResponse
    {
        return redirect()->route('admin.accounts.show', ['locale' => $locale, 'account' => $account]);
    }

    private function id(string $value): int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($id === false, 404);

        return $id;
    }
}
