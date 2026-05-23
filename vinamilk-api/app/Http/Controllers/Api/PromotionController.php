<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotionBanner;
use App\Models\PromotionCampaign;
use App\Models\PromotionFlashSale;
use App\Models\PromotionTerm;
use App\Models\PromotionPageSetting;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\Api\ProductResource;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            $productRelations = [
                'brand', 'category', 'productLine', 'sugarLevel',
                'variants.flavor', 'variants.volume', 'variants.packagingType',
                'homeFeaturedVolume', 'volumeMedia', 'specialHighlights', 'cardTag'
            ];

            // ─── Ưu tiên: Tìm chiến dịch đang active & trong thời gian chạy ───
            $activeCampaign = PromotionCampaign::where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->with(['pageSetting', 'banners', 'flashSale', 'terms'])
                ->first();

            if ($activeCampaign) {
                // Dữ liệu từ chiến dịch đang chạy
                $settings  = $activeCampaign->pageSetting;
                $terms     = $activeCampaign->terms;
                $flashSale = $activeCampaign->flashSale;

                // Ghi đè thời gian của flash sale bằng thời gian của chiến dịch
                if ($flashSale) {
                    $flashSale->start_time = \Carbon\Carbon::parse($activeCampaign->start_date)->startOfDay();
                    $flashSale->end_time = \Carbon\Carbon::parse($activeCampaign->end_date)->endOfDay();
                }

                // Chuẩn hóa ảnh cho banners trong campaign
                $banners = $activeCampaign->banners->map(function ($banner) {
                    $banner->image_path = $banner->image_path
                        ? asset('storage/' . $banner->image_path)
                        : null;
                    return $banner;
                });
            } else {
                // ─── Fallback: Lấy từng bảng riêng lẻ (hành vi cũ) ───
                $flashSale = PromotionFlashSale::where('is_active', true)
                    ->where('end_time', '>', now())
                    ->orderBy('end_time', 'asc')
                    ->first();

                $terms    = PromotionTerm::whereNull('campaign_id')->orderBy('sort_order')->get();
                $banners  = PromotionBanner::where('is_active', true)
                    ->whereNull('campaign_id')
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($banner) {
                        $banner->image_path = $banner->image_path
                            ? asset('storage/' . $banner->image_path)
                            : null;
                        return $banner;
                    });
                $settings = PromotionPageSetting::whereNull('campaign_id')->first();
            }

            // ─── Sản phẩm Flash Sale ───
            $flashSaleProducts = collect();
            if ($flashSale) {
                $flashSaleProducts = $flashSale->products()->with($productRelations)->get();
            }

            // Fallback sản phẩm: lấy sản phẩm có discount cao nhất
            if ($flashSaleProducts->isEmpty()) {
                $flashSaleProducts = Product::with($productRelations)
                    ->where('status', 'published')
                    ->whereHas('variants', fn($q) => $q->where('discount_percentage', '>', 0))
                    ->orderBy('updated_at', 'desc')
                    ->limit(12)
                    ->get();
            }

            // ─── Modal products ───
            $modalProducts = Product::with($productRelations)
                ->where('status', 'published')
                ->orderBy('updated_at', 'desc')
                ->limit(12)
                ->get();

            if ($settings && $settings->hero_image_path) {
                $settings->hero_image_path = asset('storage/' . $settings->hero_image_path);
            }

            return response()->json([
                'campaign'            => $activeCampaign ? [
                    'id'         => $activeCampaign->id,
                    'name'       => $activeCampaign->name,
                    'start_date' => $activeCampaign->start_date?->toDateString(),
                    'end_date'   => $activeCampaign->end_date?->toDateString(),
                ] : null,
                'settings'            => $settings,
                'banners'             => $banners,
                'flash_sale'          => $flashSale,
                'flash_sale_products' => ProductResource::collection($flashSaleProducts),
                'modal_products'      => ProductResource::collection($modalProducts),
                'terms'               => $terms,
            ]);
        } catch (\Exception $e) {
            \Log::error("Promotion API Error: " . $e->getMessage());
            return response()->json([
                'error'   => 'Internal Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banners for the /promotions public page (is_shown_on_promotions_page = true)
     */
    public function promotionsPageBanners()
    {
        $banners = PromotionBanner::where('is_active', true)
            ->where('is_shown_on_promotions_page', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($banner) {
                $banner->image_path = $banner->image_path
                    ? asset('storage/' . $banner->image_path)
                    : null;
                if ($banner->modal_image_path) {
                    $banner->modal_image_path = asset('storage/' . $banner->modal_image_path);
                }
                return $banner;
            });

        return response()->json(['banners' => $banners]);
    }
}
