# 🏗️ Promotion Engine — Production Architecture Design

---

## 1. Tổng quan kiến trúc

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT / API                            │
│              POST /api/cart/evaluate  (CartPayload)             │
└─────────────────────────┬───────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                  MarketingEngineService                         │
│  ┌──────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │ RuleLoader   │  │ ConditionEvaluator│  │  RewardApplicator│  │
│  │ (Cache TTL)  │→ │ (Strategy Pattern)│→ │  (Chain of Resp) │  │
│  └──────────────┘  └──────────────────┘  └──────────────────┘  │
└─────────────────────────┬───────────────────────────────────────┘
                          │
          ┌───────────────┼────────────────┐
          ▼               ▼                ▼
   marketing_rules  marketing_rule_   marketing_rule_
                    conditions        rewards
```

### Triết lý cốt lõi
- **Rule-Based Engine**: Logic nằm trong DB, code chỉ là Executor
- **Strategy Pattern**: Mỗi `condition_type` và `reward_type` là một Strategy độc lập
- **Open/Closed Principle**: Thêm loại condition/reward mới → chỉ thêm class, không sửa engine

---

## 2. Database Schema

### 2.1 `marketing_rules`
```sql
CREATE TABLE marketing_rules (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(255) NOT NULL,
    description     TEXT,
    
    -- Lifecycle
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    start_date      DATETIME NULL,
    end_date        DATETIME NULL,
    
    -- Conflict resolution
    priority        SMALLINT NOT NULL DEFAULT 100,    -- Số nhỏ hơn = ưu tiên cao hơn
    is_stackable    BOOLEAN NOT NULL DEFAULT FALSE,   -- FALSE = rule này chặn các rule thấp hơn
    exclusive_group VARCHAR(100) NULL,                 -- Cùng group = chỉ lấy 1 rule
    
    -- Usage limits
    usage_limit     INT NULL,                          -- NULL = vô hạn
    usage_count     INT NOT NULL DEFAULT 0,
    per_user_limit  INT NULL,                          -- Giới hạn mỗi user
    
    -- Condition operator at rule level
    condition_logic ENUM('AND', 'OR') NOT NULL DEFAULT 'AND',
    
    -- Metadata
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_active_dates (is_active, start_date, end_date),
    INDEX idx_priority (priority)
);
```

### 2.2 `marketing_rule_conditions`
```sql
CREATE TABLE marketing_rule_conditions (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rule_id         BIGINT UNSIGNED NOT NULL,
    
    -- Điều kiện thuộc group nào (để hỗ trợ (A AND B) OR (C AND D))
    group_id        TINYINT NOT NULL DEFAULT 1,
    group_logic     ENUM('AND', 'OR') NOT NULL DEFAULT 'AND', -- Logic TRONG group
    
    -- Loại điều kiện (Strategy selector)
    condition_type  ENUM(
        'cart_total',           -- Tổng giỏ hàng
        'cart_quantity',        -- Tổng số lượng sản phẩm
        'product_in_cart',      -- Có sản phẩm X trong giỏ
        'product_quantity',     -- Số lượng sản phẩm X
        'category_in_cart',     -- Có sản phẩm thuộc category Y
        'category_quantity',    -- Số lượng sản phẩm thuộc category Y
        'category_subtotal',    -- Tổng tiền sản phẩm thuộc category Y
        'user_segment',         -- Phân khúc user (new/loyal/vip)
        'coupon_code',          -- Mã coupon
        'payment_method',       -- Phương thức thanh toán
        'day_of_week',          -- Ngày trong tuần
        'time_of_day'           -- Giờ trong ngày
    ) NOT NULL,
    
    -- Toán tử so sánh
    operator        ENUM('=', '!=', '>', '>=', '<', '<=', 'in', 'not_in', 'between') NOT NULL,
    
    -- Giá trị điều kiện (linh hoạt dùng JSON)
    value           JSON NOT NULL,
    -- Ví dụ:
    -- cart_total + >=        : {"amount": 500000}
    -- product_in_cart + in   : {"product_ids": [12, 45, 78]}
    -- category_quantity + >= : {"category_id": 3, "quantity": 2}
    -- day_of_week + in       : {"days": [1, 2, 3, 4, 5]}
    -- time_of_day + between  : {"start": "08:00", "end": "22:00"}
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rule_id) REFERENCES marketing_rules(id) ON DELETE CASCADE,
    INDEX idx_rule_id (rule_id)
);
```

### 2.3 `marketing_rule_rewards`
```sql
CREATE TABLE marketing_rule_rewards (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rule_id         BIGINT UNSIGNED NOT NULL,
    
    -- Loại phần thưởng
    reward_type     ENUM(
        'gift_product',         -- Tặng sản phẩm
        'gift_product_choice',  -- User chọn 1 trong N sản phẩm
        'discount_percent',     -- Giảm % trên toàn bộ giỏ
        'discount_amount',      -- Giảm số tiền cố định
        'discount_product',     -- Giảm giá cho sản phẩm cụ thể
        'discount_category',    -- Giảm giá cho category
        'free_shipping',        -- Miễn phí vận chuyển
        'cashback_points'       -- Hoàn điểm tích lũy
    ) NOT NULL,
    
    -- Cấu hình phần thưởng (JSON linh hoạt)
    value           JSON NOT NULL,
    -- Ví dụ:
    -- gift_product          : {"product_id": 99, "quantity": 2, "variant_id": 5}
    -- gift_product_choice   : {"product_ids": [10, 20, 30], "pick": 1}
    -- discount_percent      : {"percent": 10, "max_discount": 50000}
    -- discount_amount       : {"amount": 30000}
    -- discount_product      : {"product_id": 12, "percent": 50}
    -- discount_category     : {"category_id": 3, "percent": 15}
    -- free_shipping         : {}
    -- cashback_points       : {"points": 100}
    
    -- Thứ tự áp dụng trong cùng rule
    sort_order      TINYINT NOT NULL DEFAULT 1,
    
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rule_id) REFERENCES marketing_rules(id) ON DELETE CASCADE,
    INDEX idx_rule_id (rule_id)
);
```

### 2.4 `marketing_rule_user_usage` (Tracking per-user limit)
```sql
CREATE TABLE marketing_rule_user_usage (
    id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    rule_id     BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    order_id    BIGINT UNSIGNED NULL,
    used_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rule_id) REFERENCES marketing_rules(id),
    INDEX idx_rule_user (rule_id, user_id)
);
```

---

## 3. Flow xử lý chi tiết

```
INPUT: CartPayload { items[], subtotal, user, coupon_code? }
                          │
                          ▼
          ┌───────────────────────────────┐
          │  STEP 1: Load Active Rules    │
          │  - Filter: is_active = true   │
          │  - Filter: start ≤ NOW ≤ end  │
          │  - Filter: usage_limit OK     │
          │  - ORDER BY priority ASC      │
          │  - Source: Redis Cache (TTL)  │
          └───────────────┬───────────────┘
                          │
                          ▼
          ┌───────────────────────────────┐
          │  STEP 2: Pre-filter Rules     │
          │  Quick reject (no DB query):  │
          │  - Rule cần min total?        │
          │    subtotal < min → skip      │
          │  - Rule cần sản phẩm X?       │
          │    product_ids ∉ cart → skip  │
          └───────────────┬───────────────┘
                          │
                          ▼
          ┌───────────────────────────────┐
          │  STEP 3: Validate Conditions  │
          │  foreach rule in active_rules │
          │    evaluateConditionGroups()  │
          │    → passed_rules[]           │
          └───────────────┬───────────────┘
                          │
                          ▼
          ┌───────────────────────────────┐
          │  STEP 4: Resolve Conflicts    │
          │  - Group exclusive_group      │
          │  - Trong group: lấy rule có   │
          │    priority cao nhất (nhỏ)    │
          │  - Xử lý is_stackable:        │
          │    Gặp rule !stackable →      │
          │    dừng xử lý rule phía sau   │
          └───────────────┬───────────────┘
                          │
                          ▼
          ┌───────────────────────────────┐
          │  STEP 5: Apply Rewards        │
          │  foreach rule in final_rules  │
          │    applyRewards(cart, rule)   │
          │    → cart.gifts[]             │
          │    → cart.discounts[]         │
          └───────────────┬───────────────┘
                          │
                          ▼
          ┌───────────────────────────────┐
          │  STEP 6: Calculate Final Cart │
          │  - Deduplicate gifts          │
          │  - Cap discounts (max 100%)   │
          │  - Recalculate total          │
          │  - Attach applied_rules[]     │
          └───────────────┬───────────────┘
                          │
                          ▼
