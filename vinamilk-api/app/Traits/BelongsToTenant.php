<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::creating(function (Model $model) {
            if (! $model->tenant_id) {
                $model->tenant_id = \App\Services\TenantService::getTenantId() 
                    ?? \App\Services\TenantService::DEFAULT_TENANT_ID;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (\App\Services\TenantService::isScopingEnabled()) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', \App\Services\TenantService::getTenantId());
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
