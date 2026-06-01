<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhnService
{
    public string $baseUrl;
    public string $token;
    public int $shopId;

    public function __construct()
    {
        $this->token = trim(env('GHN_TOKEN', '5249cf4e-ca52-11ee-a2c1-ca66c1c1c1f2'));
        $this->shopId = (int)env('GHN_SHOP_ID', 191024);
        $this->baseUrl = rtrim(env('GHN_URL', 'https://dev-online-gateway.ghn.vn/shiip/public-api'), '/');
    }

    /**
     * Map clean Vietnamese address strings to GHN Master Data IDs
     */
    public function getGhnLocationIds(?string $provinceName, ?string $districtName, ?string $wardName): array
    {
        $fallback = [
            'province_id' => 202, // HCMC
            'district_id' => 1442, // District 1
            'ward_code' => '20109' // Ward Ben Nghe
        ];

        if (!$provinceName || !$districtName || !$wardName) {
            return $fallback;
        }

        try {
            // 1. Get Province ID
            $provRes = Http::withHeaders(['Token' => $this->token])
                ->get("{$this->baseUrl}/master-data/province");
            
            $provinceId = null;
            if ($provRes->successful()) {
                $provinces = $provRes->json()['data'] ?? [];
                foreach ($provinces as $p) {
                    if (str_contains(mb_strtolower($p['ProvinceName']), mb_strtolower($provinceName)) ||
                        str_contains(mb_strtolower($provinceName), mb_strtolower($p['ProvinceName']))) {
                        $provinceId = $p['ProvinceID'];
                        break;
                    }
                }
            }

            if (!$provinceId) {
                return $fallback;
            }

            // 2. Get District ID
            $distRes = Http::withHeaders(['Token' => $this->token])
                ->post("{$this->baseUrl}/master-data/district", [
                    'province_id' => $provinceId
                ]);
            
            $districtId = null;
            if ($distRes->successful()) {
                $districts = $distRes->json()['data'] ?? [];
                foreach ($districts as $d) {
                    if (str_contains(mb_strtolower($d['DistrictName']), mb_strtolower($districtName)) ||
                        str_contains(mb_strtolower($districtName), mb_strtolower($d['DistrictName']))) {
                        $districtId = $d['DistrictID'];
                        break;
                    }
                }
            }

            if (!$districtId) {
                return $fallback;
            }

            // 3. Get Ward Code
            $wardRes = Http::withHeaders(['Token' => $this->token])
                ->post("{$this->baseUrl}/master-data/ward", [
                    'district_id' => $districtId
                ]);
            
            $wardCode = null;
            if ($wardRes->successful()) {
                $wards = $wardRes->json()['data'] ?? [];
                foreach ($wards as $w) {
                    if (str_contains(mb_strtolower($w['WardName']), mb_strtolower($wardName)) ||
                        str_contains(mb_strtolower($wardName), mb_strtolower($w['WardName']))) {
                        $wardCode = $w['WardCode'];
                        break;
                    }
                }
            }

            if (!$wardCode) {
                return $fallback;
            }

            return [
                'province_id' => $provinceId,
                'district_id' => $districtId,
                'ward_code' => $wardCode
            ];

        } catch (\Exception $e) {
            Log::error("GHN Location Mapping Error: " . $e->getMessage());
            return $fallback;
        }
    }

    /**
     * Calculate delivery fee dynamically
     */
    public function calculateFee(?string $province, ?string $district, ?string $ward, int $weightGrams = 1000, int $serviceTypeId = 2): float
    {
        $locations = $this->getGhnLocationIds($province, $district, $ward);

        try {
            $response = Http::withHeaders([
                'Token' => $this->token,
                'ShopId' => $this->shopId,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/v2/shipping-order/fee", [
                'service_type_id' => $serviceTypeId,
                'insurance_value' => 200000,
                'coupon' => null,
                'from_district_id' => 1442, // Warehouse District 1
                'to_district_id' => $locations['district_id'],
                'to_ward_code' => $locations['ward_code'],
                'weight' => $weightGrams,
                'length' => 15,
                'width' => 15,
                'height' => 15
            ]);

            if ($response->successful()) {
                return (float)($response->json()['data']['total'] ?? 25000);
            }

            Log::error("GHN Calculate Fee API Error: " . $response->body());
            return $serviceTypeId === 1 ? 35000.00 : 25000.00; // Fallback standard fee
        } catch (\Exception $e) {
            Log::error("GHN Calculate Fee Exception: " . $e->getMessage());
            return $serviceTypeId === 1 ? 35000.00 : 25000.00;
        }
    }

    /**
     * Create delivery order inside GHN system
     */
    public function createShippingOrder($order): ?string
    {
        $addr = $order->shipping_address;
        if (!$addr) {
            return null;
        }

        $province = $addr['city'] ?? null;
        $district = $addr['district'] ?? null;
        $ward = $addr['ward'] ?? null;

        $locations = $this->getGhnLocationIds($province, $district, $ward);

        // Map items for GHN
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->product_name,
                'code' => $item->sku ?? 'GF-MILK',
                'quantity' => $item->quantity,
                'price' => (int)$item->price
            ];
        }

        $toName = ($addr['last_name'] ?? '') . ' ' . ($addr['first_name'] ?? '');
        if (trim($toName) === '') {
            $toName = $addr['name'] ?? 'Khách hàng';
        }

        $codAmount = $order->payment_method === 'cod' ? (int)$order->total_amount : 0;

        // Dynamically detect GHN Service Type by querying available services for the route
        $serviceTypeId = 2; // Default Standard
        try {
            $servRes = Http::withHeaders([
                'Token' => $this->token,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/v2/shipping-order/available-services", [
                'shop_id' => $this->shopId,
                'from_district' => 1442,
                'to_district' => $locations['district_id']
            ]);
            
            if ($servRes->successful()) {
                $services = $servRes->json()['data'] ?? [];
                $availTypeIds = [];
                foreach ($services as $srv) {
                    $availTypeIds[] = $srv['service_type_id'];
                }
                
                $wantsExpress = ($order->shipping_method_id == 2 || 
                    str_contains(mb_strtolower($order->shipping_method_name ?? ''), 'nhanh') || 
                    str_contains(mb_strtolower($order->shipping_method_name ?? ''), 'fast'));
                
                if ($wantsExpress && in_array(1, $availTypeIds)) {
                    $serviceTypeId = 1;
                } elseif (in_array(2, $availTypeIds)) {
                    $serviceTypeId = 2;
                } else {
                    $serviceTypeId = $availTypeIds[0] ?? 2;
                }
            }
        } catch (\Exception $e) {
            Log::error("GHN Available Services Exception: " . $e->getMessage());
        }

        try {
            $response = Http::withHeaders([
                'Token' => $this->token,
                'ShopId' => $this->shopId,
                'Content-Type' => 'application/json'
            ])->post("{$this->baseUrl}/v2/shipping-order/create", [
                'payment_type_id' => 1, // Shop/Seller pays shipping (highly recommended: Shop collects shipping fee from client, and pays GHN directly)
                'note' => $order->notes ?? 'Cho xem hàng, không thử',
                'required_note' => 'CHOXEMHANGKHONGTHU',
                'from_name' => 'Vinamilk Shop',
                'from_phone' => '0945449758',
                'from_address' => '10 Mai Chí Thọ',
                'from_ward_code' => '20109',
                'from_district_id' => 1442,
                'to_name' => $toName,
                'to_phone' => $addr['phone'] ?? '0945449758',
                'to_address' => $addr['detail'] ?? 'Địa chỉ giao hàng',
                'to_ward_code' => $locations['ward_code'],
                'to_district_id' => $locations['district_id'],
                'cod_amount' => $codAmount,
                'weight' => 1000,
                'length' => 20,
                'width' => 20,
                'height' => 15,
                'service_type_id' => $serviceTypeId,
                'items' => $items
            ]);

            if ($response->successful()) {
                $orderCode = $response->json()['data']['order_code'] ?? null;
                Log::info("GHN Shipping Order successfully created: {$orderCode}");
                return $orderCode;
            }

            Log::error("GHN Shipping Order Creation API Error: " . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error("GHN Shipping Order Creation Exception: " . $e->getMessage());
            return null;
        }
    }
}