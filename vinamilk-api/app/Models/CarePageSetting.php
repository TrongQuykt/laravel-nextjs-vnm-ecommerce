<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarePageSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'benefits'               => 'array',
        'premium_coming_soon'    => 'boolean',
    ];
}