OUTPUT: EnrichedCart { items[], gifts[], discounts[], 
                       total, applied_rules[], savings }
```

---

## 4. Pseudocode / Service Design

```php
// ============================================================
// CartPayload (Input DTO)
// ============================================================
class CartPayload {
    public array $items;     // [{product_id, category_id, quantity, price, variant_id}]
    public float $subtotal;  // Tổng tiền chưa giảm
    public ?User $user;
    public ?string $coupon_code;
}

// ============================================================
// STEP 1 + 2: RuleLoader với caching và pre-filter
// ============================================================
class RuleLoader {
    public function loadActiveRules(CartPayload $cart): Collection {
        $rules = Cache::remember('marketing:active_rules', 300, function () {
            return MarketingRule::with(['conditions', 'rewards'])
                ->where('is_active', true)
                ->where(fn($q) => $q
                    ->whereNull('start_date')->orWhere('start_date', '<=', now()))
                ->where(fn($q) => $q
                    ->whereNull('end_date')->orWhere('end_date', '>=', now()))
                ->where(fn($q) => $q
                    ->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit'))
                ->orderBy('priority')
                ->get();
        });

        // Pre-filter nhanh (không query DB)
        return $rules->filter(fn($rule) => $this->quickReject($rule, $cart) === false);
    }

    private function quickReject(MarketingRule $rule, CartPayload $cart): bool {
        $cartProductIds = collect($cart->items)->pluck('product_id')->toArray();
        $cartCategoryIds = collect($cart->items)->pluck('category_id')->unique()->toArray();

        foreach ($rule->conditions as $cond) {
            if ($cond->condition_type === 'cart_total' && $cond->operator === '>=') {
                if ($cart->subtotal < $cond->value['amount']) return true;
            }
            if ($cond->condition_type === 'product_in_cart') {
                $required = $cond->value['product_ids'];
                if (empty(array_intersect($required, $cartProductIds))) return true;
            }
            if ($cond->condition_type === 'category_in_cart') {
                if (!in_array($cond->value['category_id'], $cartCategoryIds)) return true;
            }
        }
        return false;
    }
}

// ============================================================
// STEP 3: validateConditions()
// ============================================================
class ConditionEvaluator {
    // Map condition_type → Strategy class
    private array $strategies = [
        'cart_total'        => CartTotalCondition::class,
        'cart_quantity'     => CartQuantityCondition::class,
        'product_in_cart'   => ProductInCartCondition::class,
        'product_quantity'  => ProductQuantityCondition::class,
        'category_in_cart'  => CategoryInCartCondition::class,
        'category_quantity' => CategoryQuantityCondition::class,
        'category_subtotal' => CategorySubtotalCondition::class,
        'user_segment'      => UserSegmentCondition::class,
        'coupon_code'       => CouponCodeCondition::class,
        'day_of_week'       => DayOfWeekCondition::class,
        'time_of_day'       => TimeOfDayCondition::class,
    ];

