<?php
namespace Vendor\Schoolarsystem;

class PermissionHelper {
    public static function canAccess($perm, array $userPerms = [], bool $isAdmin = false, bool $requireAll = false): bool {
        if ($isAdmin) return true;

        // Cargar mapa de permisos
        $permissionMap = include __DIR__ . '/PermissionMap.php';

        // Expandir permisos según roles asignados
        // Conservar permisos directos y, además, expandir llaves de rol heredadas.
        $expandedPerms = $userPerms;
        foreach ($userPerms as $role) {
            if (isset($permissionMap[$role])) {
                $expandedPerms = array_merge($expandedPerms, $permissionMap[$role]);
            }
        }

        $expandedPerms = array_unique($expandedPerms);

        // Normalizar entrada
        $permsToCheck = is_array($perm) ? $perm : [$perm];

        if ($requireAll) {
            return count(array_intersect($permsToCheck, $expandedPerms)) === count($permsToCheck);
        }
        return count(array_intersect($permsToCheck, $expandedPerms)) > 0;
    }
}

