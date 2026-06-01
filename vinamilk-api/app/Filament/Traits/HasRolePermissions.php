<?php

namespace App\Filament\Traits;

trait HasRolePermissions
{
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        // Super Admin bypasses all restrictions
        if ($user->hasRole('Super Admin')) return true;

        // Get the navigation identifier for this resource
        $resourceIdentifier = static::getNavigationIdentifier();
        
        // Check if user has permission to view this resource
        return $user->can("view {$resourceIdentifier}");
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if ($user->hasRole('Super Admin')) return true;

        $resourceIdentifier = static::getNavigationIdentifier();
        return $user->can("create {$resourceIdentifier}");
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if ($user->hasRole('Super Admin')) return true;

        $resourceIdentifier = static::getNavigationIdentifier();
        return $user->can("edit {$resourceIdentifier}");
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if ($user->hasRole('Super Admin')) return true;

        $resourceIdentifier = static::getNavigationIdentifier();
        return $user->can("delete {$resourceIdentifier}");
    }

    public static function canView($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if ($user->hasRole('Super Admin')) return true;

        $resourceIdentifier = static::getNavigationIdentifier();
        return $user->can("view {$resourceIdentifier}");
    }

    protected static function getNavigationIdentifier(): string
    {
        // Convert class name to kebab-case navigation identifier
        $className = class_basename(static::class);
        // Remove "Resource" suffix and convert to kebab-case
        $identifier = str_replace('Resource', '', $className);
        $identifier = preg_replace('/(?<!^)[A-Z]/', '-$0', $identifier);
        return strtolower($identifier);
    }
}
