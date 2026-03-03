<?php

namespace Modules\User\Repositories;

use Nwidart\Modules\Facades\Module;

class Permission
{
    /**
     * Cached lookup for normalized permission keys.
     *
     * @var array<string, string>|null
     */
    private static ?array $normalizedKeyLookup = null;

    /**
     * Get the permissions from all the enabled modules.
     *
     * @return array
     */
    public static function all()
    {
        return static::getEnabledModulePermissions();
    }


    /**
     * Prepare given permissions.
     *
     * @param array $permissions
     *
     * @return string
     */
    public static function prepare(array $permissions)
    {
        $preparedPermissions = [];

        foreach ($permissions as $name => $value) {
            $name = static::resolvePermissionKey((string) $name);

            if (is_null($value) || is_bool($value)) {
                $preparedPermissions[$name] = $value;

                continue;
            }

            if (!is_null(static::value($value))) {
                $preparedPermissions[$name] = static::value($value);
            }
        }

        return json_encode($preparedPermissions);
    }


    /**
     * Get the permission value.
     *
     * @param $permission
     *
     * @return bool|null
     */
    protected static function value($permission)
    {
        if ($permission === '1' || $permission === 1) {
            return true;
        }

        if ($permission === '-1' || $permission === -1) {
            return false;
        }
    }


    /**
     * Resolve and normalize incoming permission key.
     */
    private static function resolvePermissionKey(string $permission): string
    {
        $lookup = static::normalizedPermissionKeyLookup();

        return $lookup[$permission] ?? $permission;
    }


    /**
     * Build a lookup map for permission keys and their normalized variants.
     *
     * @return array<string, string>
     */
    private static function normalizedPermissionKeyLookup(): array
    {
        if (!is_null(static::$normalizedKeyLookup)) {
            return static::$normalizedKeyLookup;
        }

        $lookup = [];

        foreach (static::all() as $modulePermissions) {
            foreach ($modulePermissions as $group => $actions) {
                foreach (array_keys($actions) as $action) {
                    $permissionKey = "{$group}.{$action}";

                    $lookup[$permissionKey] = $permissionKey;
                    $lookup[str_replace('.', '_', $permissionKey)] = $permissionKey;
                }
            }
        }

        return static::$normalizedKeyLookup = $lookup;
    }


    /**
     * Get enabled module permissions.
     *
     * @return array
     */
    private static function getEnabledModulePermissions()
    {
        $permissions = [];

        foreach (Module::allEnabled() as $module) {
            $config = config('fleetcart.modules.' . $module->get('alias') . '.permissions');

            if (!is_null($config)) {
                $permissions[$module->getName()] = $config;
            }
        }

        return $permissions;
    }
}
