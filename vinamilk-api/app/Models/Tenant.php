<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ["name", "slug", "domain", "theme_config", "is_active"];
    protected $casts = ["theme_config" => "json", "is_active" => "boolean"];

    public function categories() { return $this->hasMany(Category::class); }
    public function products() { return $this->hasMany(Product::class); }
    public function users() { return $this->hasMany(User::class); }
    public function blogs() { return $this->hasMany(Blog::class); }
    public function banners() { return $this->hasMany(Banner::class); }
    public function orders() { return $this->hasMany(Order::class); }
    public function coupons() { return $this->hasMany(Coupon::class); }
    public function shippingMethods() { return $this->hasMany(ShippingMethod::class); }
}