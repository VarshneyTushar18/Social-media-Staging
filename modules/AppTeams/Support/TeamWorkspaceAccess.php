<?php

namespace Modules\AppTeams\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AppChannels\Models\SocialAccount;

class TeamWorkspaceAccess
{
    protected static array $activeTeamCache = [];
    protected static array $workspaceOwnerCache = [];
    protected static array $permissionsCache = [];
    protected static array $managedAccountIdsCache = [];

    public static function activeTeam(?User $user): ?Team
    {
        if (! $user) {
            return null;
        }

        $teamId = (int) session('portal_team_id', 0);

        if ($teamId <= 0) {
            return null;
        }

        $cacheKey = (int) $user->id.':'.$teamId;

        if (array_key_exists($cacheKey, self::$activeTeamCache)) {
            return self::$activeTeamCache[$cacheKey];
        }

        return self::$activeTeamCache[$cacheKey] = Team::query()
            ->with('owner')
            ->whereKey($teamId)
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
            })
            ->first();
    }

    /**
     * Resolve the team context used by publishing calendar, queue, and bulk imports.
     * Matches PublishingCalendar::currentTeamId() so posts remain visible in the portal.
     */
    public static function currentTeamId(?User $user): ?int
    {
        if (! $user) {
            return null;
        }

        $team = self::activeTeam($user);

        if ($team) {
            return (int) $team->id;
        }

        $ownedTeamId = Team::query()->where('owner_user_id', $user->id)->value('id');

        if ($ownedTeamId) {
            return (int) $ownedTeamId;
        }

        $memberTeamId = $user->teams()->value('teams.id');

        return $memberTeamId ? (int) $memberTeamId : null;
    }

    /**
     * Team context for publishing and approvals when portal_team_id is unset.
     * Optionally persists the resolved team to the session (same as visiting Teams / switch workspace).
     */
    public static function resolveTeam(?User $user, bool $persistSession = false): ?Team
    {
        if (! $user) {
            return null;
        }

        $team = self::activeTeam($user);

        if ($team) {
            return $team;
        }

        $teamId = self::currentTeamId($user);

        if (! $teamId) {
            return null;
        }

        $team = Team::query()
            ->with('owner')
            ->whereKey($teamId)
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
            })
            ->first();

        if ($team && $persistSession) {
            session(['portal_team_id' => (int) $team->id]);
            self::$activeTeamCache[(int) $user->id.':'.(int) $team->id] = $team;
        }

        return $team;
    }

    /**
     * @return Collection<int, Team>
     */
    public static function accessibleTeamsForUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return Team::query()
            ->with('owner')
            ->where(function ($query) use ($user) {
                $query->where('owner_user_id', $user->id)
                    ->orWhereHas('members', fn ($memberQuery) => $memberQuery->where('users.id', $user->id));
            })
            ->orderByRaw('CASE WHEN owner_user_id = ? THEN 0 ELSE 1 END', [$user->id])
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();
    }

    public static function firstTeamWhereUserCanApprovePosts(?User $user): ?Team
    {
        if (! $user) {
            return null;
        }

        foreach (self::accessibleTeamsForUser($user) as $team) {
            if (! self::teamHasModule($team, 'publishing')) {
                continue;
            }

            if (self::hasPermission($user, 'post.approve', $team)) {
                return $team;
            }
        }

        return null;
    }

    public static function canShowPublishingApprovalsNav(?User $user): bool
    {
        return self::firstTeamWhereUserCanApprovePosts($user) !== null;
    }

    public static function ensurePublishingTeamSession(?User $user): void
    {
        if (! $user) {
            return;
        }

        if ((int) session('portal_team_id', 0) > 0 && self::activeTeam($user)) {
            return;
        }

        self::resolveTeam($user, persistSession: true);
    }

    public static function bumpPublishingCalendarCache(?User $user): void
    {
        if (! $user) {
            return;
        }

        $ownerId = self::workspaceOwnerUserId($user);
        $teamId = self::currentTeamId($user) ?? 0;
        $cacheKey = 'publishing:calendar-version:v1:'.$ownerId.':'.$teamId;

        Cache::forever($cacheKey, (int) Cache::get($cacheKey, 1) + 1);
    }

    public static function workspaceOwnerUserId(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $teamId = (int) session('portal_team_id', 0);
        $cacheKey = (int) $user->id.':'.$teamId;

        if (array_key_exists($cacheKey, self::$workspaceOwnerCache)) {
            return self::$workspaceOwnerCache[$cacheKey];
        }

        $team = self::activeTeam($user);

        if ($team && (int) $team->owner_user_id > 0) {
            return self::$workspaceOwnerCache[$cacheKey] = (int) $team->owner_user_id;
        }

        return self::$workspaceOwnerCache[$cacheKey] = (int) $user->id;
    }

    public static function enabledModules(?Team $team): array
    {
        $modules = $team?->enabled_modules;
        $alwaysEnabledModules = TeamPermissionRegistry::alwaysEnabledModuleKeys();

        // `null` means the team has never saved module preferences, so fall back
        // to registry defaults. An explicit empty array means "all configurable
        // modules disabled" and must be preserved.
        if (! is_array($modules)) {
            return collect(array_merge(
                TeamPermissionRegistry::defaultEnabledModuleKeys(),
                $alwaysEnabledModules
            ))
                ->unique()
                ->values()
                ->all();
        }

        return collect(array_merge($modules, $alwaysEnabledModules))
            ->filter()
            ->map(fn ($module) => (string) $module)
            ->unique()
            ->values()
            ->all();
    }

    public static function teamHasModule(?Team $team, string $module): bool
    {
        if (! $team) {
            return true;
        }

        if (self::userBypassesWorkspaceRestrictions(auth()->user(), $team)) {
            return true;
        }

        return in_array($module, self::enabledModules($team), true);
    }

    /**
     * @return array<int, string>
     */
    public static function permissionsForUser(?User $user, ?Team $team = null): array
    {
        if (! $user) {
            return [];
        }

        $team ??= self::activeTeam($user);

        if (! $team) {
            return [];
        }

        $cacheKey = (int) $user->id.':'.(int) $team->id;

        if (array_key_exists($cacheKey, self::$permissionsCache)) {
            return self::$permissionsCache[$cacheKey];
        }

        if (self::userBypassesWorkspaceRestrictions($user, $team)) {
            return self::$permissionsCache[$cacheKey] = array_keys(TeamPermissionRegistry::permissionLabels());
        }

        $member = $team->relationLoaded('members')
            ? $team->members->firstWhere('id', $user->id)
            : null;
        $member ??= $team->members()->where('users.id', $user->id)->first();

        if (! $member) {
            return self::$permissionsCache[$cacheKey] = [];
        }

        $role = (string) ($member->pivot->role ?? 'member');
        $permissions = self::decodePermissions($member->pivot->permissions ?? null);
        $knownPermissions = collect($permissions)
            ->filter(fn ($permission) => TeamPermissionRegistry::hasPermission((string) $permission))
            ->map(fn ($permission) => (string) $permission)
            ->values()
            ->all();
        $containsLegacyPermissions = count($knownPermissions) !== count($permissions);
        $defaultPermissions = TeamPermissionRegistry::defaultPermissionsForRole($role, self::enabledModules($team));

        if ($knownPermissions === []) {
            return self::$permissionsCache[$cacheKey] = $defaultPermissions;
        }

        if ($containsLegacyPermissions) {
            return self::$permissionsCache[$cacheKey] = collect(array_merge($defaultPermissions, $knownPermissions))
                ->unique()
                ->values()
                ->all();
        }

        return self::$permissionsCache[$cacheKey] = $knownPermissions;
    }

    public static function hasPermission(?User $user, string $permission, ?Team $team = null): bool
    {
        if (! $user) {
            return false;
        }

        $team ??= self::activeTeam($user);

        if (! $team) {
            return true;
        }

        if (self::userBypassesWorkspaceRestrictions($user, $team)) {
            return true;
        }

        $module = TeamPermissionRegistry::permissionModule($permission);

        if ($module && ! self::teamHasModule($team, $module)) {
            return false;
        }

        return in_array($permission, self::permissionsForUser($user, $team), true);
    }

    public static function managedAccountIds(?User $user, ?Team $team = null): ?array
    {
        if (! $user) {
            return [];
        }

        $team ??= self::activeTeam($user);

        if (! $team) {
            return null;
        }

        $cacheKey = (int) $user->id.':'.(int) $team->id;

        if (array_key_exists($cacheKey, self::$managedAccountIdsCache)) {
            return self::$managedAccountIdsCache[$cacheKey];
        }

        if ((int) $team->owner_user_id === (int) $user->id) {
            return self::$managedAccountIdsCache[$cacheKey] = null;
        }

        $member = $team->relationLoaded('members')
            ? $team->members->firstWhere('id', $user->id)
            : null;
        $member ??= $team->members()->where('users.id', $user->id)->first();

        if (! $member) {
            return self::$managedAccountIdsCache[$cacheKey] = [];
        }

        $managedAccountIds = $member->pivot->managed_account_ids ?? null;

        if (is_string($managedAccountIds) && trim($managedAccountIds) !== '') {
            $managedAccountIds = json_decode($managedAccountIds, true);
        }

        if (! is_array($managedAccountIds)) {
            return self::$managedAccountIdsCache[$cacheKey] = [];
        }

        return self::$managedAccountIdsCache[$cacheKey] = collect($managedAccountIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public static function accessibleAccountsQuery(?User $user)
    {
        $ownerUserId = self::workspaceOwnerUserId($user);
        $query = SocialAccount::query()->where('created_by_user_id', $ownerUserId);
        $managedAccountIds = self::managedAccountIds($user);

        if (is_array($managedAccountIds)) {
            $query->whereKey($managedAccountIds);
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    protected static function decodePermissions(mixed $permissions): array
    {
        if (is_array($permissions)) {
            return collect($permissions)->filter()->map(fn ($permission) => (string) $permission)->unique()->values()->all();
        }

        if (is_string($permissions) && trim($permissions) !== '') {
            $decoded = json_decode($permissions, true);

            if (is_array($decoded)) {
                return collect($decoded)->filter()->map(fn ($permission) => (string) $permission)->unique()->values()->all();
            }
        }

        return [];
    }

    protected static function userBypassesWorkspaceRestrictions(?User $user, ?Team $team): bool
    {
        return $user !== null
            && $team !== null
            && (int) $team->owner_user_id === (int) $user->id;
    }
}
