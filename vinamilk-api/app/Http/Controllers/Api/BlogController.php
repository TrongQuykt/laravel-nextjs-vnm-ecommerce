<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        // Trả về danh sách danh mục kèm theo tối đa 4 bài viết mới nhất mỗi mục
        $categories = BlogCategory::with(['posts' => function ($query) {
            $query->orderBy('published_at', 'desc')
                  ->limit(4);
        }])->orderBy('sort_order')->get();

        return response()->json($categories);
    }

    public function show($slug)
    {
        $post = BlogPost::with(['category', 'products.variants', 'suggestedPosts'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Ưu tiên lấy các bài viết được admin chọn đề xuất
        $relatedPosts = $post->suggestedPosts;

        // Nếu admin không chọn bài nào, lấy các bài cùng danh mục làm dự phòng
        if ($relatedPosts->isEmpty()) {
            $relatedPosts = BlogPost::where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->inRandomOrder()
                ->limit(4)
                ->get();
        }

        return response()->json([
            'post' => $post,
            'related_posts' => $relatedPosts
        ]);
    }
}