    public function validateConditions(CartPayload $cart, Collection $conditions): bool {
        // Group conditions theo group_id
        $groups = $conditions->groupBy('group_id');

        // Logic GIỮA các group: lấy từ rule.condition_logic (AND/OR)
        // Logic TRONG group: lấy từ condition.group_logic (AND/OR)
        $groupResults = $groups->map(fn($groupConditions) =>
            $this->evaluateGroup($cart, $groupConditions)
        );

        // Rule-level logic: mặc định AND giữa các group
        return $groupResults->every(fn($result) => $result === true);
    }

    private function evaluateGroup(CartPayload $cart, Collection $conditions): bool {
        $logic = $conditions->first()->group_logic; // AND | OR

        foreach ($conditions as $condition) {
            $strategy = app($this->strategies[$condition->condition_type]);
            $result = $strategy->evaluate($cart, $condition->operator, $condition->value);

            if ($logic === 'AND' && !$result) return false;
            if ($logic === 'OR' && $result) return true;
        }

        return $logic === 'AND'; // AND: tất cả pass → true; OR: không cái nào pass → false
    }
}

// ============================================================
// Ví dụ một Strategy condition cụ thể
// ============================================================
class CartTotalCondition implements ConditionStrategy {
    public function evaluate(CartPayload $cart, string $operator, array $value): bool {
        $amount = $value['amount'];
        return match($operator) {
            '>='      => $cart->subtotal >= $amount,
            '>'       => $cart->subtotal > $amount,
            '<='      => $cart->subtotal <= $amount,
            'between' => $cart->subtotal >= $value['min'] && $cart->subtotal <= $value['max'],
            default   => false,
        };
    }
}

// ============================================================
// STEP 4: Conflict resolution
// ============================================================
class ConflictResolver {
    public function resolve(Collection $passedRules): Collection {
        // 1. Xử lý exclusive_group: chỉ giữ rule priority cao nhất trong mỗi group
        $resolved = $passedRules->groupBy(fn($r) => $r->exclusive_group ?? 'rule_' . $r->id)
            ->map(fn($group) => $group->sortBy('priority')->first())
            ->values()
            ->sortBy('priority');

        // 2. Xử lý is_stackable: dừng tại rule non-stackable đầu tiên (inclusive)
        $final = collect();
        foreach ($resolved as $rule) {
            $final->push($rule);
            if (!$rule->is_stackable) break; // Rule này chặn tất cả rule phía sau
        }

        return $final;
    }
}

// ============================================================
// STEP 5: applyRewards()
// ============================================================
class RewardApplicator {
    private array $strategies = [
        'gift_product'        => GiftProductReward::class,
        'gift_product_choice' => GiftProductChoiceReward::class,
        'discount_percent'    => DiscountPercentReward::class,
        'discount_amount'     => DiscountAmountReward::class,
        'discount_product'    => DiscountProductReward::class,
        'discount_category'   => DiscountCategoryReward::class,
        'free_shipping'       => FreeShippingReward::class,
        'cashback_points'     => CashbackPointsReward::class,
    ];

