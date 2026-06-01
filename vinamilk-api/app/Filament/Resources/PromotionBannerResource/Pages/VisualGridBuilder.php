<?php

namespace App\Filament\Resources\PromotionBannerResource\Pages;

use App\Filament\Resources\PromotionBannerResource;
use Filament\Resources\Pages\Page;
use App\Models\PromotionBanner;
use Filament\Notifications\Notification;

class VisualGridBuilder extends Page
{
    protected static string $resource = PromotionBannerResource::class;

    protected static string $view = 'filament.resources.promotion-banner-resource.pages.visual-grid-builder';

    protected static ?string $title = 'Thiết kế lưới Banner (Visual Builder)';

    public $banners;

    public function mount()
    {
        $this->loadBanners();
    }

    public function loadBanners()
    {
        // Chuyển sang mảng để dùng trong blade dễ dàng
        $this->banners = PromotionBanner::where('is_active', true)->orderBy('sort_order')->get()->toArray();
    }

    public function updateOrder($items)
    {
        // $items is array of IDs in new order
        foreach ($items as $index => $id) {
            PromotionBanner::where('id', $id)->update(['sort_order' => $index]);
        }
        $this->loadBanners();
        Notification::make()->title('Đã lưu vị trí mới')->success()->send();
    }

    public function updateSize($id, $colSpan, $rowSpan)
    {
        PromotionBanner::where('id', $id)->update([
            'col_span' => $colSpan,
            'row_span' => $rowSpan,
        ]);
        $this->loadBanners();
        Notification::make()->title('Đã thay đổi kích thước')->success()->send();
    }
}
