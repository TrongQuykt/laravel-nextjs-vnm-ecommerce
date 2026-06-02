<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\BlogPost;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\PromotionCampaign;
use App\Models\Reward;
use App\Models\Store;
use App\Models\ShippingMethod;
use App\Models\ChatSetting;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\StockAlert;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use App\Observers\CategoryObserver;
use App\Observers\BrandObserver;
use App\Observers\BlogPostObserver;
use App\Observers\BannerObserver;
use App\Observers\VoucherObserver;
use App\Observers\PromotionObserver;
use App\Observers\RewardObserver;
use App\Observers\StoreObserver;
use App\Observers\ShippingMethodObserver;
use App\Observers\ChatSettingObserver;
use App\Observers\ProductVariantObserver;
use App\Observers\StockMovementObserver;
use App\Observers\StockReservationObserver;
use App\Observers\StockAlertObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        User::observe(UserObserver::class);
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);
        BlogPost::observe(BlogPostObserver::class);
        Banner::observe(BannerObserver::class);
        Coupon::observe(VoucherObserver::class);
        PromotionCampaign::observe(PromotionObserver::class);
        Reward::observe(RewardObserver::class);
        Store::observe(StoreObserver::class);
        ShippingMethod::observe(ShippingMethodObserver::class);
        ChatSetting::observe(ChatSettingObserver::class);
        ProductVariant::observe(ProductVariantObserver::class);
        StockMovement::observe(StockMovementObserver::class);
        StockReservation::observe(StockReservationObserver::class);
        StockAlert::observe(StockAlertObserver::class);
    }
}