    public function applyRewards(EnrichedCart $cart, MarketingRule $rule): EnrichedCart {
        foreach ($rule->rewards->sortBy('sort_order') as $reward) {
            $strategy = app($this->strategies[$reward->reward_type]);
            $cart = $strategy->apply($cart, $reward->value);
        }
        return $cart;
    }
}

// ============================================================
// STEP 5: Ví dụ Strategy Reward cụ thể
// ============================================================
class GiftProductReward implements RewardStrategy {
    public function apply(EnrichedCart $cart, array $value): EnrichedCart {
        $giftKey = "gift_{$value['product_id']}_{$value['variant_id']}";

        // Deduplicate: không thêm quà trùng
        if (!isset($cart->gifts[$giftKey])) {
            $cart->gifts[$giftKey] = [
                'product_id' => $value['product_id'],
                'variant_id' => $value['variant_id'] ?? null,
                'quantity'   => $value['quantity'],
                'price'      => 0,
                'is_gift'    => true,
            ];
        }
        return $cart;
    }
}

// ============================================================
// ORCHESTRATOR: runMarketingEngine()
// ============================================================
class MarketingEngineService {
    public function __construct(
        private RuleLoader        $loader,
        private ConditionEvaluator $evaluator,
        private ConflictResolver  $resolver,
        private RewardApplicator  $applicator,
    ) {}

