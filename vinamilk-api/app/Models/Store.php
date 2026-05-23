<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'ward',
        'district',
        'province',
        'latitude',
        'longitude',
    ];

    protected static function booted()
    {
        static::saving(function ($store) {
            // Check if address fields changed or coordinates are missing
            if ($store->isDirty(['address', 'ward', 'district', 'province']) || (empty($store->latitude) || empty($store->longitude))) {
                
                $fullAddress = implode(', ', array_filter([
                    $store->name, // Thêm tên cửa hàng để Google Maps tìm POI chính xác hơn
                    $store->address,
                    $store->ward,
                    $store->district,
                    $store->province,
                    'Vietnam'
                ]));
                
                try {
                    $apiKey = env('SERPAPI_KEY');
                    $response = \Illuminate\Support\Facades\Http::get('https://serpapi.com/search.json', [
                        'engine' => 'google_maps',
                        'q' => $fullAddress,
                        'api_key' => $apiKey,
                        'type' => 'search',
                        'num' => 1
                    ]);
                    
                    $data = $response->json();
                    
                    // Lấy tọa độ từ kết quả tìm kiếm Google Maps
                    if (isset($data['place_results']['gps_coordinates'])) {
                        // Trường hợp tìm thấy chính xác 1 địa điểm
                        $store->latitude = $data['place_results']['gps_coordinates']['latitude'];
                        $store->longitude = $data['place_results']['gps_coordinates']['longitude'];
                    } elseif (isset($data['local_results'][0]['gps_coordinates'])) {
                        // Trường hợp trả về danh sách kết quả (lấy kết quả đầu tiên)
                        $store->latitude = $data['local_results'][0]['gps_coordinates']['latitude'];
                        $store->longitude = $data['local_results'][0]['gps_coordinates']['longitude'];
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("SerpApi Geocoding failed: " . $e->getMessage());
                }
            }
        });
    }
}
