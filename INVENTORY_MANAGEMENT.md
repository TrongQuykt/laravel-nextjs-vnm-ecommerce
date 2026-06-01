# Vinamilk Core Ecommerce - Inventory Management Architecture

## Table of Contents
1. [Inventory Management Overview](#inventory-management-overview)
2. [Stock Reservation Strategy](#stock-reservation-strategy)
3. [Concurrency Control Solutions](#concurrency-control-solutions)
4. [Database Schema Design](#database-schema-design)
5. [Stock Reservation Logic](#stock-reservation-logic)
6. [Inventory Import/Export Management](#inventory-importexport-management)
7. [Inventory History & Auditing](#inventory-history--auditing)
8. [Low Stock Alerts](#low-stock-alerts)
9. [Implementation Examples](#implementation-examples)
10. [Testing Strategy](#testing-strategy)

---

## Inventory Management Overview

### Problem Statement
Bài toán quản lý tồn kho khi có nhiều người cùng mua một sản phẩm tại một thời điểm (High Concurrency) là một thử thách kinh điển trong thương mại điện tử. Nếu xử lý không khéo, hệ thống sẽ gặp các lỗi như:
- **Race Condition:** Hai người cùng mua món hàng cuối cùng
- **Overselling:** Bán quá số lượng tồn kho
- **Payment Issues:** Khách đã trừ tiền nhưng đơn hàng bị hủy do hết kho

### Current Architecture Analysis
- **Stock Location:** `product_variants.stock_quantity` (integer)
- **Current State:** Không có logic trừ kho, không có concurrency control
- **Risk:** High - Có thể xảy ra overselling khi có nhiều user cùng mua

### Solution Architecture
Quy trình được chia làm 2 phần:
1. **Logic trừ kho:** Trừ khi nào?
2. **Giải pháp kỹ thuật:** Trừ như thế nào để không lỗi?

---

## Stock Reservation Strategy

### Strategy Comparison

#### Strategy 1: Hold-Stock / Pessimistic (Recommended)
**Mô tả:** Khi khách nhấn nút đặt hàng, hệ thống sẽ giữ (reserve) hoặc trừ tạm thời số lượng kho đó trong một khoảng thời gian quy định (ví dụ: 15-30 phút) để chờ thanh toán.

**Ưu điểm:**
- Đảm bảo khách hàng đã đặt hàng chắc chắn sẽ có hàng sau khi thanh toán thành công
- Không bao giờ bị bán quá số lượng (Overselling)
- Tránh tình trạng khách mất tiền oan

**Nhược điểm:**
- Dễ bị đối thủ hoặc bom hàng cố tình tạo đơn ảo để giữ hết kho
- Tồn kho bị giữ tạm thời, không bán được cho khách khác

**Xử lý:**
- Cài đặt cơ chế Cronjob hoặc Queue tự động hoàn lại kho (Release Stock) nếu quá thời gian giữ hàng mà khách chưa thanh toán
- Giới hạn số lượng đơn hàng giữ kho per user
- Sử dụng CAPTCHA để tránh bot

**Thời gian giữ kho theo phương thức thanh toán:**
- COD: 30 phút
- Bank Transfer: 15 phút
- VNPay/MoMo: 10 phút
- Credit Card: 5 phút

#### Strategy 2: Pay-Stock / Optimistic (Not Recommended)
**Mô tả:** Hệ thống cho phép đặt hàng thoải mái, chỉ khi nào có phản hồi thanh toán thành công (Webhook từ Momo, VNPay, Stripe...) thì mới chính thức trừ kho.

**Ưu điểm:**
- Không sợ bị giữ kho ảo
- Tối đa hóa khả năng bán hàng

**Nhược điểm:**
- Rủi ro cực cao khi nhiều người cùng thanh toán cho một sản phẩm có số lượng ít
- Dẫn đến tình trạng "thanh toán xong rồi nhưng hệ thống hết hàng, đơn bị hủy, khách mất tiền và phải đi hoàn tiền"
- Tốn chi phí hoàn tiền và làm mất uy tín

### Recommended Strategy: Hold-Stock with Timeout
Sử dụng **Chiến lược 1 (Hold-Stock):** Giữ kho ngay khi tạo đơn hàng thành công, nhưng đặt thời gian hết hạn ngắn (5 - 15 phút tùy phương thức thanh toán). Điều này giải quyết tận gốc việc khách bị mất tiền oan.

---

## Concurrency Control Solutions

### Solution A: Database Level (Recommended for Medium Scale)

#### 1. Pessimistic Locking (Khóa bi quan)
**Mô tả:** Khi User A vào mua, hệ thống sẽ "khóa" dòng sản phẩm đó lại. User B vào sau phải "xếp hàng" chờ User A xử lý xong (thành công hoặc thất bại) thì mới được đọc tiếp.

**Laravel Implementation:**
```php
use Illuminate\Support\Facades\DB;

class StockService
{
    public function reserveStock(int $productVariantId, int $quantity, string $orderNumber): bool
    {
        return DB::transaction(function () use ($productVariantId, $quantity, $orderNumber) {
            // FOR UPDATE sẽ khóa dòng này lại
            $variant = ProductVariant::where('id', $productVariantId)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                throw new \Exception('Sản phẩm không tồn tại');
            }

            // Kiểm tra số lượng tồn kho thực tế
            $availableStock = $variant->stock_quantity - $this->getReservedStock($productVariantId);
            
            if ($availableStock < $quantity) {
                throw new \Exception('Không đủ hàng trong kho');
            }

            // Tạo bản ghi giữ kho
            StockReservation::create([
                'product_variant_id' => $productVariantId,
                'order_number' => $orderNumber,
                'quantity' => $quantity,
                'reserved_at' => now(),
                'expires_at' => now()->addMinutes(15), // 15 phút
                'status' => 'pending',
            ]);

            return true;
        });
    }

    private function getReservedStock(int $productVariantId): int
    {
        return StockReservation::where('product_variant_id', $productVariantId)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->sum('quantity');
    }
}
```

**Ưu điểm:**
- Đảm bảo tính nhất quán dữ liệu
- Dễ implement với Laravel
- Không cần thêm infrastructure

**Nhược điểm:**
- Có thể gây deadlock nếu không cẩn thận
- Hiệu suất giảm khi có nhiều request đồng thời
- Database bottleneck khi load cao

#### 2. Optimistic Locking (Khóa lạc quan)
**Mô tả:** Không khóa dòng dữ liệu, nhưng thêm một điều kiện kiểm tra số lượng tồn kho lúc cập nhật.

**Laravel Implementation:**
```php
class StockService
{
    public function reserveStockOptimistic(int $productVariantId, int $quantity, string $orderNumber): bool
    {
        $variant = ProductVariant::find($productVariantId);
        
        if (!$variant) {
            throw new \Exception('Sản phẩm không tồn tại');
        }

        // Cập nhật với điều kiện stock >= quantity
        $affected = DB::table('product_variants')
            ->where('id', $productVariantId)
            ->where('stock_quantity', '>=', $quantity)
            ->update([
                'stock_quantity' => DB::raw("stock_quantity - {$quantity}"),
            ]);

        if ($affected === 0) {
            throw new \Exception('Không đủ hàng trong kho');
        }

        // Tạo bản ghi lịch sử
        StockMovement::create([
            'product_variant_id' => $productVariantId,
            'order_number' => $orderNumber,
            'quantity' => -$quantity,
            'type' => 'reservation',
            'reference_type' => 'order',
            'reference_id' => $orderNumber,
        ]);

        return true;
    }
}
```

**Ưu điểm:**
- Không gây deadlock
- Hiệu suất tốt hơn pessimistic locking
- Phù hợp cho hệ thống có nhiều read, ít write

**Nhược điểm:**
- Có thể fail nhiều lần khi có nhiều request đồng thời
- Cần implement retry logic
- Không phù hợp cho hệ thống có nhiều write

### Solution B: Redis Distributed Lock (Recommended for Large Scale)

#### Architecture
```
Client → API Gateway → Redis (Stock Check) → Queue → Database
```

#### Implementation

**1. Setup Redis Stock:**
```php
class RedisStockService
{
    private $redis;
    private const STOCK_KEY_PREFIX = 'stock:';
    private const RESERVATION_KEY_PREFIX = 'reservation:';
    private const LOCK_KEY_PREFIX = 'lock:';

    public function __construct()
    {
        $this->redis = Redis::connection();
    }

    /**
     * Khởi tạo stock trong Redis
     */
    public function initializeStock(int $productVariantId, int $quantity): void
    {
        $key = self::STOCK_KEY_PREFIX . $productVariantId;
        $this->redis->set($key, $quantity);
    }

    /**
     * Đồng bộ stock từ Database sang Redis
     */
    public function syncStockFromDatabase(int $productVariantId): void
    {
        $variant = ProductVariant::find($productVariantId);
        $key = self::STOCK_KEY_PREFIX . $productVariantId;
        $this->redis->set($key, $variant->stock_quantity);
    }
}
```

**2. Atomic Stock Reservation with Lua Script:**
```php
class RedisStockService
{
    /**
     * Giữ kho nguyên tử bằng Lua Script
     */
    public function reserveStockAtomic(int $productVariantId, int $quantity, string $orderNumber): bool
    {
        $luaScript = <<<'LUA'
            local stockKey = KEYS[1]
            local reservationKey = KEYS[2]
            local quantity = tonumber(ARGV[1])
            local orderNumber = ARGV[2]
            local ttl = tonumber(ARGV[3])
            
            -- Lấy stock hiện tại
            local currentStock = tonumber(redis.call('GET', stockKey) or 0)
            
            -- Kiểm tra xem có đủ stock không
            if currentStock < quantity then
                return 0
            end
            
            -- Trừ stock
            redis.call('DECRBY', stockKey, quantity)
            
            -- Lưu thông tin reservation
            redis.call('HSET', reservationKey, 'order_number', orderNumber)
            redis.call('HSET', reservationKey, 'quantity', quantity)
            redis.call('HSET', reservationKey, 'reserved_at', ARGV[4])
            redis.call('EXPIRE', reservationKey, ttl)
            
            return 1
        LUA;

        $stockKey = self::STOCK_KEY_PREFIX . $productVariantId;
        $reservationKey = self::RESERVATION_KEY_PREFIX . $orderNumber;
        
        $result = $this->redis->eval(
            $luaScript,
            2,
            $stockKey,
            $reservationKey,
            $quantity,
            $orderNumber,
            900, // 15 phút TTL
            now()->timestamp
        );

        if ($result == 0) {
            throw new \Exception('Không đủ hàng trong kho');
        }

        // Đẩy vào Queue để xử lý ghi vào Database
        ProcessReservationJob::dispatch($productVariantId, $quantity, $orderNumber);

        return true;
    }
}
```

**3. Release Stock (Hủy giữ kho):**
```php
class RedisStockService
{
    /**
     * Hoàn lại kho khi đơn hàng hủy/hết hạn
     */
    public function releaseStock(string $orderNumber): void
    {
        $reservationKey = self::RESERVATION_KEY_PREFIX . $orderNumber;
        
        // Lấy thông tin reservation
        $reservation = $this->redis->hGetAll($reservationKey);
        
        if (empty($reservation)) {
            return;
        }

        $quantity = (int) $reservation['quantity'];
        
        // Lấy product_variant_id từ order
        $order = Order::where('order_number', $orderNumber)->first();
        if (!$order) {
            return;
        }

        $productVariantId = $order->items()->first()->product_variant_id;
        $stockKey = self::STOCK_KEY_PREFIX . $productVariantId;

        // Cộng lại stock
        $this->redis->incrBy($stockKey, $quantity);
        
        // Xóa reservation
        $this->redis->del($reservationKey);

        // Đẩy vào Queue để cập nhật Database
        ReleaseStockJob::dispatch($productVariantId, $quantity, $orderNumber);
    }
}
```

**4. Confirm Stock (Xác nhận đơn hàng):**
```php
class RedisStockService
{
    /**
     * Xác nhận giữ kho khi thanh toán thành công
     */
    public function confirmStock(string $orderNumber): void
    {
        $reservationKey = self::RESERVATION_KEY_PREFIX . $orderNumber;
        
        // Xóa reservation (đã được confirm)
        $this->redis->del($reservationKey);

        // Đẩy vào Queue để ghi log vào Database
        ConfirmStockJob::dispatch($orderNumber);
    }
}
```

**Ưu điểm:**
- Hiệu suất cực cao (Redis in-memory)
- Xử lý được hàng triệu request/giây
- Không gây database bottleneck
- Atomic operations đảm bảo tính nhất quán

**Nhược điểm:**
- Cần thêm infrastructure (Redis)
- Cần đồng bộ dữ liệu giữa Redis và Database
- Cần handle Redis failure

### Solution Comparison

| Solution | Scale | Complexity | Performance | Consistency | Recommended |
|----------|-------|------------|-------------|-------------|-------------|
| Pessimistic Locking | Small-Medium | Low | Medium | High | ✅ Yes |
| Optimistic Locking | Medium | Low | High | Medium | ⚠️ Conditional |
| Redis Distributed Lock | Large | High | Very High | High | ✅ Yes |

**Recommendation:**
- **Small-Medium Scale:** Pessimistic Locking
- **Large Scale / Flash Sale:** Redis Distributed Lock

---

## Database Schema Design

### Implementation Status
✅ **Completed Implementation:**
- Added inventory fields to `product_variants` table
- Created `stock_reservations` table
- Created `stock_movements` table
- Created `warehouses` table
- Created `stock_alerts` table
- Created all corresponding models
- Created StockService with full functionality
- Created console commands for automated tasks

### New Tables Required

#### 1. stock_reservations
```php
// ✅ IMPLEMENTED
Schema::create('stock_reservations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
    $table->string('order_number')->index();
    $table->integer('quantity');
    $table->timestamp('reserved_at');
    $table->timestamp('expires_at')->index();
    $table->enum('status', ['pending', 'confirmed', 'released', 'expired'])->default('pending');
    $table->timestamps();
    
    $table->index(['product_variant_id', 'status']);
    $table->index(['expires_at', 'status']);
    $table->index(['order_number', 'status']);
});
```

**File:** `database/migrations/2026_06_01_000002_create_stock_reservations_table.php`

**Model:** `app/Models/StockReservation.php`

**Purpose:** Lưu thông tin giữ kho tạm thời

#### 2. stock_movements
```php
// ✅ IMPLEMENTED
Schema::create('stock_movements', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
    $table->integer('quantity'); // Positive for import, Negative for export
    $table->enum('type', [
        'import',          // Nhập kho
        'export',          // Xuất kho
        'reservation',     // Giữ kho
        'release',         // Hoàn kho
        'adjustment',      // Điều chỉnh
        'return',         // Trả hàng
        'damage',          // Hư hỏng
        'transfer',        // Chuyển kho
    ])->index();
    $table->string('reference_type')->nullable()->index(); // order, purchase_order, adjustment, etc.
    $table->string('reference_id')->nullable()->index();
    $table->text('notes')->nullable();
    $table->foreignId('warehouse_id')->nullable()->constrained();
    $table->foreignId('user_id')->nullable()->constrained();
    $table->timestamps();
    
    $table->index(['product_variant_id', 'type']);
    $table->index(['reference_type', 'reference_id']);
    $table->index('created_at');
});
```

**File:** `database/migrations/2026_06_01_000003_create_stock_movements_table.php`

**Model:** `app/Models/StockMovement.php`

**Purpose:** Lịch sử tất cả các movement của tồn kho

#### 3. purchase_orders (Đơn nhập hàng)
```php
Schema::create('purchase_orders', function (Blueprint $table) {
    $table->id();
    $table->string('po_number')->unique();
    $table->foreignId('supplier_id')->nullable();
    $table->enum('status', ['draft', 'pending', 'partial', 'received', 'cancelled'])->default('draft');
    $table->date('expected_date')->nullable();
    $table->date('received_date')->nullable();
    $table->text('notes')->nullable();
    $table->foreignId('warehouse_id')->nullable()->constrained();
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
});
```

**Purpose:** Quản lý đơn nhập hàng từ nhà cung cấp

#### 4. purchase_order_items
```php
Schema::create('purchase_order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_variant_id')->constrained();
    $table->integer('quantity_ordered');
    $table->integer('quantity_received')->default(0);
    $table->decimal('unit_cost', 15, 2);
    $table->decimal('total_cost', 15, 2);
    $table->timestamps();
});
```

**Purpose:** Chi tiết đơn nhập hàng

#### 5. warehouses
```php
// ✅ IMPLEMENTED
Schema::create('warehouses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->text('address');
    $table->string('phone')->nullable();
    $table->string('manager')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**File:** `database/migrations/2026_06_01_000004_create_warehouses_table.php`

**Model:** `app/Models/Warehouse.php`

**Purpose:** Quản lý kho hàng

#### 6. stock_adjustments
```php
Schema::create('stock_adjustments', function (Blueprint $table) {
    $table->id();
    $table->string('adjustment_number')->unique();
    $table->foreignId('warehouse_id')->constrained();
    $table->enum('reason', [
        'damage',
        'loss',
        'theft',
        'count_difference',
        'expiry',
        'quality_issue',
        'other'
    ]);
    $table->text('notes')->nullable();
    $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();
});
```

**Purpose:** Quản lý điều chỉnh tồn kho

#### 7. stock_adjustment_items
```php
Schema::create('stock_adjustment_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stock_adjustment_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_variant_id')->constrained();
    $table->integer('current_quantity');
    $table->integer('adjusted_quantity');
    $table->integer('difference');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

**Purpose:** Chi tiết điều chỉnh tồn kho

### Update Existing Tables

#### Update product_variants table
```php
// ✅ IMPLEMENTED
Schema::table('product_variants', function (Blueprint $table) {
    $table->integer('reserved_quantity')->default(0)->after('stock_quantity');
    // Note: available_quantity virtual column attempted but not created due to MySQL compatibility
    // Calculated in API resource layer as: stock_quantity - reserved_quantity
    $table->timestamp('last_stock_update')->nullable();
    $table->integer('low_stock_threshold')->default(10);
    $table->integer('out_of_stock_threshold')->default(0);
});
```

**File:** `database/migrations/2026_06_01_000001_add_inventory_fields_to_product_variants_table.php`

**Note:** The virtual column for `available_quantity` was attempted in the migration but not successfully created due to MySQL compatibility issues. Instead, `available_quantity` is calculated in the API resource layer as `stock_quantity - reserved_quantity`.

---

## Frontend Stock Status Display - ✅ IMPLEMENTED

### Product Resource API Updates
**File:** `app/Http/Resources/Api/ProductResource.php`

**Stock Status Fields in API Response:**
```php
return [
    'id' => $v->id,
    'stock_quantity' => $v->stock_quantity,
    'reserved_quantity' => (int) ($v->reserved_quantity ?? 0),
    'available_quantity' => (int) ($v->stock_quantity - ($v->reserved_quantity ?? 0)),
    'stock_status' => $v->stock_status_label ?? 'in_stock',
    'is_in_stock' => $v->stock_quantity > 0,
    'is_low_stock' => $v->stock_quantity <= ($v->low_stock_threshold ?? 10) && $v->stock_quantity > 0,
    'is_out_of_stock' => $v->stock_quantity <= 0,
    'units_per_pack' => (int) ($v->units_per_pack ?? 1),
    // ... other fields
];
```

### Frontend Components Updates
**File:** `vinamilk-fe/src/components/catalog/ProductDetailView.tsx`

**Stock Status Display:**
- Badge showing stock status (Còn hàng / Sắp hết hàng / Hết hàng)
- Color coding: Green (in stock), Orange (low stock), Red (out of stock)
- Shows available quantity for low stock items

**Add to Cart Button:**
- Disabled when `available_quantity <= 0`
- Shows "Hết hàng" instead of price when out of stock

**Variant Selector:**
- Shows "Hết hàng" badge for out of stock variants
- Shows "Còn X" badge for low stock variants

**TypeScript Types:**
**File:** `vinamilk-fe/src/types/index.ts`

```typescript
export interface ProductVariant {
    // ... existing fields
    stock_quantity: number;
    reserved_quantity: number;
    available_quantity: number;
    stock_status: 'in_stock' | 'low_stock' | 'out_of_stock';
    is_in_stock: boolean;
    is_low_stock: boolean;
    is_out_of_stock: boolean;
    units_per_pack: number;
}
```

---

## Implementation Details

### ProductVariant Model Updates
**File:** `app/Models/ProductVariant.php`

**New Fields:**
- `reserved_quantity` - Số lượng đang được giữ
- `last_stock_update` - Thời gian cập nhật tồn kho
- `low_stock_threshold` - Ngưỡng cảnh báo tồn kho thấp (default: 10)
- `out_of_stock_threshold` - Ngưỡng cảnh báo hết hàng (default: 0)

**Note:** `available_quantity` is calculated as `stock_quantity - reserved_quantity` in the API resource layer (not a virtual column in database due to MySQL compatibility issues).

**New Methods:**
```php
// Check if variant is in stock
$variant->isInStock(); // bool

// Check if variant has low stock
$variant->isLowStock(); // bool

// Check if variant is out of stock
$variant->isOutOfStock(); // bool

// Get stock status label
$variant->stock_status_label; // string: 'Còn hàng', 'Sắp hết hàng', 'Hết hàng'

// Relationships
$variant->stockReservations(); // HasMany
$variant->stockMovements(); // HasMany
$variant->stockAlerts(); // HasMany
```

### StockService
**File:** `app/Services/StockService.php`

**Available Methods:**
```php
// Reserve stock for an order
$stockService->reserveStock($productVariantId, $quantity, $orderNumber, $timeoutMinutes = 15);

// Confirm stock when payment successful
$stockService->confirmStock($orderNumber);

// Release stock when order cancelled/expired
$stockService->releaseStock($orderNumber);

// Release expired reservations (cron job)
$stockService->releaseExpiredReservations(); // returns int

// Add stock (import)
$stockService->addStock($productVariantId, $quantity, $referenceType, $referenceId, $warehouseId, $notes);

// Adjust stock (manual)
$stockService->adjustStock($productVariantId, $newQuantity, $reason, $notes);

// Get available stock
$stockService->getAvailableStock($productVariantId); // int

// Check if stock available
$stockService->isStockAvailable($productVariantId, $quantity); // bool

// Get movement history
$stockService->getMovementHistory($productVariantId, $filters); // paginated
```

### Console Commands

#### Release Expired Reservations - ✅ IMPLEMENTED
**File:** `app/Console/Commands/ReleaseExpiredReservationsCommand.php`

```bash
php artisan inventory:release-expired
```

**Schedule in routes/console.php:**
```php
Schedule::command('inventory:release-expired')
    ->everyMinute()
    ->description('Release expired stock reservations');
```

#### Check Low Stock - ✅ IMPLEMENTED
**File:** `app/Console/Commands/CheckLowStockCommand.php`

```bash
php artisan inventory:check-low-stock
```

**Schedule in routes/console.php:**
```php
Schedule::command('inventory:check-low-stock')
    ->hourly()
    ->description('Check for low stock and out of stock items');
```

### Running Migrations
```bash
# Run all new migrations
php artisan migrate

# Or run specific migration
php artisan migrate --path=database/migrations/2026_06_01_000001_add_inventory_fields_to_product_variants_table.php
php artisan migrate --path=database/migrations/2026_06_01_000002_create_stock_reservations_table.php
php artisan migrate --path=database/migrations/2026_06_01_000003_create_stock_movements_table.php
php artisan migrate --path=database/migrations/2026_06_01_000004_create_warehouses_table.php
php artisan migrate --path=database/migrations/2026_06_01_000005_create_stock_alerts_table.php
```

---

## Stock Reservation Logic

### Order State Machine

```
┌─────────────┐
│  CART       │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  PENDING    │ ← Giữ kho tại đây
│  PAYMENT    │
└──────┬──────┘
       │
       ├─→ SUCCESS ──→ PAID ──→ CONFIRMED ──→ PROCESSING
       │
       ├─→ FAILED ──→ CANCELLED ──→ Release Stock
       │
       └─→ TIMEOUT ──→ EXPIRED ──→ Release Stock
```

### Complete Order Flow with Stock Management

#### Step 1: Customer Places Order
```php
class OrderService
{
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // 1. Validate cart
            $cart = Cart::with('items.productVariant')->find($data['cart_id']);
            
            // 2. Reserve stock for each item
            foreach ($cart->items as $item) {
                $this->stockService->reserveStock(
                    $item->product_variant_id,
                    $item->quantity,
                    $orderNumber
                );
            }
            
            // 3. Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $data['user_id'],
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                // ... other fields
            ]);
            
            // 4. Create order items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product_variant->price,
                ]);
            }
            
            // 5. Clear cart
            $cart->items()->delete();
            
            return $order;
        });
    }
}
```

#### Step 2: Payment Processing
```php
class PaymentService
{
    public function processPayment(string $orderNumber, array $paymentData): Payment
    {
        $order = Order::where('order_number', $orderNumber)->first();
        
        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => $paymentData['method'],
            'status' => 'processing',
        ]);
        
        // Redirect to payment gateway
        return $this->paymentGateway->createPayment($order, $payment);
    }
}
```

#### Step 3: Payment Success (Webhook)
```php
class PaymentWebhookController extends Controller
{
    public function handle(Request $request, string $gateway)
    {
        return DB::transaction(function () use ($request, $gateway) {
            // 1. Validate webhook signature
            if (!$this->validateWebhook($request, $gateway)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            
            // 2. Parse webhook data
            $data = $this->parseWebhook($request, $gateway);
            $orderNumber = $data['order_number'];
            
            // 3. Check idempotency (tránh xử lý trùng)
            if ($this->isWebhookProcessed($orderNumber, $data['transaction_id'])) {
                return response()->json(['message' => 'Already processed'], 200);
            }
            
            // 4. Find order
            $order = Order::where('order_number', $orderNumber)->first();
            
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }
            
            // 5. Process based on payment status
            if ($data['status'] === 'success') {
                // Update payment status
                $order->payment()->update([
                    'status' => 'success',
                    'transaction_id' => $data['transaction_id'],
                ]);
                
                // Update order status
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                ]);
                
                // Confirm stock reservation
                $this->stockService->confirmStock($orderNumber);
                
                // Log stock movement
                foreach ($order->items as $item) {
                    StockMovement::create([
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => -$item->quantity,
                        'type' => 'export',
                        'reference_type' => 'order',
                        'reference_id' => $order->order_number,
                    ]);
                }
                
                // Send notifications
                event(new OrderPaid($order));
                
            } elseif ($data['status'] === 'failed') {
                // Update payment status
                $order->payment()->update([
                    'status' => 'failed',
                ]);
                
                // Update order status
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                ]);
                
                // Release stock
                $this->stockService->releaseStock($orderNumber);
                
                // Send notifications
                event(new OrderFailed($order));
            }
            
            // 6. Mark webhook as processed
            $this->markWebhookProcessed($orderNumber, $data['transaction_id']);
            
            return response()->json(['message' => 'Processed'], 200);
        });
    }
}
```

#### Step 4: Handle Expired Reservations (Cron Job)
```php
class ReleaseExpiredReservationsCommand extends Command
{
    protected $signature = 'inventory:release-expired';
    
    public function handle()
    {
        // Find expired reservations
        $expiredReservations = StockReservation::where('expires_at', '<', now())
            ->where('status', 'pending')
            ->get();
        
        foreach ($expiredReservations as $reservation) {
            DB::transaction(function () use ($reservation) {
                // Update reservation status
                $reservation->update(['status' => 'expired']);
                
                // Release stock
                $variant = ProductVariant::where('id', $reservation->product_variant_id)
                    ->lockForUpdate()
                    ->first();
                
                $variant->increment('stock_quantity', $reservation->quantity);
                
                // Log stock movement
                StockMovement::create([
                    'product_variant_id' => $reservation->product_variant_id,
                    'quantity' => $reservation->quantity,
                    'type' => 'release',
                    'reference_type' => 'reservation',
                    'reference_id' => $reservation->order_number,
                ]);
                
                // Update order status
                $order = Order::where('order_number', $reservation->order_number)->first();
                if ($order) {
                    $order->update(['status' => 'expired']);
                }
            });
        }
        
        $this->info("Released {$expiredReservations->count()} expired reservations");
    }
}
```

**Schedule in Kernel.php:**
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('inventory:release-expired')->everyMinute();
}
```

---

## Inventory Import/Export Management

### Import Stock (Nhập kho)

#### Create Purchase Order
```php
class PurchaseOrderService
{
    public function createPurchaseOrder(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . strtoupper(uniqid()),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'expected_date' => $data['expected_date'],
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);
            
            foreach ($data['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity_ordered' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }
            
            return $po;
        });
    }
}
```

#### Receive Purchase Order (Nhận hàng)
```php
class PurchaseOrderService
{
    public function receivePurchaseOrder(int $purchaseOrderId, array $data): void
    {
        DB::transaction(function () use ($purchaseOrderId, $data) {
            $po = PurchaseOrder::with('items')->find($purchaseOrderId);
            
            foreach ($data['items'] as $item) {
                $poItem = $po->items->where('product_variant_id', $item['product_variant_id'])->first();
                
                // Update received quantity
                $poItem->update([
                    'quantity_received' => $poItem->quantity_received + $item['quantity_received'],
                ]);
                
                // Add to stock
                $variant = ProductVariant::where('id', $item['product_variant_id'])
                    ->lockForUpdate()
                    ->first();
                
                $variant->increment('stock_quantity', $item['quantity_received']);
                
                // Log stock movement
                StockMovement::create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity_received'],
                    'type' => 'import',
                    'reference_type' => 'purchase_order',
                    'reference_id' => $po->po_number,
                    'warehouse_id' => $po->warehouse_id,
                    'user_id' => auth()->id(),
                ]);
                
                // Sync to Redis if using
                if (config('inventory.use_redis')) {
                    app(RedisStockService::class)->syncStockFromDatabase($item['product_variant_id']);
                }
            }
            
            // Update PO status
            $allReceived = $po->items->every(function ($item) {
                return $item->quantity_received >= $item->quantity_ordered;
            });
            
            if ($allReceived) {
                $po->update([
                    'status' => 'received',
                    'received_date' => now(),
                ]);
            } else {
                $po->update(['status' => 'partial']);
            }
        });
    }
}
```

### Export Stock (Xuất kho)

#### Manual Stock Export
```php
class StockExportService
{
    public function exportStock(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data['items'] as $item) {
                // Check and reserve stock
                $variant = ProductVariant::where('id', $item['product_variant_id'])
                    ->lockForUpdate()
                    ->first();
                
                if ($variant->stock_quantity < $item['quantity']) {
                    throw new \Exception('Không đủ hàng trong kho');
                }
                
                // Deduct stock
                $variant->decrement('stock_quantity', $item['quantity']);
                
                // Log stock movement
                StockMovement::create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => -$item['quantity'],
                    'type' => 'export',
                    'reference_type' => $data['reference_type'],
                    'reference_id' => $data['reference_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'user_id' => auth()->id(),
                    'notes' => $data['notes'],
                ]);
                
                // Sync to Redis if using
                if (config('inventory.use_redis')) {
                    app(RedisStockService::class)->syncStockFromDatabase($item['product_variant_id');
                }
            }
        });
    }
}
```

### Stock Adjustment (Điều chỉnh tồn kho)

#### Create Stock Adjustment
```php
class StockAdjustmentService
{
    public function createAdjustment(array $data): StockAdjustment
    {
        return DB::transaction(function () use ($data) {
            $adjustment = StockAdjustment::create([
                'adjustment_number' => 'ADJ-' . strtoupper(uniqid()),
                'warehouse_id' => $data['warehouse_id'],
                'reason' => $data['reason'],
                'notes' => $data['notes'],
                'status' => 'pending',
            ]);
            
            foreach ($data['items'] as $item) {
                $variant = ProductVariant::find($item['product_variant_id']);
                
                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'current_quantity' => $variant->stock_quantity,
                    'adjusted_quantity' => $item['adjusted_quantity'],
                    'difference' => $item['adjusted_quantity'] - $variant->stock_quantity,
                    'notes' => $item['notes'],
                ]);
            }
            
            return $adjustment;
        });
    }
}
```

#### Approve Stock Adjustment
```php
class StockAdjustmentService
{
    public function approveAdjustment(int $adjustmentId): void
    {
        DB::transaction(function () use ($adjustmentId) {
            $adjustment = StockAdjustment::with('items')->find($adjustmentId);
            
            foreach ($adjustment->items as $item) {
                $variant = ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();
                
                // Update stock
                $variant->update([
                    'stock_quantity' => $item->adjusted_quantity,
                ]);
                
                // Log stock movement
                StockMovement::create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->difference,
                    'type' => 'adjustment',
                    'reference_type' => 'adjustment',
                    'reference_id' => $adjustment->adjustment_number,
                    'warehouse_id' => $adjustment->warehouse_id,
                    'user_id' => auth()->id(),
                    'notes' => $item->notes,
                ]);
                
                // Sync to Redis if using
                if (config('inventory.use_redis')) {
                    app(RedisStockService::class)->syncStockFromDatabase($item->product_variant_id);
                }
            }
            
            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });
    }
}
```

---

## Inventory History & Auditing

### Stock Movement Tracking

#### View Stock History
```php
class StockMovementService
{
    public function getMovementHistory(int $productVariantId, array $filters = [])
    {
        $query = StockMovement::with(['user', 'warehouse'])
            ->where('product_variant_id', $productVariantId);
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }
        
        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(50);
    }
}
```

#### Calculate Stock Balance
```php
class StockMovementService
{
    public function calculateStockBalance(int $productVariantId, ?Carbon $fromDate = null): int
    {
        $query = StockMovement::where('product_variant_id', $productVariantId);
        
        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }
        
