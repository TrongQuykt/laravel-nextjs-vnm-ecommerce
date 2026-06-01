<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupportPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Chính sách bảo vệ thông tin khách hàng',
                'content' => '<h3>Chính sách bảo vệ thông tin khách hàng</h3><p>Nội dung chi tiết về bảo vệ thông tin cá nhân...</p>',
                'order' => 1,
            ],
            [
                'slug' => 'terms-of-use',
                'title' => 'Chính sách sử dụng',
                'content' => '<h3>Chính sách sử dụng</h3><p>Điều khoản và điều kiện sử dụng dịch vụ...</p>',
                'order' => 2,
            ],
            [
                'slug' => 'dispute-resolution',
                'title' => 'Chính sách giải quyết khiếu nại tại Website',
                'content' => '<h3>Quy trình giải quyết khiếu nại</h3><p>Các bước xử lý khiếu nại từ khách hàng...</p>',
                'order' => 3,
            ],
            [
                'slug' => 'shop-at-vinamilk',
                'title' => 'Mua hàng tại Vinamilk',
                'content' => '<h3>Hướng dẫn mua hàng</h3><p>Cách thức đặt hàng trực tuyến...</p>',
                'order' => 4,
            ],
            [
                'slug' => 'payment-policy',
                'title' => 'Chính sách giá và phương thức thanh toán',
                'content' => '<h3>Giá cả và Thanh toán</h3><p>Thông tin về giá bán và các cổng thanh toán hỗ trợ...</p>',
                'order' => 5,
            ],
            [
                'slug' => 'vinamilk-rewards',
                'title' => 'Vinamilk Rewards',
                'content' => '<h3>Chương trình Vinamilk Rewards</h3><p>Quyền lợi thành viên và cách tích điểm...</p>',
                'order' => 6,
            ],
            [
                'slug' => 'qr-code-loyalty',
                'title' => 'Thể lệ chương trình Quét Mã Tích Điểm',
                'content' => '<h3>Quét Mã Tích Điểm</h3><p>Hướng dẫn quét mã trên bao bì sản phẩm...</p>',
                'order' => 7,
            ],
            [
                'slug' => 'return-refund-store',
                'title' => 'Chính sách Đổi trả và Hoàn tiền Cửa hàng Vinamilk',
                'content' => '<h3>Đổi trả tại cửa hàng</h3><p>Quy định đổi trả khi mua trực tiếp...</p>',
                'order' => 8,
            ],
            [
                'slug' => 'return-refund-online',
                'title' => 'Chính sách Đổi trả và Hoàn tiền đơn hàng trực tuyến',
                'content' => '<h3>Đổi trả trực tuyến</h3><p>Quy định đổi trả khi đặt hàng qua website...</p>',
                'order' => 9,
            ],
            [
                'slug' => 'loyalty-program-store',
                'title' => 'Chương Trình Khách Hàng Thân Thiết Tại Cửa Hàng Vinamilk',
                'content' => '<h3>Khách hàng thân thiết</h3><p>Ưu đãi dành cho khách hàng truyền thống...</p>',
                'order' => 10,
            ],
            [
                'slug' => 'shopee-mall',
                'title' => 'Mua Sắm Tại Shopee',
                'content' => '<h3>Gian hàng Shopee</h3><p>Liên kết tới Shopee Mall của Vinamilk...</p>',
                'order' => 11,
            ],
            [
                'slug' => 'affiliate-program',
                'title' => 'Giới Thiệu Khách Hàng Qua App Vinamilk (Affiliate)',
                'content' => '<h3>Chương trình Affiliate</h3><p>Cách kiếm thêm thu nhập từ việc giới thiệu...</p>',
                'order' => 12,
            ],
            [
                'slug' => 'complaint-procedure',
                'title' => 'Quy trình tiếp nhận, giải quyết phản ánh, yêu cầu, khiếu nại của người tiêu dùng',
                'content' => '<h3>Quy trình tiếp nhận</h3><p>Cam kết hỗ trợ người tiêu dùng...</p>',
                'order' => 13,
            ],
            [
                'slug' => 'influencer-collaboration',
                'title' => 'Danh sách người có ảnh hưởng hợp tác với Vinamilk',
                'content' => '<h3>Hợp tác Influencer</h3><p>Danh sách các KOLs/Influencers...</p>',
                'order' => 14,
            ],
        ];

        foreach ($pages as $page) {
            \App\Models\SupportPage::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
