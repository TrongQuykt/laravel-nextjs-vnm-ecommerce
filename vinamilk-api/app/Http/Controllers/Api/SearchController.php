<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use App\Models\TrendingSearch;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Full search and results page.
     */
    public function index(Request $request)
    {
        $q = $request->get('q');
        
        $query = Product::with([
            'brand', 'category', 'productLine',
            'variants.flavor', 'variants.volume', 'variants.packagingType',
            'images', 'nutritionalNeeds', 'sugarLevel',
            'specialHighlights', 'certificates'
        ])
        ->where('status', 'published');

        if ($q) {
            $query->where(function ($query) use ($q) {
                // Primary: Name
                $query->where('name', 'LIKE', "%{$q}%")
                      // Related: Brand, Category, Product Line
                      ->orWhereHas('brand', fn($bq) => $bq->where('name', 'LIKE', "%{$q}%"))
                      ->orWhereHas('category', fn($cq) => $cq->where('name', 'LIKE', "%{$q}%"))
                      ->orWhereHas('productLine', fn($pq) => $pq->where('name', 'LIKE', "%{$q}%"))
                      // Keywords / Features
                      ->orWhere('features', 'LIKE', "%{$q}%");
            });
        }

        // Apply filters (Reused from CatalogController logic)
        $this->applyFilters($request, $query);

        // Sorting
        $sort = $request->get('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->whereHas('variants', function($q) {
                $q->orderBy('price', 'asc');
            });
        } elseif ($sort === 'price_desc') {
            $query->whereHas('variants', function($q) {
                $q->orderBy('price', 'desc');
            });
        } else {
            $query->latest();
        }

        $products = $query->paginate(24);

        return ProductResource::collection($products)->additional([
            'meta' => [
                'query' => $q
            ]
        ]);
    }

    /**
     * Sidebar suggestions: Trending, Top matches, etc.
     */
    public function suggestions(Request $request)
    {
        $q = $request->get('q', '');
        $cacheKey = 'search_suggestions_' . md5($q);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($q) {
            $trending = TrendingSearch::where('is_active', true)
                ->orderBy('sort_order')
                ->limit(10)
                ->get();

            $suggestedProducts = [];
            if ($q) {
                $query = Product::with(['brand', 'variants.flavor', 'variants.volume', 'variants.packagingType'])
                    ->where('status', 'published');

                // Tokenized search for better "Smart" results
                $tokens = explode(' ', $q);
                $query->where(function ($sub) use ($tokens) {
                    foreach ($tokens as $token) {
                        $sub->where('name', 'LIKE', "%{$token}%");
                    }
                });

                $suggestedProducts = $query->limit(5)->get();
            }

            // Add some random recommendations for the "Dành cho bạn" section
            $recommendations = Product::with([
                'brand', 'variants.volume', 'variants.packagingType',
                'specialHighlights', 'certificates'
            ])
                ->where('status', 'published')
                ->where('is_search_featured', true)
                ->inRandomOrder()
                ->limit(4)
                ->get();

            // If no featured products, fallback to random ones
            if ($recommendations->isEmpty()) {
                $recommendations = Product::with([
                    'brand', 'variants.volume', 'variants.packagingType',
                    'specialHighlights', 'certificates'
                ])
                    ->where('status', 'published')
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
            }

            return response()->json([
                'trending' => $trending,
                'products' => ProductResource::collection($suggestedProducts),
                'recommendations' => ProductResource::collection($recommendations),
            ]);
        });
    }

    /**
     * Helper to apply filters to the query.
     */
    private function applyFilters(Request $request, $query)
    {
        if ($request->has('category')) {
            $categories = explode(',', $request->category);
            $query->whereHas('category', fn($q) => $q->whereIn('slug', $categories));
        }
        if ($request->has('product_line')) {
            $productLinesParam = explode(',', $request->product_line);
            $query->whereHas('productLine', fn($q) => $q->whereIn('slug', $productLinesParam));
        }
        if ($request->has('brand')) {
            $brands = explode(',', $request->brand);
            $query->whereHas('brand', fn($q) => $q->whereIn('slug', $brands));
        }
        if ($request->has('sugar')) {
            $sugars = explode(',', $request->sugar);
            $query->whereHas('sugarLevel', fn($q) => $q->whereIn('slug', $sugars));
        }
        if ($request->has('need')) {
            $needs = explode(',', $request->need);
            $query->whereHas('nutritionalNeeds', fn($q) => $q->whereIn('slug', $needs));
        }
        if ($request->has('flavor')) {
            $flavors = explode(',', $request->flavor);
            $query->whereHas('variants.flavor', fn($q) => $q->whereIn('slug', $flavors));
        }
        if ($request->has('volume')) {
            $volumes = explode(',', $request->volume);
            $query->whereHas('variants.volume', fn($q) => $q->whereIn('slug', $volumes));
        }
        if ($request->has('packaging')) {
            $packaging = explode(',', $request->packaging);
            $query->whereHas('variants.packagingType', fn($q) => $q->whereIn('slug', $packaging));
        }
    }
}
