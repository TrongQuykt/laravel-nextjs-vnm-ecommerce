<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SupportPage;

class SupportPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = SupportPage::where('is_active', true)
            ->orderBy('order')
            ->select('id', 'slug', 'title')
            ->get();

        return response()->json($pages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:support_pages',
            'title' => 'required|string',
            'content' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $page = SupportPage::create($validated);

        return response()->json($page, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        // Try to find by slug first (for public FE)
        $page = SupportPage::where('slug', $slug)->first();

        // If not found by slug, try by ID (for admin API)
        if (!$page && is_numeric($slug)) {
            $page = SupportPage::find($slug);
        }

        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        return response()->json($page);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $page = SupportPage::findOrFail($id);

        $validated = $request->validate([
            'slug' => 'string|unique:support_pages,slug,' . $id,
            'title' => 'string',
            'content' => 'nullable|string',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $page->update($validated);

        return response()->json($page);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $page = SupportPage::findOrFail($id);
        $page->delete();

        return response()->json(null, 204);
    }
}
