<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MarketingEngineController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminExportController;
use App\Http\Middleware\AdminAuthMiddleware;
use App\Http\Middleware\ApiRateLimitMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes ---
Route::prefix('v1')->group(function () {
    // Tenant info
    Route::get('/tenant', function () {
        return response()->json(['tenant' => auth()->user()?->tenant ?? 'Default']);
    });

    // Catalog & Discovery
    Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
    Route::get('/menus', function () {
        $menus = \App\Models\MegaMenu::with([
            'featuredProduct' => function ($query) {
                $query->with(['volumeMedia', 'homeFeaturedVolume', 'cardTag']);
            }
        ])->where('is_active', true)->orderBy('sort_order')->get();

        // Compute main_image giống logic trong ProductResource
        return $menus->map(function ($menu) {
            $data = $menu->toArray();
            if ($menu->featuredProduct) {
                $product = $menu->featuredProduct;
                $mainImage = null;
                if ($product->homeFeaturedVolume && $product->homeFeaturedVolume->main_image) {
                    $mainImage = asset('storage/' . $product->homeFeaturedVolume->main_image);
                } else {
                    $firstVm = $product->volumeMedia->first();
                    if ($firstVm && $firstVm->main_image) {
                        $mainImage = asset('storage/' . $firstVm->main_image);
                    }
                }
                $data['featured_product']['computed_main_image'] = $mainImage;
            }
            return $data;
        });
    });
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::get('/catalog/filters', [CatalogController::class, 'filters']);
    Route::get('/collections/{slug}', [CatalogController::class, 'categoryProducts']);
    Route::get('/products/{slug}', [CatalogController::class, 'product']);
    Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'index']);
    Route::get('/search/suggestions', [\App\Http\Controllers\Api\SearchController::class, 'suggestions']);

    // Content
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogController::class, 'show']);
    Route::get('/banners', function () {
        return \App\Models\Banner::where('is_active', true)->orderBy('sort_order')->get();
    });
    Route::get('/support-pages', [\App\Http\Controllers\Api\V1\SupportPageController::class, 'index']);
    Route::get('/support-pages/{slug}', [\App\Http\Controllers\Api\V1\SupportPageController::class, 'show']);

    // Auth (Guest/User)
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
    Route::get('/promotions', [\App\Http\Controllers\Api\PromotionController::class, 'index']);
    Route::get('/promotions-page-banners', [\App\Http\Controllers\Api\PromotionController::class, 'promotionsPageBanners']);
    Route::get('/care', [\App\Http\Controllers\Api\CareController::class, 'page']);
    Route::get('/care/products', [\App\Http\Controllers\Api\CareController::class, 'products']);
    Route::get('/care/greeting-cards', [\App\Http\Controllers\Api\CareController::class, 'greetingCards']);
    Route::post('/care/calculate', [\App\Http\Controllers\Api\CareController::class, 'calculate']);
    Route::get('/stores', [\App\Http\Controllers\Api\StoreController::class, 'index']);
    Route::get('/shipping-methods', [\App\Http\Controllers\Api\ShippingMethodController::class, 'index']);
    Route::post('/shipping/calculate-fee', [\App\Http\Controllers\Api\ShippingMethodController::class, 'calculateFee']);
    Route::post('/chat', [\App\Http\Controllers\Api\V1\ChatController::class, 'sendMessage']);

    // Marketing Engine — Cart Evaluation
    Route::post('/cart/evaluate', [MarketingEngineController::class, 'evaluate']);

    // Vouchers — public: validate code (guest OK)
    Route::post('/vouchers/validate-code', [VoucherController::class, 'validateCode']);
    // Orders — payment status sync callback
    Route::post('/orders/{orderNumber}/payment-success', [\App\Http\Controllers\Api\OrderController::class, 'paymentSuccess']);
});

