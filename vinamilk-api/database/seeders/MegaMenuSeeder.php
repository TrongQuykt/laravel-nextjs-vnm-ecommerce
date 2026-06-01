<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MegaMenu;
use App\Models\Product;

class MegaMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Ưu tiên sản phẩm có ảnh chính
        $featuredProduct = \App\Models\Product::where('status', 'published')
            ->whereNotNull('main_image')
            ->first()
            ?: \App\Models\Product::where('status', 'published')->first();

        MegaMenu::updateOrCreate(
            ['name' => 'Sản phẩm'],
            [
                'url' => '/collections/all-products',
                'featured_product_id' => $featuredProduct ? $featuredProduct->id : null,
                'is_active' => true,
                'sort_order' => 1,
                'columns' => [
                    [
                        'title' => 'NGÀNH HÀNG',
                        'links' => [
                            ['label' => 'Sữa Bột Trẻ Em', 'url' => '/collections/sua-bot', 'badge' => '55'],
                            ['label' => 'Bột Ăn Dặm', 'url' => '/collections/bot-an-dam', 'badge' => '20'],
                            ['label' => 'Sữa Bột Người Lớn', 'url' => '/collections/sua-bot-nguoi-lon', 'badge' => '16'],
                            ['label' => 'Sữa Tươi', 'url' => '/collections/sua-tuoi', 'badge' => '59'],
                            ['label' => 'Sữa Dinh Dưỡng', 'url' => '/collections/sua-dinh-duong', 'badge' => '23'],
                            ['label' => 'Sữa Thực Vật', 'url' => '/collections/sua-thuc-vat', 'badge' => '27'],
                            ['label' => 'Sữa Trái Cây', 'url' => '/collections/sua-trai-cay', 'badge' => '9'],
                        ]
                    ],
                    [
                        'title' => 'THƯƠNG HIỆU',
                        'links' => [
                            ['label' => '100%', 'url' => '/search?brand=100', 'badge' => '35'],
                            ['label' => 'Green Farm', 'url' => '/search?brand=green-farm', 'badge' => '25'],
                            ['label' => 'Probi', 'url' => '/search?brand=probi', 'badge' => '18'],
                            ['label' => 'Optimum', 'url' => '/search?brand=optimum', 'badge' => '21'],
                            ['label' => 'Dielac', 'url' => '/search?brand=dielac', 'badge' => '31'],
                            ['label' => 'Ông Thọ', 'url' => '/search?brand=ong-tho', 'badge' => '14'],
                        ]
                    ],
                    [
                        'title' => 'ĐỐI TƯỢNG ĐẶC BIỆT',
                        'links' => [
                            ['label' => 'Trẻ Suy Dinh Dưỡng', 'url' => '/search?need=suy-dinh-duong', 'badge' => '159'],
                            ['label' => 'Mẹ Mang Thai', 'url' => '/search?need=mang-thai', 'badge' => '190'],
                            ['label' => 'Người Bị Dị Ứng', 'url' => '/search?need=di-ung', 'badge' => '14'],
                            ['label' => 'Người Bệnh Tiểu Đường', 'url' => '/search?need=tieu-duong', 'badge' => '3'],
                        ]
                    ],
                    [
                        'title' => 'CÔNG THỨC CHUYÊN BIỆT',
                        'links' => [
                            ['label' => 'Hữu Cơ Châu Âu', 'url' => '/search?need=huu-co', 'badge' => '2'],
                            ['label' => 'Cao Đạm', 'url' => '/search?need=cao-dam', 'badge' => '1'],
                            ['label' => 'Cao Canxi', 'url' => '/search?need=cao-canxi', 'badge' => '3'],
                            ['label' => 'Ít Đường', 'url' => '/search?need=it-duong', 'badge' => '118'],
                            ['label' => 'Ít Béo', 'url' => '/search?need=it-beo', 'badge' => '1'],
                        ]
                    ]
                ],
                'bottom_links' => [
                    ['label' => 'BÁN CHẠY', 'url' => '', 'badge' => 'BEST', 'theme' => 'cyan'],
                    ['label' => 'ƯU ĐÃI', 'url' => '', 'badge' => 'PROMO', 'theme' => 'cyan'],
                    ['label' => 'VINAMILK CARE', 'url' => '', 'badge' => 'SUBS', 'theme' => 'cyan'],
                    ['label' => 'FLASH SALE', 'url' => '', 'badge' => 'HOT', 'theme' => 'pink'],
                ]
            ]
        );
    }
}
