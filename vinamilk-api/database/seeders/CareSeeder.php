<?php

namespace Database\Seeders;

use App\Models\CareGreetingCard;
use Illuminate\Database\Seeder;

class CareSeeder extends Seeder
{
    public function run(): void
    {
        $cards = [
            [
                'title'   => 'Thiệp Vinamilk Care mẫu 01',
                'message' => "KHÔNG TĂNG ĐƯỜNG HUYẾT - TĂNG NHIỀU NIỀM VUI\nCon chúc ba mẹ luôn khỏe mạnh, vui vẻ mỗi ngày!",
                'sort_order' => 1,
            ],
            [
                'title'   => 'Thiệp Vinamilk Care mẫu 02',
                'message' => "ĐƯỜNG HUYẾT GIẢM - NIỀM VUI TĂNG\nĂn ngon, ngủ khoẻ — con luôn bên ba mẹ!",
                'sort_order' => 2,
            ],
            [
                'title'   => 'Thiệp Vinamilk Care mẫu 03',
                'message' => "ĂN ĐƯỢC - NGỦ KHỎE - SỐNG VUI MỖI NGÀY\nGửi gắm yêu thương từ con!",
                'sort_order' => 3,
            ],
            [
                'title'   => 'Thiệp Vinamilk Care mẫu 04',
                'message' => "BỮA NÀO CÙNG NGON GIẤC NÀO CÙNG TRÒN\nChúc ba mẹ luôn tràn đầy sức khỏe!",
                'sort_order' => 4,
            ],
        ];

        foreach ($cards as $card) {
            CareGreetingCard::firstOrCreate(['title' => $card['title']], array_merge($card, ['is_active' => true]));
        }
    }
}
