---
description: Logic
---

# VNM E-Commerce — Quy Tắc & Quy Trình Phát Triển

> Tài liệu tổng hợp toàn bộ tiêu chuẩn code và quy trình phát triển cho hệ thống **Vinamilk Core E-Commerce**.
> Stack: **Next.js (App Router) + TypeScript + Tailwind CSS + Framer Motion** (FE) · **Laravel** (BE) · **MySQL** (DB)

---

## MỤC LỤC

1. [Quy tắc Database](#1-quy-tắc-database)
2. [Quy tắc Backend — Laravel](#2-quy-tắc-backend--laravel)
3. [Quy tắc Frontend — Next.js](#3-quy-tắc-frontend--nextjs)
4. [Workflow phát triển tính năng](#4-workflow-phát-triển-tính-năng)

---

## 1. Quy Tắc Database

### 1.1 Nguyên tắc Migration

- **100% thay đổi schema** (thêm/sửa/xoá bảng, cột, index) phải thông qua Laravel Migration (`php artisan make:migration`).
- Tuyệt đối **không can thiệp trực tiếp** vào database bằng tay.
- Mỗi migration phải có đầy đủ method `up()` và `down()`.

### 1.2 Naming Conventions

| Đối tượng | Quy tắc | Ví dụ |
|---|---|---|
| Tên bảng | Viết thường, số nhiều, `snake_case` | `care_products`, `order_items` |
| Tên cột | Viết thường, `snake_case` | `base_price`, `discount_percentage`, `created_at` |
| Khoá ngoại | `[tên_bảng_số_ít]_id` | `user_id`, `product_variant_id` |
| Index | Đặt tên rõ ràng theo cột | `products_slug_index` |

### 1.3 Performance & Constraints

- Luôn đánh **index** (`$table->index()`) cho các cột thường xuyên được query: foreign keys, `status`, `slug`.
- Sử dụng **Soft Deletes** (`$table->softDeletes()`) cho dữ liệu quan trọng (Users, Orders, Products) — không xoá cứng.

### 1.4 Tính Toàn Vẹn Dữ Liệu

- Mọi thao tác **ghi đồng thời vào nhiều bảng** (checkout đơn hàng, lưu giao dịch thanh toán) **bắt buộc** đặt trong `DB::transaction()`.

```php
// ✅ Đúng
DB::transaction(function () use ($data) {
    $order = Order::create($data['order']);
    $order->items()->createMany($data['items']);
    // ...
});

// ❌ Sai — không bảo vệ tính toàn vẹn
$order = Order::create($data['order']);
$order->items()->createMany($data['items']);
```

---

## 2. Quy Tắc Backend — Laravel

### 2.1 Kiến Trúc Service-Repository / MVC Nâng Cao

Phân tầng trách nhiệm **rõ ràng, tuyệt đối**:

| Tầng | Nhiệm vụ | Không được làm |
|---|---|---|
| **Controller** | Nhận HTTP Request, gọi FormRequest validate, truyền xuống Service, trả Response | Viết business logic |
| **Service** (`app/Services/`) | Toàn bộ logic tính toán, kiểm tra điều kiện, gọi API bên thứ 3 | Trực tiếp nhận request từ Controller mà không qua validate |
| **Model (Eloquent)** | Định nghĩa relationships, accessors, mutators, scopes | Chứa business logic |

```php
// ✅ Đúng — Controller chỉ điều phối
class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, OrderService $orderService)
    {
        $order = $orderService->createOrder($request->validated());
        return response()->json(['status' => 'success', 'data' => $order]);
    }
}
```

### 2.2 RESTful API & Response Format

- Route khai báo tại `routes/api.php` với prefix version: `/api/v1/`
- **Toàn bộ phản hồi JSON** phải tuân theo cấu trúc chuẩn hoá:

```json
{
  "status": "success | error",
  "message": "Mô tả trạng thái",
  "data": { },
  "errors": { }
}
```

### 2.3 Tối Ưu Eloquent ORM

- **Bắt buộc** xử lý N+1 Query bằng Eager Loading khi query dữ liệu có quan hệ.

```php
// ✅ Đúng — Eager Loading
$orders = Order::with(['items.product', 'user'])->get();

// ❌ Sai — N+1 Query
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name; // Query mới cho mỗi dòng
}
```

### 2.4 Bảo Mật & Validation

- Mọi API endpoint nhận dữ liệu (POST/PUT) **phải** validate thông qua **Laravel Form Requests**.

```php
// Tạo FormRequest
// php artisan make:request StoreOrderRequest

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity'           => 'required|integer|min:1',
        ];
    }
}
```

---

## 3. Quy Tắc Frontend — Next.js

### 3.1 Kiến Trúc App Router

- **Mặc định** dùng **React Server Components** để tối ưu SEO và tốc độ tải trang.
- Chỉ thêm directive `"use client"` khi component **thực sự cần**:
  - Dùng hooks (`useState`, `useEffect`, `useContext`)
  - Tương tác browser (events, `window` object)
  - Thư viện animation (Framer Motion)
- Cấu trúc theo **domain-driven**: gom component, hook, type theo feature.

```
src/
├── app/                      # Next.js App Router pages
├── components/
│   ├── care/                 # Feature: Care products
│   ├── catalog/              # Feature: Product catalog
│   └── shared/               # Shared/reusable components
├── lib/
│   └── api.ts                # Tất cả API client functions
└── types/
    ├── care.ts               # Types cho Care feature
    └── catalog.ts            # Types cho Catalog feature
```

### 3.2 TypeScript — Strict Typing

- **100%** props, state, DTO, API Response phải có Interface/Type tường minh tại `src/types/`.
- **Tuyệt đối không** dùng type `any`.

```typescript
// ✅ Đúng — src/types/care.ts
export interface CareProduct {
  id: number;
  name: string;
  base_price: number;
  discount_percentage: number;
  variants: CareProductVariant[];
}

export interface CareProductVariant {
  id: number;
  sku: string;
  unit_price: number;
}

// ❌ Sai
const product: any = await fetchProduct(id);
```

### 3.3 UI/UX & Nhận Diện Thương Hiệu Vinamilk

#### Màu sắc

| Vai trò | Màu | Hex |
|---|---|---|
| Text / Border chính | Navy Blue | `#001c9a` hoặc `#0213b0` |
| Nền mảng lớn | Cream White | `#fffff1` hoặc `#fefef0` |

#### Typography

- Font `sans-serif` mặc định cho body text.
- Class `font-serif italic` cho heading, thông điệp sang trọng.

#### Scrollbar

- **Bắt buộc** dùng class `navy-scrollbar` cho mọi thẻ có `overflow-y-auto`.

```html
<!-- ✅ Đúng -->
<div class="overflow-y-auto navy-scrollbar max-h-96">...</div>

<!-- ❌ Sai -->
<div class="overflow-y-auto max-h-96">...</div>
```

#### Overlay / Modal

- Lớp phủ màn hình mờ **phải** dùng `bg-black/80`.

```html
<div class="fixed inset-0 bg-black/80 flex items-center justify-center">
  <!-- Modal content -->
</div>
```

### 3.4 Tính Toán & Logic Giá Tiền

- **Không** tự viết lại công thức tính giá — luôn dùng utils tập trung:

```typescript
// ✅ Đúng — dùng utils
import { variantUnitPrice, variantBasePrice, formatVnd } from '@/lib/utils/price';

const displayPrice = formatVnd(variantUnitPrice(variant));

// ❌ Sai — tự tính inline
const displayPrice = `${(variant.base_price * (1 - variant.discount / 100)).toLocaleString()}đ`;
```

### 3.5 API Client

- Mọi hàm fetch tới Backend **phải** được module hoá trong `src/lib/api.ts`:

```typescript
// src/lib/api.ts
export const careApi = {
  getProducts: async (): Promise<CareProduct[]> => {
    const res = await fetch('/api/v1/care/products');
    return res.json();
  },
  subscribe: async (payload: SubscribePayload): Promise<Order> => {
    const res = await fetch('/api/v1/care/subscribe', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    return res.json();
  },
};
```

### 3.6 Xử Lý Trạng Thái Loading & Error

- **Bắt buộc** xử lý cả 3 trạng thái: Loading, Error, Empty — không để màn hình trắng.

```tsx
// ✅ Đúng
if (isLoading) return <ProductSkeleton />;
if (error)     return <ErrorMessage message={error.message} />;
if (!data?.length) return <EmptyState />;

return <ProductList products={data} />;
```

---

## 4. Workflow Phát Triển Tính Năng

Khi implement tính năng mới, **bắt buộc** tuân thủ thứ tự 4 phase từ dưới lên:

---

### Phase 1 — Database & Backend

> **Làm BE trước, FE sau. Không đảo ngược thứ tự.**

**Bước 1 — Phân tích data**
Đọc yêu cầu → Phác thảo cấu trúc bảng cần thiết, xác định quan hệ.

**Bước 2 — Tạo DB Schema**
```bash
php artisan make:migration create_care_subscriptions_table
```
Viết `up()` / `down()` → Chạy migration → Cập nhật Model (Fillable, Casts, Relations).

**Bước 3 — Validation**
```bash
php artisan make:request StoreCareSubscriptionRequest
```
Định nghĩa rules để chặn/lọc input không hợp lệ trước khi vào Service.

**Bước 4 — Business Logic**
Tạo/cập nhật class tại `app/Services/`. Viết logic tính toán, Eloquent queries. Bọc `DB::transaction()` nếu ghi nhiều bảng.

**Bước 5 — Controller & Route**
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::post('/care/subscribe', [CareController::class, 'subscribe']);
});
```
Controller gọi Service → trả JSON chuẩn.

---

### Phase 2 — Kết Nối Dữ Liệu (FE Data Setup)

**Bước 6 — Định nghĩa Type**
Đối chiếu JSON response vừa thiết kế → tạo Interface tương ứng tại `src/types/[domain].ts`.

**Bước 7 — Khai báo API Client**
Thêm function fetch (GET/POST) vào `src/lib/api.ts`.

**Bước 8 — State Management**
- State dùng chung nhiều nơi → cập nhật Context Provider (VD: `CareCartContext`).
- State cục bộ của component → dùng `useState`.

---

### Phase 3 — Frontend UI & UX

**Bước 9 — Dựng Layout**
Tạo file component mới tại `src/components/[domain]/`. Phân tách component nhỏ, logic phức tạp vào custom hooks.

**Bước 10 — Áp dụng Design System**
- Tailwind với màu Navy Blue / Cream White theo branding.
- Thêm `navy-scrollbar` nếu có thanh cuộn.
- Overlay dùng `bg-black/80`.

**Bước 11 — Responsive & Animation**
- Code **mobile-first** — ưu tiên màn hình nhỏ trước, dùng breakpoint `md:`, `lg:` cho màn hình lớn.
- Framer Motion cho các transition, modal, dropdown cần animation mượt.

**Bước 12 — Gắn Data & Handle States**
Map dữ liệu từ API vào UI. **Bắt buộc** xử lý:

| Trạng thái | Yêu cầu |
|---|---|
| `loading` | Skeleton hoặc Spinner |
| `error` | Thông báo lỗi rõ ràng |
| `empty` | UI trống có hướng dẫn |
| `success` | Hiển thị data đầy đủ |

---

### Phase 4 — QA & Kiểm Tra

**Bước 13 — End-to-End Test**
Chạy thử toàn bộ luồng người dùng trên Terminal/Browser (VD: chọn sản phẩm → thêm vào giỏ → checkout).

**Bước 14 — Checklist kiểm tra**

- [ ] Dữ liệu tạo từ FE đã lưu đúng xuống Database
- [ ] Naming conventions (bảng, cột, FK) đúng chuẩn
- [ ] Không có N+1 Query (kiểm tra Telescope/Debugbar)
- [ ] Tất cả API endpoint có FormRequest validation
- [ ] Không có type `any` trong TypeScript
- [ ] Màu Navy Blue / Cream White đúng branding
- [ ] `navy-scrollbar` đã thêm đủ nơi cần
- [ ] Loading + Error state đã xử lý đầy đủ
- [ ] Transaction bọc đủ các thao tác ghi nhiều bảng

---

*Tài liệu này có giá trị bắt buộc với mọi agent và developer làm việc trên dự án Vinamilk Core E-Commerce.*
