<?php

namespace App\Services;

class TenantService
{
    const DEFAULT_TENANT_ID = 1;

    protected static ?int $tenantId = null;

    public static function setTenantId(?int $id): void
    {
        static::$tenantId = $id;
    }

    public static function getTenantId(): ?int
    {
        return static::$tenantId;
    }

    public static function isScopingEnabled(): bool
    {
        return static::$tenantId !== null;
    }
}
