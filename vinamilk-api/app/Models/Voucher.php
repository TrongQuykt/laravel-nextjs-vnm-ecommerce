<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'banner_image',
        'type', 'discount_value', 'max_discount_amount', 'min_order_amount',
        'applicable_product_ids', 'total_quantity', 'used_count',
        'is_active', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'applicable_product_ids' => 'array',
        'discount_value'         => 'float',
        'max_discount_amount'    => 'float',
        'min_order_amount'       => 'float',
        'total_quantity'         => 'integer',
        'used_count'             => 'integer',
        'is_active'              => 'boolean',
        'starts_at'              => 'datetime',
        'expires_at'             => 'datetime',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    /** Còn lượt dùng không */
    public function hasStock(): bool
    {
        if ($this->total_quantity === 0) return true;
        return $this->used_count < $this->total_quantity;
    }

    /** Còn trong thời hạn không */
    public function isValid(): bool
    {
        // Sử dụng now() đồng bộ với múi giờ của database (thường là UTC)
        $now = Carbon::now();
        
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->expires_at && $now->gt($this->expires_at)) return false;
        
        return $this->is_active;
    }

    /**
     * Kiểm tra điều kiện áp dụng voucher.
     * Returns ['eligible' => bool, 'reason' => string|null, 'discount' => float]
     */
    public function checkEligibility(float $cartTotal, array $cartItems, ?int $userId = null): array
    {
        \Log::info("Checking eligibility for Voucher: {$this->code}", [
            'input_cartTotal' => $cartTotal,
            'cartItems_count' => count($cartItems)
        ]);

        if (!$this->isValid()) {
            return ['eligible' => false, 'reason' => 'Voucher đã hết hạn hoặc chưa kích hoạt', 'discount' => 0];
        }

        if (!$this->hasStock()) {
            return ['eligible' => false, 'reason' => 'Voucher đã hết số lượng', 'discount' => 0];
        }

        if ($userId) {
            $alreadyUsed = VoucherUsage::where('voucher_id', $this->id)
                ->where('user_id', $userId)
                ->exists();
            if ($alreadyUsed) {
                return ['eligible' => false, 'reason' => 'Bạn đã sử dụng voucher này rồi', 'discount' => 0];
            }
        }

        // Đảm bảo cartTotal là số
        $totalVal = floatval($cartTotal);

        // Tổng tiền áp dụng (mặc định là toàn bộ giỏ hàng, nếu có list sản phẩm thì mới lọc)
        $applicableTotal = $totalVal;
        
        if (!empty($this->applicable_product_ids)) {
            $applicableTotal = collect($cartItems)
                ->filter(fn($i) => in_array($i['product_id'], $this->applicable_product_ids))
                ->sum(fn($i) => floatval($i['price'] ?? 0) * intval($i['quantity'] ?? 0));
                
            \Log::info("Filtered applicableTotal for {$this->code}", [
                'applicableTotal' => $applicableTotal
            ]);

            if ($applicableTotal == 0) {
                $productNames = Product::whereIn('id', $this->applicable_product_ids)->pluck('name')->implode(', ');
                return ['eligible' => false, 'reason' => "Voucher chỉ áp dụng cho: {$productNames}", 'discount' => 0];
            }
        }

        \Log::info("Final check for {$this->code}", [
            'applicableTotal' => $applicableTotal,
            'min_order_amount' => $this->min_order_amount,
            'is_eligible' => $applicableTotal >= $this->min_order_amount
        ]);

        // Kiểm tra điều kiện đơn tối thiểu
        if ($applicableTotal < $this->min_order_amount) {
            $missing = $this->min_order_amount - $applicableTotal;
            return [
                'eligible' => false, 
                'reason' => "Cần thêm " . number_format($missing, 0, '.', '.') . "đ sản phẩm thỏa điều kiện để áp dụng voucher", 
                'discount' => 0
            ];
        }

        return ['eligible' => true, 'reason' => null, 'discount' => $this->calculateDiscount($applicableTotal)];
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percent') {
            $d = $amount * ($this->discount_value / 100);
            if ($this->max_discount_amount) {
                $d = min($d, $this->max_discount_amount);
            }
            return round($d);
        }
        return min($this->discount_value, $amount);
    }
}
