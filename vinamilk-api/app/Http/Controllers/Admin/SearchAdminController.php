<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TrendingSearch;
use Illuminate\Http\Request;

class SearchAdminController extends Controller
{
    /**
     * Get all trending searches.
     */
    public function getTrending(Request $request)
    {
        return response()->json(TrendingSearch::orderBy('sort_order')->get());
    }

    /**
     * Update trending searches (Bulk save).
     */
    public function updateTrending(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.keyword' => 'required|string',
            'items.*.sort_order' => 'required|integer',
            'items.*.is_active' => 'required|boolean',
        ]);

        // Simple approach: Clear and re-insert or sync by ID
        foreach ($request->items as $item) {
            TrendingSearch::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'tenant_id' => 1, // Default for now
                    'keyword' => $item['keyword'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => $item['is_active'],
                ]
            );
        }

        return response()->json(['message' => 'Cập nhật danh sách xu hướng thành công']);
    }

    /**
     * Delete a trending search.
     */
    public function deleteTrending($id)
    {
        TrendingSearch::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa từ khóa xu hướng']);
    }

    /**
     * Get products for selection in search recommendations.
     */
    public function getFeaturedProducts()
    {
        return response()->json(
            Product::where('is_search_featured', true)->get(['id', 'name', 'slug', 'is_search_featured'])
        );
    }

    /**
     * Toggle search featured status for a product.
     */
    public function toggleFeaturedProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_search_featured' => !$product->is_search_featured]);
        
        return response()->json([
            'message' => $product->is_search_featured ? 'Đã thêm vào "Dành cho bạn"' : 'Đã xóa khỏi "Dành cho bạn"',
            'is_search_featured' => $product->is_search_featured
        ]);
    }
}