        return $query->sum('quantity');
    }
}
```

### Audit Log

#### Stock Movement Observer
```php
class StockMovementObserver
{
    public function created(StockMovement $movement)
    {
        // Log to audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'stock_movement',
            'resource_type' => StockMovement::class,
            'resource_id' => $movement->id,
            'old_values' => null,
            'new_values' => $movement->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

---

## Low Stock Alerts

### Alert Configuration

#### Low Stock Thresholds
```php
class ProductVariant extends Model
{
    protected $fillable = [
        // ... existing fields
        'low_stock_threshold',
        'out_of_stock_threshold',
    ];

    protected $casts = [
        'low_stock_threshold' => 'integer',
        'out_of_stock_threshold' => 'integer',
    ];
}
```

#### Check Low Stock (Cron Job) - ✅ IMPLEMENTED
**File:** `app/Console/Commands/CheckLowStockCommand.php`

```bash
php artisan inventory:check-low-stock
```

**Implementation Details:**
- Checks for low stock variants (stock_quantity <= low_stock_threshold and > 0)
- Checks for out of stock variants (stock_quantity <= 0)
- Prevents duplicate alerts by checking for existing unresolved alerts
- Creates StockAlert records for each variant
- Logs warnings/errors for admin visibility

**Schedule in routes/console.php:**
```php
Schedule::command('inventory:check-low-stock')
    ->hourly()
    ->description('Check for low stock and out of stock items');
```

### Stock Alert Table - ✅ IMPLEMENTED
**File:** `database/migrations/2026_06_01_000005_create_stock_alerts_table.php`

```php
Schema::create('stock_alerts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['low_stock', 'out_of_stock']);
    $table->integer('current_quantity');
    $table->integer('threshold');
    $table->boolean('is_resolved')->default(false);
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();

    $table->index(['product_variant_id', 'is_resolved']);
    $table->index('created_at');
});
```

**Model:** `app/Models/StockAlert.php`

**Features:**
- `markAsResolved()` method to mark alerts as resolved
- Scopes: `unresolved()`, `resolved()`, `byType()`
- Relationship with ProductVariant

### Stock Alert Resource (Admin Panel) - ✅ IMPLEMENTED
**File:** `app/Filament/Resources/StockAlertResource.php`

**Navigation:** Kho hàng → Cảnh báo tồn kho

**Features:**
- Display product name and variant (volume - packaging type)
- Badge color for alert type (warning for low stock, danger for out of stock)
- Filter by type (Sắp hết hàng / Hết hàng)
- Filter by unresolved alerts
- "Đánh dấu đã giải quyết" action button
- Default sort by created_at (newest first)

**Accessor for Variant Display:**
```php
public function getVariantDisplayNameAttribute(): string
{
    $variant = $this->productVariant;
    if (!$variant) return 'N/A';

    $parts = [];
    if ($variant->volume && $variant->volume->name) $parts[] = $variant->volume->name;
    if ($variant->packagingType && $variant->packagingType->name) $parts[] = $variant->packagingType->name;

    $result = implode(' - ', array_filter($parts));
    return $result ?: 'Variant #' . $variant->id;
}
```

---

## Implementation Examples

### Complete Order Creation with Stock Reservation

```php
class OrderController extends Controller
{
    public function store(CreateOrderRequest $request)
    {
        try {
            $order = DB::transaction(function () use ($request) {
                // Generate order number
                $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT));
                
                // Get cart
                $cart = Cart::with('items.productVariant')->where('user_id', auth()->id())->first();
                
                if (!$cart || $cart->items->isEmpty()) {
                    throw new \Exception('Giỏ hàng trống');
                }
                
                // Reserve stock for each item
                foreach ($cart->items as $item) {
                    try {
                        app(StockService::class)->reserveStock(
                            $item->product_variant_id,
                            $item->quantity,
                            $orderNumber
                        );
                    } catch (\Exception $e) {
                        throw new \Exception("Sản phẩm {$item->productVariant->name} không đủ hàng");
                    }
                }
                
                // Calculate totals
                $subtotal = $cart->items->sum(function ($item) {
                    return $item->quantity * $item->productVariant->price;
                });
                
                // Apply discounts
                $discount = 0;
                if ($request->coupon_code) {
                    $discount = app(CouponService::class)->applyCoupon($request->coupon_code, $subtotal);
                }
                
                // Calculate shipping
                $shippingFee = app(ShippingService::class)->calculateShippingFee($request->shipping_address);
                
                $total = $subtotal - $discount + $shippingFee;
                
                // Create order
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => auth()->id(),
                    'status' => 'pending_payment',
                    'payment_status' => 'pending',
                    'shipping_address' => $request->shipping_address,
                    'shipping_method_id' => $request->shipping_method_id,
                    'total_amount' => $total,
                    'discount_amount' => $discount,
                    'shipping_cost' => $shippingFee,
                    'notes' => $request->notes,
                ]);
                
                // Create order items
                foreach ($cart->items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'price' => $item->productVariant->price,
                        'subtotal' => $item->quantity * $item->productVariant->price,
                    ]);
                }
                
                // Create payment record
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $total,
                    'method' => $request->payment_method,
                    'status' => 'pending',
                ]);
                
                // Clear cart
                $cart->items()->delete();
                
                return $order;
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'payment_url' => app(PaymentService::class)->getPaymentUrl($order),
                ],
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

### Redis-Based Stock Reservation

```php
class RedisStockController extends Controller
{
    public function reserveStock(Request $request)
    {
        try {
            $request->validate([
                'product_variant_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
            ]);
            
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT));
            
            // Reserve stock using Redis
            $success = app(RedisStockService::class)->reserveStockAtomic(
                $request->product_variant_id,
                $request->quantity,
                $orderNumber
            );
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'order_number' => $orderNumber,
                        'message' => 'Đã giữ hàng thành công',
                    ],
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
```

---

## Testing Strategy

### Unit Tests

#### Stock Service Tests
```php
class StockServiceTest extends TestCase
{
    public function test_it_can_reserve_stock()
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);
        
        $this->stockService->reserveStock($variant->id, 5, 'TEST-001');
        
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'quantity' => 5,
            'status' => 'pending',
        ]);
    }
    
    public function test_it_prevents_overselling()
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 5]);
        
        $this->expectException(\Exception::class);
        $this->stockService->reserveStock($variant->id, 10, 'TEST-001');
    }
    
    public function test_it_releases_expired_reservations()
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);
        
        StockReservation::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 5,
            'expires_at' => now()->subMinutes(1),
            'status' => 'pending',
        ]);
        
        $this->artisan('inventory:release-expired');
        
        $this->assertDatabaseHas('stock_reservations', [
            'product_variant_id' => $variant->id,
            'status' => 'expired',
        ]);
    }
}
```

### Concurrency Tests

#### Simulate Concurrent Orders
```php
class ConcurrencyTest extends TestCase
{
    public function test_concurrent_orders_do_not_oversell()
    {
        $variant = ProductVariant::factory()->create(['stock_quantity' => 10]);
        
        // Simulate 20 concurrent orders for 1 item each
        $processes = [];
        
        for ($i = 0; $i < 20; $i++) {
            $processes[] = function () use ($variant, $i) {
                try {
                    $this->stockService->reserveStock($variant->id, 1, "TEST-{$i}");
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            };
        }
        
        // Execute all processes concurrently
        $results = Parallel::map($processes, fn ($process) => $process());
        
        // Count successful reservations
        $successful = count(array_filter($results, fn ($r) => $r === true));
        
        // Should only have 10 successful reservations
        $this->assertEquals(10, $successful);
        
        // Stock should be 0
        $variant->refresh();
        $this->assertEquals(0, $variant->stock_quantity);
    }
}
```

### Load Tests

#### Stock Reservation Load Test
```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '1m', target: 100 },  // Ramp up to 100 users
    { duration: '2m', target: 100 },  // Stay at 100 users
    { duration: '1m', target: 0 },    // Ramp down
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'],
    http_req_failed: ['rate<0.01'],
  },
};

