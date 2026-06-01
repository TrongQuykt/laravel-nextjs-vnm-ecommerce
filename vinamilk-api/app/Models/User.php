<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Laravel\Sanctum\HasApiTokens;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, BelongsToTenant, HasApiTokens;

    protected $fillable = ["name", "email", "phone", "password", "tenant_id", "reward_points", "loyalty_points", "referral_code", "role", "is_active", "last_login_at", "avatar_url"];
    protected $hidden = ["password", "remember_token"];
    protected $casts = ["email_verified_at" => "datetime", "password" => "hashed"];

    public function orders() { return $this->hasMany(Order::class); }
    public function wishlists() { return $this->hasMany(Wishlist::class); }
    public function addresses() { return $this->hasMany(Address::class); }
    public function rewardRedemptions() { return $this->hasMany(RewardRedemption::class); }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Cho phép tất cả user truy cập tạm thời
    }
}