    public function runMarketingEngine(CartPayload $cart): EnrichedCart {
        $enrichedCart = EnrichedCart::fromPayload($cart);

        // 1. Load + Pre-filter
        $rules = $this->loader->loadActiveRules($cart);

        // 2. Validate conditions
        $passed = $rules->filter(fn($rule) =>
            $this->evaluator->validateConditions($cart, $rule->conditions)
        );

        // 3. Resolve conflicts
        $final = $this->resolver->resolve($passed);

        // 4. Apply rewards
        foreach ($final as $rule) {
            $enrichedCart = $this->applicator->applyRewards($enrichedCart, $rule);
            $enrichedCart->applied_rules[] = [
                'id'   => $rule->id,
                'name' => $rule->name,
            ];
        }

        // 5. Finalize
        $enrichedCart->calculateFinalTotal();

        return $enrichedCart;
    }
}
```

---

## 5. Edge Cases

| Edge Case | Vấn đề | Giải pháp |
|---|---|---|
| **Nhiều rule cùng áp dụng** | Không biết rule nào thắng | `priority` + `exclusive_group` + `is_stackable` |
| **Trùng quà tặng** | 2 rule cùng tặng sản phẩm X | Dùng `gift_key` làm key trong `gifts[]` map |
| **User xóa sản phẩm** | Rule đã áp dụng nhưng điều kiện không còn | FE gọi lại `/api/cart/evaluate` sau mỗi thay đổi giỏ |
| **Rule hết hạn giữa chừng** | TTL cache 5 phút | Khi confirm order: re-validate không dùng cache |
| **Usage limit race condition** | 2 user cùng dùng 1 suất cuối | Dùng `DB::transaction` + pessimistic lock khi increment |
| **Nhiều rule, nhiều điều kiện** | N×M evaluation chậm | Pre-filter nhanh, cache rule, index DB đúng chỗ |
| **Gift product hết stock** | Tặng nhưng không có hàng | Check stock khi apply, fallback sang `gift_product_choice` |
| **Discount vượt 100%** | Tổng giảm > subtotal | Cap tại subtotal, không trả về số âm |
| **Rule conflict với coupon** | Coupon không stack với rule | Đặt `exclusive_group = 'coupon_group'` cho cả hai |

---

## 6. Optimization

### 6.1 Caching Strategy
```php
// Cache theo tầng:
// Layer 1: Active rules list (TTL 5 phút, invalidate khi admin lưu rule)
Cache::tags(['marketing_rules'])->remember('active_rules', 300, fn() => ...);

// Khi admin update rule:
Cache::tags(['marketing_rules'])->flush();

// Layer 2: Pre-computed rule index (product_ids, category_ids dùng trong conditions)
// Giúp pre-filter O(1) thay vì O(n×m)
Cache::remember('marketing:product_rule_map', 600, fn() => 
    MarketingRuleCondition::where('condition_type', 'product_in_cart')
        ->get()
        ->groupBy(fn($c) => $c->value['product_ids'])
        ->toArray()
);
```

### 6.2 Database Indexes
```sql
-- Lookup active rules nhanh
CREATE INDEX idx_rules_active_priority ON marketing_rules (is_active, start_date, end_date, priority);

-- Join conditions nhanh
CREATE INDEX idx_conditions_rule_type ON marketing_rule_conditions (rule_id, condition_type);

-- Per-user usage check
CREATE INDEX idx_usage_rule_user ON marketing_rule_user_usage (rule_id, user_id);
```

### 6.3 Pre-filter trước khi evaluate đầy đủ
```
1. Build CartIndex từ CartPayload (1 lần): 
   { product_ids: Set, category_ids: Set, subtotal, quantity_map }

2. Với mỗi Rule → Quick Reject trong O(1):
   - Cần total > X? Check subtotal
   - Cần product Y? Check Set.has(Y)
   - Cần category Z? Check Set.has(Z)
   → Reject ~70% rules trước khi evaluate condition đầy đủ
```

### 6.4 Tránh N+1 Query
```php
// BAD: Lazy load (N+1)
$rules->each(fn($r) => $r->conditions->each(fn($c) => ...));

// GOOD: Eager load tất cả cùng lúc
MarketingRule::with(['conditions', 'rewards'])->get();
```

### 6.5 Queue cho non-critical rewards
```php
// Cashback points, email notification → không cần sync
// Đẩy vào queue sau khi order confirmed
dispatch(new ApplyCashbackPointsJob($order, $appliedRules));
```

---

## 7. API Design

```
POST /api/v1/cart/evaluate
Body: { items[], coupon_code? }
Response: { items[], gifts[], discounts[], total, applied_rules[], savings }

// Gọi khi: thêm/xóa sản phẩm, nhập coupon, vào trang checkout
// Performance target: < 50ms (với cache hit)
```

---

## 8. Mở rộng tương lai

| Feature | Cách thêm |
|---|---|
| Condition mới (e.g., `weather`) | Tạo `WeatherCondition::class`, đăng ký trong `$strategies` map |
| Reward mới (e.g., `nft_badge`) | Tạo `NftBadgeReward::class`, đăng ký |
| Decision Tree thay AND/OR | Thêm cột `parent_condition_id` vào `marketing_rule_conditions` |
| A/B Testing rules | Thêm `experiment_group` vào `marketing_rules` |
| Rule builder UI (no-code) | Filament Resource trên `marketing_rules` 3 bảng đã có |
