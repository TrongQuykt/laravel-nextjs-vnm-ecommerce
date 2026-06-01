<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionCampaignController extends Controller
{
    /**
     * GET /admin/promotion-campaigns
     * Liệt kê tất cả chiến dịch, kèm số lượng banners, terms, flash sale.
     */
    public function index()
    {
        $campaigns = PromotionCampaign::withCount(['banners', 'terms'])
            ->with(['flashSale:id,campaign_id,title,start_time,end_time', 'pageSetting:id,campaign_id,hero_title'])
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($c) => $this->formatCampaign($c));

        return response()->json(['data' => $campaigns]);
    }

    /**
     * POST /admin/promotion-campaigns
     * Tạo chiến dịch mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'is_active'   => 'boolean',
        ]);

        $campaign = PromotionCampaign::create($validated);

        return response()->json(['data' => $this->formatCampaign($campaign->load(['flashSale', 'pageSetting']))], 201);
    }

    /**
     * GET /admin/promotion-campaigns/{id}
     * Chi tiết chiến dịch đầy đủ.
     */
    public function show($id)
    {
        $campaign = PromotionCampaign::with([
            'pageSetting',
            'banners',
            'flashSale.products',
            'terms',
        ])->findOrFail($id);

        return response()->json(['data' => $this->formatCampaign($campaign, true)]);
    }

    /**
     * PUT /admin/promotion-campaigns/{id}
     * Cập nhật thông tin chiến dịch.
     */
    public function update(Request $request, $id)
    {
        $campaign = PromotionCampaign::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'sometimes|date|after_or_equal:start_date',
            'is_active'   => 'boolean',
        ]);

        $campaign->update($validated);

        return response()->json(['data' => $this->formatCampaign($campaign->fresh())]);
    }

    /**
     * DELETE /admin/promotion-campaigns/{id}
     * Xóa chiến dịch (chỉ xóa campaign, các bản ghi con sẽ set campaign_id = null).
     */
    public function destroy($id)
    {
        $campaign = PromotionCampaign::findOrFail($id);
        $campaign->delete();

        return response()->json(['message' => 'Chiến dịch đã được xóa.']);
    }

    /**
     * POST /admin/promotion-campaigns/{id}/activate
     * Kích hoạt chiến dịch này, tắt tất cả chiến dịch khác.
     */
    public function activate($id)
    {
        DB::transaction(function () use ($id) {
            // Tắt tất cả chiến dịch khác
            PromotionCampaign::where('id', '!=', $id)->update(['is_active' => false]);

            // Bật chiến dịch này
            PromotionCampaign::where('id', $id)->update(['is_active' => true]);
        });

        $campaign = PromotionCampaign::findOrFail($id);
        return response()->json([
            'message' => "Chiến dịch \"{$campaign->name}\" đã được kích hoạt.",
            'data'    => $this->formatCampaign($campaign),
        ]);
    }

    /**
     * POST /admin/promotion-campaigns/{id}/deactivate
     * Tắt chiến dịch này.
     */
    public function deactivate($id)
    {
        $campaign = PromotionCampaign::findOrFail($id);
        $campaign->update(['is_active' => false]);

        return response()->json([
            'message' => "Chiến dịch \"{$campaign->name}\" đã bị tắt.",
            'data'    => $this->formatCampaign($campaign),
        ]);
    }

    /**
     * Format campaign data for API response.
     */
    private function formatCampaign(PromotionCampaign $campaign, bool $detailed = false): array
    {
        $base = [
            'id'          => $campaign->id,
            'name'        => $campaign->name,
            'description' => $campaign->description,
            'start_date'  => $campaign->start_date?->toDateString(),
            'end_date'    => $campaign->end_date?->toDateString(),
            'is_active'   => $campaign->is_active,
            'is_running'  => $campaign->isRunning(),
            'created_at'  => $campaign->created_at,
            'updated_at'  => $campaign->updated_at,
        ];

        if ($detailed) {
            $base['page_setting'] = $campaign->pageSetting;
            $base['banners']      = $campaign->banners;
            $base['flash_sale']   = $campaign->flashSale;
            $base['terms']        = $campaign->terms;
        } else {
            $base['page_setting_title'] = $campaign->pageSetting?->hero_title;
            $base['flash_sale_title']   = $campaign->flashSale?->title;
            $base['banners_count']      = $campaign->banners_count ?? $campaign->banners?->count() ?? 0;
            $base['terms_count']        = $campaign->terms_count  ?? $campaign->terms?->count()  ?? 0;
        }

        return $base;
    }
}
