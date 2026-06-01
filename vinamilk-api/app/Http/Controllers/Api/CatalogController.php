<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\NutritionalNeed;
use App\Models\Product;
use App\Models\ProductLine;
use App\Models\SugarLevel;
use App\Models\Flavor;
use App\Models\Volume;
use App\Models\PackagingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    protected $allRelations = [
        'brand', 'category', 'productLine', 'sugarLevel',
        'variants.flavor', 'variants.volume', 'variants.packagingType',
        'volumeMedia', 'specialHighlights', 'certificates', 'cardTag',
        'nutritionalNeeds', 'homeFeaturedVolume'
    ];

    public function index()
    {
        $categories = Category::with('productLines')->get();
        return response()->json([
            'categories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'product_lines' => $cat->productLines->map(fn ($pl) => [
                    'id' => $pl->id,
                    'name' => $pl->name,
                    'slug' => $pl->slug,
                ]),
            ]),
        ]);
    }

    public function categoryProducts(Request $request, $slug)
    {
        $category = null;
        if ($slug !== 'all-products' && $slug !== 'best-selling' && $slug !== 'flash-sales' && $slug !== 'promotions') {
            $category = Category::where('slug', $slug)->first();
        }

        $query = Product::with($this->allRelations)->where('status', 'published');

        if ($slug === 'flash-sales') {
            $activeCampaign = \App\Models\PromotionCampaign::where('is_active', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->with('flashSale')
                ->first();

            if ($activeCampaign && $activeCampaign->flashSale) {
                $flashSale = $activeCampaign->flashSale;
            } else {
                $flashSale = \App\Models\PromotionFlashSale::where('is_active', true)
                    ->where('end_time', '>', now())
                    ->orderBy('end_time', 'asc')
                    ->first();
            }
                
            if ($flashSale) {
                $productIds = \DB::table('promotion_flash_sale_products')
                    ->where('promotion_flash_sale_id', $flashSale->id)
                    ->pluck('product_id');
                
                if ($productIds->isNotEmpty()) {
                    $query->whereIn('products.id', $productIds);
                } else {
                    $query->whereHas('variants', fn($q) => $q->where('discount_percentage', '>', 0));
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($slug === 'promotions') {
            // Sản phẩm có giảm giá, sắp xếp theo % giảm cao nhất
            $query->whereHas('variants', fn($q) => $q->where('discount_percentage', '>', 0));
            $query->orderByDesc(
                \App\Models\ProductVariant::selectRaw('MAX(discount_percentage)')
                    ->whereColumn('product_id', 'products.id')
                    ->where('discount_percentage', '>', 0)
            );
        } elseif ($slug === 'best-selling') {
            $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('order_items')
                  ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                  ->whereColumn('product_variants.product_id', 'products.id')
                  ->where('order_items.quantity', '>', 0);
            });

            $query->orderByDesc(
                \App\Models\OrderItem::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                    ->whereColumn('product_variants.product_id', 'products.id')
            );
        } elseif ($category) {
            $query->where('category_id', $category->id);
        }

        // --- Apply Filters (Synced with Frontend Params) ---

        // 1. Filter by Categories
        if ($request->has('category')) {
            $catSlugs = explode(',', $request->category);
            $query->whereHas('category', fn($q) => $q->whereIn('slug', $catSlugs));
        }

        // 2. Filter by Brands
        if ($request->has('brand')) {
            $brandSlugs = explode(',', $request->brand);
            $query->whereHas('brand', fn($q) => $q->whereIn('slug', $brandSlugs));
        }

        // 3. Filter by Flavors
        if ($request->has('flavor')) {
            $flavorSlugs = explode(',', $request->flavor);
            $query->whereHas('variants.flavor', fn($q) => $q->whereIn('slug', $flavorSlugs));
        }

        // 4. Filter by Volumes
        if ($request->has('volume')) {
            $volumeSlugs = explode(',', $request->volume);
            $query->whereHas('variants.volume', fn($q) => $q->whereIn('slug', $volumeSlugs));
        }

        // 5. Filter by Sugar Levels (Frontend uses 'sugar')
        if ($request->has('sugar')) {
            $sugarSlugs = explode(',', $request->sugar);
            $query->whereHas('sugarLevel', fn($q) => $q->whereIn('slug', $sugarSlugs));
        }

        // 6. Filter by Nutritional Needs (Frontend uses 'need')
        if ($request->has('need')) {
            $needSlugs = explode(',', $request->need);
            $query->whereHas('nutritionalNeeds', fn($q) => $q->whereIn('slug', $needSlugs));
        }

        // 7. Filter by Product Line
        if ($request->has('product_line')) {
            $plSlugs = explode(',', $request->product_line);
            $query->whereHas('productLine', fn($q) => $q->whereIn('slug', $plSlugs));
        }

        if ($slug !== 'best-selling' && $slug !== 'promotions') {
            $query->latest();
        }

        $products = $query->paginate(24);

        // Get filter data for the sidebar
        $publishedIds = Product::where('status', 'published')->pluck('id');
        $productLineCounts = Product::where('status', 'published')
            ->select('product_line_id', DB::raw('count(*) as total'))
            ->groupBy('product_line_id')
            ->pluck('total', 'product_line_id');

        $response = ProductResource::collection($products);
        $response->additional([
            'category' => $category,
            'product_lines' => ProductLine::all()->map(fn($pl) => [
                'id' => $pl->id, 'name' => $pl->name, 'slug' => $pl->slug,
                'count' => $productLineCounts[$pl->id] ?? 0
            ]),
        ]);

        return $response;
    }

    public function filters(Request $request)
    {
        // Get search query if present
        $searchQuery = $request->get('q', '');
        
        // Base query for published products
        $productQuery = Product::where('status', 'published');
        
        // Apply search filter if query exists
        if ($searchQuery) {
            $productQuery->where('name', 'like', "%{$searchQuery}%");
        }
        
        $publishedIds = $productQuery->pluck('id');
        
        $catCounts = $productQuery
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $brandCounts = $productQuery
            ->select('brand_id', DB::raw('count(*) as total'))
            ->groupBy('brand_id')
            ->pluck('total', 'brand_id');

        $productLineCounts = $productQuery
            ->select('product_line_id', DB::raw('count(*) as total'))
            ->groupBy('product_line_id')
            ->pluck('total', 'product_line_id');

        $sugarCounts = $productQuery
            ->select('sugar_level_id', DB::raw('count(*) as total'))
            ->groupBy('sugar_level_id')
            ->pluck('total', 'sugar_level_id');

        return response()->json([
            'categories' => Category::whereNull('parent_id')->get()->map(fn($c) => [
                'id' => $c->id, 'name' => $c->name, 'slug' => $c->slug,
                'count' => $catCounts[$c->id] ?? 0
            ]),
            'brands' => Brand::all()->map(fn($b) => [
                'id' => $b->id, 'name' => $b->name, 'slug' => $b->slug,
                'count' => $brandCounts[$b->id] ?? 0
            ]),
            'product_lines' => ProductLine::all()->map(fn($pl) => [
                'id' => $pl->id, 'name' => $pl->name, 'slug' => $pl->slug,
                'count' => $productLineCounts[$pl->id] ?? 0
            ]),
            'sugar_levels' => SugarLevel::all()->map(fn($s) => [
                'id' => $s->id, 'name' => $s->name, 'slug' => $s->slug,
                'count' => $sugarCounts[$s->id] ?? 0
            ]),
            'flavors' => Flavor::all()->map(fn($f) => [
                'id' => $f->id, 'name' => $f->name, 'slug' => $f->slug,
                'count' => DB::table('product_variants')->whereIn('product_id', $publishedIds)->where('flavor_id', $f->id)->count()
            ]),
            'volumes' => Volume::all()->map(fn($v) => [
                'id' => $v->id, 'name' => $v->name, 'slug' => $v->slug,
                'count' => DB::table('product_variants')->whereIn('product_id', $publishedIds)->where('volume_id', $v->id)->count()
            ]),
            'packaging_types' => PackagingType::all()->map(fn($p) => [
                'id' => $p->id, 'name' => $p->name, 'slug' => $p->slug,
                'count' => DB::table('product_variants')->whereIn('product_id', $publishedIds)->where('packaging_type_id', $p->id)->count()
            ]),
            'nutritional_needs' => NutritionalNeed::all()->map(fn($n) => [
                'id' => $n->id, 'name' => $n->name, 'slug' => $n->slug,
                'count' => DB::table('product_nutritional_need')->whereIn('product_id', $publishedIds)->where('nutritional_need_id', $n->id)->count()
            ]),
        ]);
    }

    public function product($slug)
    {
        $product = Product::with($this->allRelations)
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
