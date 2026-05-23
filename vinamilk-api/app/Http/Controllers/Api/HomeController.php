<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Http\Resources\Api\ProductResource;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Lấy Banners
        $heroBanners = Banner::where('position', 'home_hero')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($b) => $this->formatBanner($b));

        $promoLeft = Banner::where('position', 'home_promo_left')
            ->where('is_active', true)
            ->first();

        $promoRight = Banner::where('position', 'home_promo_right')
            ->where('is_active', true)
            ->first();

        // 2. Lấy Chứng nhận
        $certificates = Certificate::where('is_home_featured', true)->get()->map(function($cert) {
            return [
                'id' => $cert->id,
                'name' => $cert->name,
                'icon' => asset('storage/' . $cert->icon),
            ];
        });

        // 3. Lấy Sản phẩm nổi bật (Sắp xếp theo ID tăng dần như yêu cầu)
        $featuredProducts = Product::with([
            'brand', 'category', 'productLine', 'sugarLevel',
            'variants.flavor', 'variants.volume', 'variants.packagingType',
            'homeFeaturedVolume', 'volumeMedia', 'specialHighlights', 'certificates', 'cardTag'
        ])
        ->where('status', 'published')
        ->whereNotNull('home_featured_volume_id')
        ->orderBy('id', 'asc') // Sắp xếp theo ID tăng dần
        ->get();

        return response()->json([
            'hero_banners' => $heroBanners,
            'promo_split_left' => $promoLeft ? $this->formatBanner($promoLeft) : null,
            'promo_split_right' => $promoRight ? $this->formatBanner($promoRight) : null,
            'featured_products' => ProductResource::collection($featuredProducts),
            'certificates' => $certificates,
        ]);
    }

    private function formatBanner($banner)
    {
        return [
            'id' => $banner->id,
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'show_text' => (bool)$banner->show_text,
            'image' => asset('storage/' . $banner->image),
            'link' => $banner->link,
            'box_text' => $banner->box_text,
            'box_subtitle' => $banner->box_subtitle,
            'product_slug' => $banner->product_id ? \App\Models\Product::find($banner->product_id)?->slug : null,
        ];
    }
}