// --- Protected Routes ---
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [UserController::class, 'logout']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'changePassword']);

    // Address
    Route::apiResource('user/addresses', \App\Http\Controllers\Api\AddressController::class)->except(['show']);

    // Wishlist
    Route::get('/wishlist', [UserController::class, 'wishlist']);
    Route::post('/wishlist/add', [UserController::class, 'addToWishlist']);

    // Sales
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/checkout', [OrderController::class, 'checkout']);
    Route::post('/care/checkout', [\App\Http\Controllers\Api\CareController::class, 'checkout']);
    Route::get('/orders/{number}', [OrderController::class, 'show']);

    // Loyalty / Membership
    Route::get('/loyalty', function (Request $request) {
        return [
            'points' => $request->user()->reward_points,
            'tier' => $request->user()->reward_points > 1000 ? 'Gold' : 'Silver',
        ];
    });

    // Search Administration
    Route::prefix('admin/search')->group(function () {
        Route::get('/trending', [\App\Http\Controllers\Admin\SearchAdminController::class, 'getTrending']);
        Route::post('/trending', [\App\Http\Controllers\Admin\SearchAdminController::class, 'updateTrending']);
        Route::delete('/trending/{id}', [\App\Http\Controllers\Admin\SearchAdminController::class, 'deleteTrending']);
        Route::get('/featured', [\App\Http\Controllers\Admin\SearchAdminController::class, 'getFeaturedProducts']);
        Route::post('/featured/{id}/toggle', [\App\Http\Controllers\Admin\SearchAdminController::class, 'toggleFeaturedProduct']);
    });

    // Vouchers — protected: list + apply (requires login)
    Route::get('/vouchers', [VoucherController::class, 'index']);
    Route::post('/vouchers/apply', [VoucherController::class, 'apply']);

    // Vinamilk Rewards
    Route::get('/rewards', [\App\Http\Controllers\Api\V1\RewardController::class, 'index']);
    Route::get('/rewards/history', [\App\Http\Controllers\Api\V1\RewardController::class, 'history']);
    Route::get('/rewards/my-rewards', [\App\Http\Controllers\Api\V1\RewardController::class, 'myRewards']);
    Route::post('/rewards/{id}/redeem', [\App\Http\Controllers\Api\V1\RewardController::class, 'redeem']);


    // Support Pages Admin
    Route::apiResource('admin/support-pages', \App\Http\Controllers\Api\V1\SupportPageController::class);

    // API Documentation (Admin only)
    Route::get('/admin/api-docs', [\App\Http\Controllers\Admin\ApiDocumentationController::class, 'index']);

    // Promotion Campaigns Admin
    Route::prefix('admin/promotion-campaigns')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PromotionCampaignController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\PromotionCampaignController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\PromotionCampaignController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\PromotionCampaignController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\PromotionCampaignController::class, 'destroy']);
        Route::post('/{id}/activate', [\App\Http\Controllers\Admin\PromotionCampaignController::class, 'activate']);
        Route::post('/{id}/deactivate', [\App\Http\Controllers\Admin\PromotionCampaignController::class, 'deactivate']);
    });

    // Admin Dashboard API
    Route::prefix('admin')
        ->middleware(['admin.auth', 'api.rate_limit:60,1'])
        ->group(function () {
            // Dashboard
            Route::get('/dashboard/stats', [AdminController::class, 'getStats']);
            Route::get('/dashboard/sales-chart', [AdminController::class, 'getSalesChart']);
            Route::get('/dashboard/top-products', [AdminController::class, 'getTopProducts']);
            Route::get('/dashboard/top-customers', [AdminController::class, 'getTopCustomers']);
            Route::get('/dashboard/order-status-distribution', [AdminController::class, 'getOrderStatusDistribution']);
            Route::get('/dashboard/customer-acquisition', [AdminController::class, 'getCustomerAcquisition']);
            Route::get('/dashboard/revenue-forecast', [AdminController::class, 'getRevenueForecast']);

            // Monitoring
            Route::get('/monitoring/server-health', [AdminController::class, 'getServerHealth']);
            Route::get('/monitoring/api-metrics', [AdminController::class, 'getApiMetrics']);

            // Export
            Route::get('/export/orders', [AdminExportController::class, 'exportOrders']);
            Route::get('/export/orders-pdf', [AdminExportController::class, 'exportOrdersPdf']);
            Route::get('/export/products', [AdminExportController::class, 'exportProducts']);
            Route::get('/export/users', [AdminExportController::class, 'exportUsers']);
        });
});


