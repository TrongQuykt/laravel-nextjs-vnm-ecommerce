<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Brand;
use App\Models\SugarLevel;
use App\Models\NutritionalNeed;
use App\Models\Flavor;
use App\Models\Volume;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class VinamilkCoreSeeder extends Seeder
{
    public function run(): void
    {
        // Sectors
        $sectors = ['Sữa tươi', 'Sữa chua', 'Sữa bột', 'Sữa đặc', 'Nước giải khát', 'Kem'];
        foreach ($sectors as $s) {
            Sector::firstOrCreate(['name' => $s, 'slug' => Str::slug($s)]);
        }

        // Brands
        $brands = ['Vinamilk', 'Green Farm', 'Probi', 'Susuru', 'Dielac', 'Ông Thọ'];
        foreach ($brands as $b) {
            Brand::firstOrCreate(['name' => $b, 'slug' => Str::slug($b)]);
        }

        // Sugar Levels
        $sugars = ['Không đường', 'Ít đường', 'Có đường', 'Tách béo không đường'];
        foreach ($sugars as $s) {
            SugarLevel::firstOrCreate(['name' => $s, 'slug' => Str::slug($s)]);
        }

        // Nutritional Needs
        $needs = ['Phát triển trí não', 'Tăng chiều cao', 'Tăng sức đề kháng', 'Tốt cho tiêu hóa', 'Xương chắc khỏe'];
        foreach ($needs as $n) {
            NutritionalNeed::firstOrCreate(['name' => $n, 'slug' => Str::slug($n)]);
        }

        // Flavors
        $flavors = ['Nguyên bản', 'Sô-cô-la', 'Dâu', 'Matcha', 'Yến mạch', 'Tổ yến'];
        foreach ($flavors as $f) {
            Flavor::firstOrCreate(['name' => $f, 'slug' => Str::slug($f)]);
        }

        // Volumes
        $volumes = ['110ml', '180ml', '1L', '400g', '900g', '110g'];
        foreach ($volumes as $v) {
            Volume::firstOrCreate(['name' => $v, 'slug' => Str::slug($v)]);
        }

        // Create an example Product
        $p = Product::create([
            'tenant_id' => 1,
            'name' => 'Sữa tươi tiệt trùng Green Farm Matcha',
            'slug' => Str::slug('Sữa tươi tiệt trùng Green Farm Matcha'),
            'sector_id' => Sector::where('name', 'Sữa tươi')->first()->id,
            'brand_id' => Brand::where('name', 'Green Farm')->first()->id,
            'sugar_level_id' => SugarLevel::where('name', 'Ít đường')->first()->id,
            'status' => 'published',
            'short_description' => 'Sữa tươi hút chân không công nghệ mới, vị matcha thơm mát.',
            'description' => '<p>Sữa tươi Vinamilk Green Farm với quy trình chăn nuôi thân thiện với môi trường...</p>',
            'ingredients' => 'Sữa tươi (95%), đường tinh luyện, bột matcha tự nhiên...',
            'nutrition_facts' => [
                ['key' => 'Năng lượng', 'value' => '75', 'unit' => 'kcal'],
                ['key' => 'Chất đạm', 'value' => '2.9', 'unit' => 'g'],
                ['key' => 'Chất béo', 'value' => '3.2', 'unit' => 'g'],
                ['key' => 'Canxi', 'value' => '110', 'unit' => 'mg'],
            ]
        ]);

        $p->nutritionalNeeds()->attach(
            NutritionalNeed::whereIn('name', ['Tăng sức đề kháng', 'Xương chắc khỏe'])->pluck('id')
        );

        // Variants
        ProductVariant::create([
            'product_id' => $p->id,
            'flavor_id' => Flavor::where('name', 'Matcha')->first()->id,
            'volume_id' => Volume::where('name', '180ml')->first()->id,
            'sku' => 'GF-MATCHA-180',
            'price' => 32000,
            'stock_quantity' => 100,
            'is_active' => true,
        ]);
    }
}
