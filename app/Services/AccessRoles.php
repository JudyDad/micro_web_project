<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WebAccount;
use InvalidArgumentException;

/**
 * Named bundles of the per-city capabilities. A role is only a convenient way for an administrator to grant several capabilities at
 * once: each capability is still granted, audited (`capability.granted`) and revoked individually, so cities can differ.
 */
final class AccessRoles
{
    public const ROLES = [
        // Reads, writes and deletes applications' related records: people, applications, visits and special cases.
        'data-entry' => [
            'projects.view', 'people.view', 'people.manage', 'applications.view', 'applications.manage',
            'special_cases.view', 'special_cases.manage', 'special_cases.delete', 'visits.view', 'visits.manage', 'visits.delete',
            'me_visits.view', 'me_visits.manage', 'me_visits.delete', 'profession_meetings.view', 'profession_meetings.manage', 'profession_meetings.delete',
        ],
    ];

    public function __construct(private AccountProvisioner $provisioner) {}

    /** @return list<string> */
    public static function capabilities(string $role): array
    {
        return self::ROLES[$role] ?? throw new InvalidArgumentException('Unknown role.');
    }

    /** Grants every capability of the role in one city; already granted capabilities are refreshed, never duplicated. */
    public function assign(WebAccount $account, int $city, string $role, string $decision, ?int $actor = null): void
    {
        foreach (self::capabilities($role) as $capability) {
            $this->provisioner->grant($account, $city, $capability, $decision, $actor);
        }
    }
}
