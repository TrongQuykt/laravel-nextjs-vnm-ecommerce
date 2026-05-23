<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'variants', 'variants.flavor', 'variants.volume', 'variants.packagingType', 'volumeMedia', 'brand', 'productLine', 'sugarLevel'])
            ->where('status', 'published')
            ->when($request->category, function ($query, $category) {
                return $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            })
            ->when($request->brand, function ($query, $brand) {
                return $query->where('brand', $brand);
            })
            ->paginate(12);

        return ProductResource::collection($products);
    }

    /**
     * Display the specified product.
     */
    public function show($slug)
    {
        $product = Product::with(['category', 'variants', 'variants.flavor', 'variants.volume', 'variants.packagingType', 'volumeMedia', 'brand', 'productLine', 'sugarLevel', 'nutritionalNeeds'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