export default function () {
  const payload = JSON.stringify({
    product_variant_id: 1,
    quantity: 1,
  });

  const params = {
    headers: {
      'Content-Type': 'application/json',
    },
  };

  const res = http.post('http://localhost:8000/api/v1/stock/reserve', payload, params);

  check(res, {
    'status is 200': (r) => r.status === 200,
    'response time < 500ms': (r) => r.timings.duration < 500,
  });

  sleep(1);
}
```

---

## Best Practices

### 1. Always Use Transactions
```php
DB::transaction(function () {
    // All stock operations must be in a transaction
});
```

### 2. Use Pessimistic Locking for Critical Operations
```php
$variant = ProductVariant::where('id', $id)->lockForUpdate()->first();
```

### 3. Implement Idempotency for Webhooks
```php
if ($this->isWebhookProcessed($orderNumber, $transactionId)) {
    return response()->json(['message' => 'Already processed'], 200);
}
```

### 4. Log All Stock Movements
```php
StockMovement::create([
    'product_variant_id' => $variantId,
    'quantity' => $quantity,
    'type' => 'export',
    'reference_type' => 'order',
    'reference_id' => $orderNumber,
]);
```

### 5. Set Appropriate Timeouts
```php
// Different timeout for different payment methods
$timeout = match ($paymentMethod) {
    'cod' => 30,      // 30 minutes
    'bank_transfer' => 15,  // 15 minutes
    'vnpay' => 10,    // 10 minutes
    'credit_card' => 5,     // 5 minutes
};
```

### 6. Monitor Stock Levels
```php
// Run hourly to check low stock
$schedule->command('inventory:check-low-stock')->hourly();
```

### 7. Sync Redis with Database
```php
// Sync stock from database to Redis periodically
$schedule->command('inventory:sync-redis')->everyFiveMinutes();
```

---

## Monitoring & Alerts

### Key Metrics to Monitor
- **Stock Accuracy:** Difference between Redis and Database stock
- **Reservation Rate:** Percentage of reservations that convert to orders
- **Expiration Rate:** Percentage of reservations that expire
- **Low Stock Alerts:** Number of low stock alerts per day
- **Overselling Incidents:** Number of overselling incidents (should be 0)

### Alert Thresholds
- **Stock Accuracy Error:** > 1% difference
- **Expiration Rate:** > 30%
- **Low Stock Alerts:** > 10 per day
- **Overselling Incidents:** > 0 (immediate alert)

---

## Future Enhancements

### Planned Features
- [ ] Multi-warehouse support
- [ ] Stock transfer between warehouses
- [ ] Batch/lot tracking
- [ ] Expiry date management
- [ ] Predictive inventory management
- [ ] Automated reordering
- [ ] Supplier integration
- [ ] Real-time stock dashboard

### Performance Improvements
- [ ] Implement read replicas for stock queries
- [ ] Use Redis cluster for high availability
- [ ] Implement stock caching at CDN level
- [ ] Optimize database queries with proper indexing

---

## Conclusion

Hệ thống quản lý tồn kho với high concurrency cần được thiết kế cẩn thận để tránh các vấn đề như overselling và race condition. Giải pháp được đề xuất:

1. **Chiến lược Hold-Stock:** Giữ kho ngay khi tạo đơn hàng với timeout 5-15 phút
2. **Concurrency Control:** Sử dụng Pessimistic Locking cho hệ thống vừa và nhỏ, Redis Distributed Lock cho hệ thống lớn
3. **Stock Movement Logging:** Ghi log tất cả các movement để audit
4. **Low Stock Alerts:** Cảnh báo khi tồn kho thấp
5. **Idempotency:** Đảm bảo webhook không được xử lý trùng lặp

Giải pháp này đảm bảo:
- Không có overselling
- Khách hàng không bị mất tiền oan
- Hệ thống có thể xử lý high concurrency
- Có đầy đủ audit trail
- Có cảnh báo khi tồn kho thấp
