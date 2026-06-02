# Vinamilk Core Ecommerce

<div align="center">
Nền tảng thương mại điện tử full-stack xây dựng trên Laravel 11 và Next.js 15, phục vụ nghiệp vụ bán lẻ trực tuyến của Vinamilk. Hệ thống bao gồm toàn bộ vòng đời đơn hàng từ duyệt sản phẩm đến hoàn tất thanh toán, kèm theo admin panel và promotion engine độc lập.

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-15.x-black.svg)](https://nextjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue.svg)](https://www.typescriptlang.org)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-38B2AC.svg)](https://tailwindcss.com)

</div>

## Mục lục

- [Tổng quan hệ thống](#tổng-quan-hệ-thống)
- [Công nghệ sử dụng](#công-nghệ-sử-dụng)
- [Kiến trúc](#kiến-trúc)
- [Tính năng nghiệp vụ](#tính-năng-nghiệp-vụ)
- [Cài đặt môi trường phát triển](#cài-đặt-môi-trường-phát-triển)
- [Biến môi trường](#biến-môi-trường)
- [Cấu trúc thư mục](#cấu-trúc-thư-mục)
- [API Reference](#api-reference)
- [Admin Panel](#admin-panel)
- [Quy trình đóng góp](#quy-trình-đóng-góp)
- [Giấy phép](#giấy-phép)

---

## Tổng quan hệ thống

Dự án được tổ chức theo mô hình monorepo với hai service độc lập:

- **vinamilk-api** — Laravel REST API, xử lý toàn bộ business logic, quản lý dữ liệu, tích hợp cổng thanh toán và vận hành admin panel qua Filament.
- **vinamilk-fe** — Next.js frontend, phục vụ giao diện người dùng cuối với server-side rendering và client-side navigation.

Hai service giao tiếp qua HTTP/JSON, xác thực bằng Laravel Sanctum token. Redis được dùng cho caching, session và queue. Toàn bộ background job chạy qua Laravel Horizon.

### Điểm kiến trúc đáng chú ý

**Promotion Engine** là module phức tạp nhất trong hệ thống. Logic khuyến mãi được lưu hoàn toàn trong database theo dạng rule — code chỉ đóng vai trò executor. Kiến trúc này cho phép marketing team tạo và chỉnh sửa campaign trực tiếp trên admin panel mà không cần triển khai lại code. Chi tiết thiết kế xem tại `promotion_engine_design.md`.

**Inventory reservation** sử dụng cơ chế giữ hàng dựa trên transaction. Khi người dùng tiến hành checkout, số lượng tương ứng được lock trước khi thanh toán xác nhận, tránh tình trạng overselling ở mức độ race condition.

---

## Công nghệ sử dụng

### Backend — `vinamilk-api`

| Thành phần | Công nghệ |
|---|---|
| Framework | Laravel 11.x |
| Ngôn ngữ | PHP 8.2+ |
| Database | MySQL 8.0+ |
| Cache / Queue | Redis + Laravel Horizon |
| Admin Panel | Filament PHP |
| Authentication | Laravel Sanctum |
| Authorization | Spatie Laravel Permission |
| File Storage | S3-compatible (AWS S3 / MinIO) |
| API Documentation | OpenAPI / Swagger |

### Frontend — `vinamilk-fe`

| Thành phần | Công nghệ |
|---|---|
| Framework | Next.js 15.x (App Router) |
| Ngôn ngữ | TypeScript 5.x |
| Styling | TailwindCSS 4.x |
| State Management | Zustand |
| Form Handling | React Hook Form + Zod |
| Maps | Leaflet + React Leaflet |
| HTTP Client | Axios |
| UI Components | shadcn/ui (custom wrapper) |

### Tích hợp bên thứ ba

| Nhóm | Dịch vụ |
|---|---|
| Thanh toán | MoMo, VNPay, Stripe, PayPal |
| Vận chuyển | GHN (Giao Hàng Nhanh) |
| AI | Google Gemini API cho chatbox|
| Email | SMTP / SendGrid |
| SMS | Twilio / Viettel SMS |

---

## Kiến trúc

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   External      │
│   (Next.js)     │◄──►│   (Laravel)     │◄──►│   Services      │
│                 │    │                 │    │                 │
│ App Router      │    │ REST API v1     │    │ Payment GW      │
│ TailwindCSS     │    │ Filament Admin  │    │ GHN Shipping    │
│ Zustand         │    │ Redis / Horizon │    │ Gemini AI       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                               │
                               ▼
                        ┌─────────────────┐
                        │   MySQL         │
                        │                 │
                        │ users           │
                        │ products        │
                        │ orders          │
                        │ inventory       │
                        │ marketing_rules │
                        └─────────────────┘
```

### Các nhóm bảng dữ liệu chính

- **users** — tài khoản khách hàng, admin, phân quyền theo role
- **products** — sản phẩm, biến thể (variant), danh mục
- **orders** — đơn hàng, order items, lịch sử trạng thái, thanh toán
- **inventory** — tồn kho thực tế, lịch sử di chuyển, bản ghi giữ hàng (reservation)
- **marketing_rules / conditions / rewards** — rule-based promotion engine
- **loyalty** — điểm tích lũy, lịch sử giao dịch điểm, đổi quà
- **chat** — tin nhắn chatbot, knowledge base, cấu hình AI
- **content** — blog, banner, landing page

---

## Tính năng nghiệp vụ

### Phía người dùng cuối

**Catalog & Tìm kiếm**
Duyệt sản phẩm theo danh mục, lọc theo thuộc tính, tìm kiếm toàn văn. Trang chi tiết hiển thị đầy đủ biến thể (dung tích, hương vị) kèm trạng thái tồn kho thời gian thực.

**Giỏ hàng & Checkout**
Giỏ hàng persistent (lưu theo session hoặc tài khoản), áp dụng mã khuyến mãi, chọn địa chỉ giao hàng với tích hợp GHN để tính phí ship. Checkout nhiều bước với xác nhận đơn hàng trước khi thanh toán.

**Thanh toán**
Hỗ trợ COD, MoMo, VNPay (đặc thù thị trường Việt Nam), Stripe và PayPal (quốc tế). Mỗi cổng được xử lý qua service riêng biệt ở backend, callback xử lý bất đồng bộ qua queue.

**Tài khoản & Lịch sử**
Hồ sơ cá nhân, lịch sử đơn hàng với theo dõi trạng thái, danh sách yêu thích, điểm tích lũy và lịch sử đổi quà.

**Loyalty Program**
Hệ thống điểm thưởng đa tầng. Điểm được cấp sau khi đơn hàng hoàn tất (xử lý qua job queue), có thể đổi thành voucher giảm giá hoặc sản phẩm quà tặng. Cashback points được ghi nhận theo quy tắc marketing rule.

**Vinamilk Care**
Module đăng ký giao sữa định kỳ (subscription), quản lý lịch giao hàng và tạm dừng/huỷ gói.

**Chatbot AI**
Tích hợp Google Gemini API với knowledge base riêng của Vinamilk (sản phẩm, chính sách, FAQ). Chatbot có thể tư vấn sản phẩm và hỗ trợ tra cứu đơn hàng trong phạm vi được cấu hình.

### Phía quản trị (Admin Panel)

**Dashboard**
Tổng quan doanh thu, số đơn theo trạng thái, cảnh báo tồn kho thấp, hoạt động gần đây.

**Quản lý sản phẩm**
CRUD đầy đủ cho sản phẩm và biến thể, quản lý danh mục theo cây phân cấp, upload ảnh lên S3.

**Quản lý đơn hàng**
Xem chi tiết đơn, cập nhật trạng thái thủ công, xử lý hoàn tiền, in phiếu giao hàng.

**Quản lý tồn kho**
Theo dõi tồn kho theo kho/variant, nhập kho, điều chỉnh, xem lịch sử di chuyển. Cảnh báo tự động khi tồn kho xuống dưới ngưỡng cấu hình.

**Quản lý khách hàng**
Xem hồ sơ, lịch sử đơn hàng, điều chỉnh điểm loyalty thủ công, xem log chatbot.

**Marketing**
Tạo và quản lý promotion rule trực tiếp trên UI (không cần deploy code). Quản lý voucher, banner, bài viết blog.

**Phân quyền**
RBAC dựa trên Spatie Permission. Admin có thể tạo role mới và gán quyền chi tiết đến từng resource/action trong Filament.

**Audit Log**
Ghi lại toàn bộ hành động của admin (tạo/sửa/xoá record, đăng nhập) để phục vụ kiểm tra nội bộ.

### Kỹ thuật

- RESTful API versioned (`/api/v1/`)
- Redis cache với TTL cấu hình theo từng loại dữ liệu
- Job queue cho các tác vụ nặng: gửi email/SMS, cập nhật điểm loyalty, sync tồn kho sau đơn hàng
- S3-compatible storage cho ảnh sản phẩm và tài liệu
- WebSocket (Laravel Echo) cho thông báo real-time trên admin panel

---

## Cài đặt môi trường phát triển

### Yêu cầu

- PHP 8.2+
- Composer
- Node.js 18+
- npm hoặc yarn
- MySQL 8.0+
- Redis

### Backend

```bash
cd vinamilk-api

composer install

cp .env.example .env

php artisan key:generate

# Cấu hình DB_DATABASE, DB_USERNAME, DB_PASSWORD trong .env

php artisan migrate

php artisan db:seed   # tuỳ chọn — tạo dữ liệu mẫu

php artisan storage:link

php artisan serve

hoặc

$env:PHP_CLI_SERVER_WORKERS=8; php artisan serve
```

### Frontend

```bash
cd vinamilk-fe

npm install

cp .env.example .env.local

# Đặt NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1 trong .env.local

# Môi trường dev
npm run dev
# Môi trường production tăng tốc độ xử lý
npm run build
npm run start
```

### Truy cập Admin Panel

```
URL:      http://localhost:8000/admin
Email:    admin@vinamilk.com
Password: password   (chỉ sau khi chạy db:seed)
```

---

## Biến môi trường

### Backend — `vinamilk-api/.env`

```env
APP_NAME="Vinamilk Ecommerce"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vinamilk_ecommerce
DB_USERNAME=
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# MoMo
MOMO_PARTNER_CODE=
MOMO_ACCESS_KEY=
MOMO_SECRET_KEY=
MOMO_ENDPOINT=https://test-payment.momo.vn

# VNPay
VNPAY_TMN_CODE=
VNPAY_HASH_SECRET=
VNPAY_TEST_MODE=true

# Stripe
STRIPE_KEY=
STRIPE_SECRET=

# PayPal
PAYPAL_CLIENT_ID=
PAYPAL_SECRET=

# GHN
GHN_API_URL=https://dev-online-gateway.ghn.vn
GHN_TOKEN=
GHN_SHOP_ID=

# AI
GEMINI_API_KEY=

# S3 Storage
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=vinamilk-storage
AWS_URL=
```

### Frontend — `vinamilk-fe/.env.local`

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_URL=http://localhost:3000
NEXT_PUBLIC_GTM_ID=
```

---

## Cấu trúc thư mục

```
vinamilk-core-ecommerce/
├── vinamilk-api/                    # Laravel backend
│   ├── app/
│   │   ├── Console/                 # Artisan commands
│   │   ├── Filament/                # Admin panel resources (Filament)
│   │   ├── Http/
│   │   │   ├── Controllers/         # API controllers
│   │   │   └── Middleware/
│   │   ├── Models/                  # Eloquent models
│   │   ├── Services/                # Business logic layer
│   │   └── Traits/
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   ├── api.php
│   │   └── web.php
│   └── tests/
│
├── vinamilk-fe/                     # Next.js frontend
│   └── src/
│       ├── app/                     # App Router — pages và layouts
│       ├── components/              # React components
│       ├── hooks/                   # Custom hooks
│       ├── lib/                     # Utility functions, API clients
│       ├── stores/                  # Zustand stores
│       └── types/                   # TypeScript type definitions
│
├── promotion_engine_design.md       # Thiết kế chi tiết promotion engine
└── README.md
```

---

## API Reference

### Base URL

```
Local:       http://localhost:8000/api/v1
Production:  https://api.vinamilk.com/api/v1
```

Tài liệu đầy đủ có tại `/api/documentation` (Swagger UI, chỉ bật khi `APP_DEBUG=true`).

### Xác thực

Hầu hết endpoint yêu cầu Bearer token từ Laravel Sanctum.

```bash
# Đăng nhập
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

# Gắn token vào mọi request tiếp theo
Authorization: Bearer {token}
```

### Endpoint chính

**Products**

```
GET  /products              Danh sách sản phẩm (hỗ trợ filter, sort, pagination)
GET  /products/{id}         Chi tiết sản phẩm
GET  /products/{id}/variants  Biến thể của sản phẩm
```

**Cart**

```
GET    /cart                Lấy giỏ hàng hiện tại
POST   /cart/add            Thêm item vào giỏ
PUT    /cart/{id}           Cập nhật số lượng
DELETE /cart/{id}           Xoá item khỏi giỏ
POST   /cart/evaluate       Tính toán khuyến mãi cho giỏ hàng hiện tại
```

**Orders**

```
POST /orders/checkout       Tạo đơn hàng từ giỏ hàng
GET  /orders                Danh sách đơn của người dùng hiện tại
GET  /orders/{id}           Chi tiết đơn hàng
```

**User**

```
GET /user/profile           Thông tin hồ sơ
PUT /user/profile           Cập nhật hồ sơ
GET /user/orders            Lịch sử đơn hàng
GET /user/loyalty           Điểm và lịch sử loyalty
```

---

## Admin Panel

Admin panel xây dựng bằng Filament PHP, truy cập tại `/admin`.

Yêu cầu tài khoản được gán role `admin` hoặc `super_admin`. Quyền truy cập từng resource được kiểm soát độc lập qua Spatie Permission.

### Nhóm chức năng

| Nhóm | Chức năng |
|---|---|
| Dashboard | Chỉ số thời gian thực và feed hoạt động |  
| Đơn hàng | Xử lý trạng thái, giao dịch |
| Kho hàng | Sản phẩm, biến thể, tồn kho và cảnh báo |
| Chăm sóc khách hàng | Tài khoản người dùng, loyalty, hỗ trợ |
| Marketing | Promotion rules, voucher, banner, blog |
| Tài khoản | Quản lý admin user, role, quyền hạn |
| Hệ thống | Cấu hình thanh toán, vận chuyển, chatbot, audit log for all action from user |

---

## Trang & Tính năng

### Trang dành cho Khách hàng
#### 1. Trang chủ
- **Page**: /
- **Tính năng**: Hero banner, secondary banner, line certificate, section mời bạn sắm sửa
- **Screenshot**: <img width="1896" height="1069" alt="image" src="https://github.com/user-attachments/assets/0e61e3da-0b3e-4c93-bf7c-1a251ff986b5" /> <img width="1898" height="861" alt="image" src="https://github.com/user-attachments/assets/a5b312ed-3832-495e-a997-a3ad20148211" /> <img width="1897" height="861" alt="image" src="https://github.com/user-attachments/assets/823cd6f9-bd7a-43dc-87e4-2157af77a832" /> <img width="1902" height="867" alt="image" src="https://github.com/user-attachments/assets/a877769a-9e39-4f66-b4ea-3bce26f0652a" />

#### 2. Trang danh sách sản phẩm
- **Page**: /collections/all-products
- **Tính năng**: Lọc sản phẩm theo danh mục, dòng sản phẩm, thương hiệu, hương vị, thể tích, mức đường, nhu cầu dinh dưỡng,...
- **Screenshot**: <img width="1891" height="1073" alt="image" src="https://github.com/user-attachments/assets/d56042e4-3a02-467d-863e-7e57532a44a2" /> <img width="1899" height="1073" alt="image" src="https://github.com/user-attachments/assets/a80a098f-4e7a-412c-b3c5-2cb6b1b91fc7" />

#### 3. Trang chi tiết sản phẩm
- **Page**: /products/[id]
- **Tính năng**:
  - **Thông tin chi tiết sản phẩm**
  - Tên sản phẩm, mô tả ngắn, thương hiệu, dòng sản phẩm
  - Trạng thái còn hàng/hết hàng

- **Gallery sản phẩm**
  - Slider ảnh sản phẩm
  - Zoom ảnh khi hover/click

- **Sản phẩm liên quan**
  - Sản phẩm cùng danh mục
  - Sản phẩm cùng dòng sản phẩm
  - Sản phẩm cùng thương hiệu

- **Danh sách biến thể sản phẩm**
  - Danh sách volume của sản phẩm
  - Danh sách packing type
  - Hiển thị giá gốc
  - Hiển thị giá sau khuyến mãi
  - Hiển thị % giảm giá

- **Sidebar thành phần dinh dưỡng**
  - Bảng thông tin dinh dưỡng
  - Thành phần nguyên liệu
  - Hàm lượng dinh dưỡng chi tiết

- **Sidebar hướng dẫn sử dụng**
  - Hướng dẫn pha/chế biến
  - Điều kiện bảo quản
  - Độ tuổi sử dụng phù hợp

- **Box chứng chỉ**
  - Hiển thị các chứng nhận/chứng chỉ sản phẩm
  - Logo chứng chỉ kèm tooltip mô tả

- **Section sticky "Có gì đặc sắc"**
  - Sticky section khi scroll
  - Highlight các điểm nổi bật của sản phẩm

- **Line highlight product**
  - Hiển thị danh sách icon `.svg`
  - Các ưu điểm nổi bật của sản phẩm

- **Ảnh description**
  - Banner/ảnh mô tả chi tiết sản phẩm
  - Nội dung storytelling bằng hình ảnh

- **Bảng so sánh sản phẩm**
  - So sánh các loại sữa khác với sản phẩm hiện tại
  - So sánh dinh dưỡng, thành phần, giá, công dụng

- **Screenshot**: <img width="1900" height="1079" alt="image" src="https://github.com/user-attachments/assets/2d304b38-0058-4ba7-a437-dddd1871b2d2" /> <img width="1894" height="1079" alt="image" src="https://github.com/user-attachments/assets/877fbc72-63a8-40b6-a159-1b216888268f" /> <img width="1902" height="1079" alt="image" src="https://github.com/user-attachments/assets/acdb350b-daa6-4608-92a1-dbbc4b2459d5" /> <img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/4e5789ff-5da4-4714-b3df-dbbe23b7788c" /> <img width="1898" height="1079" alt="image" src="https://github.com/user-attachments/assets/dcecc4b8-bb1a-4670-9146-0e6e6f54f141" /> <img width="1901" height="1079" alt="image" src="https://github.com/user-attachments/assets/8ded7a38-76ef-4712-862b-50cc229bca28" /> <img width="1897" height="1079" alt="image" src="https://github.com/user-attachments/assets/e3d10f08-dcba-4131-a4f5-5bb0e89604b7" />

#### 4. Trang Vinamilk Care
- **Page**: /care
- **Tính năng**:
  - **Chương trình gói đăng ký giao hàng định kỳ**
  - Đăng ký giao hàng sản phẩm theo tháng
  - Tự động tạo lịch giao hàng 1 lần/tháng
  - Quản lý trạng thái gói đăng ký

- **Cart riêng cho gói đăng ký**
  - Subscription cart tách biệt với cart thông thường
  - Quản lý sản phẩm chỉ dành cho gói định kỳ
  - Tính toán tổng tiền riêng cho subscription cart

- **Sidebar chi tiết sản phẩm**
  - Hiển thị thông tin sản phẩm trong gói đăng ký
  - Hiển thị số lượng, giá, ưu đãi
  - Tóm tắt quyền lợi của gói

- **Modal step điều chỉnh gói**
  - Điều chỉnh số lượng sản phẩm trong giỏ hàng
  - Thêm/xóa item quà tặng
  - Thêm/xóa item thiệp
  - Kiểm tra điều kiện chương trình khuyến mãi
  - Tự động cập nhật quà tặng khi đơn hàng đủ điều kiện

- **Chọn số lần giao hàng**
  - Chọn số tháng giao hàng
  - Mỗi tháng giao 1 lần
  - Hiển thị lịch giao hàng dự kiến
  - Tính tổng chi phí theo số lần giao hàng
- **Screenshot**: <img width="1892" height="1079" alt="image" src="https://github.com/user-attachments/assets/7acf3db2-7cc3-4c85-8767-7972a420887d" /> <img width="1898" height="1079" alt="image" src="https://github.com/user-attachments/assets/8c1ce9cf-505a-404d-b0a9-ab7f0ebcf39e" /> <img width="1902" height="1079" alt="image" src="https://github.com/user-attachments/assets/08590030-b93e-4a2c-961b-794aaf1c58a8" /> <img width="1900" height="1077" alt="image" src="https://github.com/user-attachments/assets/6da33074-d132-4820-b33f-ac90f4cb1abc" /> <img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/23fd0873-f15a-46d3-9f63-aa34476a19d7" /> <img width="1919" height="866" alt="image" src="https://github.com/user-attachments/assets/6c11307f-0b50-48ff-9fca-6d4af397f434" />

- **Page**: /care/thanh-toan
- **Tính năng**:
  - **Trang checkout riêng cho gói Care**
  - Checkout tách biệt với checkout thông thường
  - Chỉ xử lý đơn hàng subscription/care

- **Thông tin tài khoản**
  - Hiển thị thông tin account của user
  - Kiểm tra trạng thái đăng nhập
  - Hiển thị thông tin liên hệ

- **Danh sách địa chỉ của user**
  - Hiển thị list address đã lưu
  - Chọn địa chỉ giao hàng
  - Thêm/sửa/xóa địa chỉ nhận hàng

- **Ngày giao hàng**
  - User chọn ngày bắt đầu giao hàng
  - Hệ thống tự động setup lịch giao hàng hàng tháng
  - Mỗi tháng giao 1 lần theo ngày đã chọn
  - Hiển thị lịch giao dự kiến

- **Thông tin VAT**
  - Chọn xuất VAT cá nhân hoặc công ty
  - Form nhập thông tin hóa đơn VAT
  - Tên công ty
  - Mã số thuế
  - Địa chỉ công ty
  - Email nhận hóa đơn

- **Phương thức thanh toán**
  - Hỗ trợ online payment
  - Không hỗ trợ COD cho gói Care
  - Lưu trạng thái thanh toán

- **Chỉnh sửa quà tặng và thiệp**
  - Thêm/chỉnh sửa item gift
  - Thêm/chỉnh sửa thiệp
  - Cập nhật quà tặng theo điều kiện khuyến mãi
  - Preview nội dung thiệp

- **Thông tin đơn hàng**
  - Danh sách sản phẩm trong đơn hàng
  - Hiển thị số lượng sản phẩm
  - Hiển thị giá tiền từng sản phẩm
  - Hiển thị giảm giá
  - Tính tổng tiền cuối cùng
- **Screenshot**: <img width="1900" height="1079" alt="image" src="https://github.com/user-attachments/assets/5a723e44-7678-400f-8366-cf607d287f25" />
 <img width="1902" height="1079" alt="image" src="https://github.com/user-attachments/assets/d5addd24-0de8-49bb-9a75-4b047adeef00" /> <img width="1896" height="858" alt="image" src="https://github.com/user-attachments/assets/6a832605-852e-43b4-a6f4-60432f51ea59" /> <img width="1895" height="863" alt="image" src="https://github.com/user-attachments/assets/d33557fa-8999-48f6-9789-15451133471a" /> <img width="1919" height="862" alt="image" src="https://github.com/user-attachments/assets/9a07c3fb-ff7b-4528-918f-f387e1eec46e" /> <img width="1895" height="862" alt="image" src="https://github.com/user-attachments/assets/49c8235b-e525-4573-8713-02f8ba0f1c98" /> <img width="1897" height="871" alt="image" src="https://github.com/user-attachments/assets/42e506ed-cd69-4068-9317-92f1f7947c2d" />

#### 5. Trang Vinamilk Rewards
- **Page**: /vinamilk-rewards
- **Tính năng**: Trang này sẽ có hiển thị điểm point của user thông qua các giao dịch mua hàng để tính điểm reward point. Banner, list card voucher, list card gift được quy đổi từ điểm của user để nhận voucher hoặc gift, sidebar history reward_redemptions(hiển thị lịch sử các giao dịch hoặc quy đổi điểm thưởng lấy voucher/gift của user)
- **Screenshot**: <img width="1895" height="864" alt="image" src="https://github.com/user-attachments/assets/eecc346f-07fa-48f1-bb01-ae23ea0173e0" /> <img width="1901" height="864" alt="image" src="https://github.com/user-attachments/assets/98b196e9-0d8a-4e3a-ba0f-06ff93eb9e09" /> <img width="835" height="789" alt="image" src="https://github.com/user-attachments/assets/b863d9f2-30da-4359-be7e-776f769bc521" /> <img width="835" height="826" alt="image" src="https://github.com/user-attachments/assets/0c16d3e4-95ed-4800-9073-d35ec08b6c4a" /> <img width="1919" height="867" alt="image" src="https://github.com/user-attachments/assets/36d07cae-c0c6-48c3-b3cc-db54b7777e66" /> <img width="1919" height="862" alt="image" src="https://github.com/user-attachments/assets/ff0b947f-d085-4afc-b985-51f911e23696" /> <img width="1902" height="860" alt="image" src="https://github.com/user-attachments/assets/d854fea0-90e5-42ef-adaf-df908235d39a" />

#### 6. Trang danh sách cửa hàng
- **Page**: /store-list
- **Tính năng**: Page này sẽ trực quan hóa danh sách cửa hàng dựa vào tọa độ thông tin cửa hàng được quản trị bên phía admin, sử dụng leaflet open street map để làm việc này. Có filter theo Tỉnh/Thành của Việt Nam, Quận/Huyện/ Phường/Xã. Khi thao tác click chọn các địa chỉ có trong list thì hệ thống sẽ follow theo tự đổng trỏ tới location của store đó, zoom in ra. Muốn xem chi tiết vị trí thì click qua gg map.
- **Screenshot**: <img width="1899" height="1079" alt="image" src="https://github.com/user-attachments/assets/095111fe-7862-4f36-b43b-5edf8b9e8308" /> <img width="1903" height="1079" alt="image" src="https://github.com/user-attachments/assets/daf16a6a-09f9-4405-83ae-2aeb3ae80d64" /> <img width="1903" height="1079" alt="image" src="https://github.com/user-attachments/assets/5d001aea-5967-4eb2-978a-ea11ac72b12d" />


#### 7. Trang danh hồ sơ khách hàng
- **Page**: /account/profile, /account/address, /account/vouchers, /account/orders, /account/rewards
- **Tính năng**: Chỉnh sửa thông tin cá nhân, chỉnh sửa danh sách địa chỉ người dùng, xem danh sách voucher/gift được quy đổi từ redeem reward vinamilk sang, danh sách các đơn hàng đã đặt, chi tiết đơn hàng đã đặt(thông tin các kiện hàng, giá tiền, giá giảm sản phẩm, phí vận chuyển, gía giảm từ voucher(nếu có), thông tin thuế(nếu có)
- **Screenshot**: <img width="1349" height="1019" alt="image" src="https://github.com/user-attachments/assets/c9efeefc-c352-4ce3-9545-c8e91195c400" /> <img width="1897" height="920" alt="image" src="https://github.com/user-attachments/assets/c6b223d6-5c92-40ab-bf25-44e693f9c41d" /> <img width="1898" height="1079" alt="image" src="https://github.com/user-attachments/assets/9db6204b-aa29-4162-9ade-aee24f778889" /> <img width="1901" height="1079" alt="image" src="https://github.com/user-attachments/assets/cb24e190-a699-486b-bc0d-d0c5d16b6278" /> <img width="889" height="1079" alt="image" src="https://github.com/user-attachments/assets/972b4ead-18b0-4756-a58b-d77a389a308b" />

#### 8. Trang blog
- **Page**: /tin-tuc
- **Tính năng**: Trang này sẽ hiển thị danh sách các mục + danh sách blog của mục đó, filter theo mục. Chi tiết blog
- **Screenshot**: <img width="1902" height="1079" alt="image" src="https://github.com/user-attachments/assets/0cb624ae-57e3-4d10-916b-914f2ff2da32" /> <img width="1898" height="1079" alt="image" src="https://github.com/user-attachments/assets/81da468d-3514-414d-bbc6-99211e1f23f3" /> <img width="1898" height="1079" alt="image" src="https://github.com/user-attachments/assets/bf7840b0-65df-4f41-b81d-e9d991dd2afc" />


#### 9. Trang sản phẩm bán chạy
- **Page**: /best-selling
- **Tính năng**: Hiển thị danh sách như /collection/all-product thôi, tính toán các sản phẩm được bán nhiều nhất trong hệ thống show ra.

#### 10. Trang sản phẩm ưu đãi
- **Page**: /promotions
- **Tính năng**: Là page về các sản phẩm ưu đãi. Banner gird, filter catalog, list product to best selling of store.
- **Screenshot**: <img width="1895" height="1079" alt="image" src="https://github.com/user-attachments/assets/090a1b59-025d-4f60-a029-6a41f65b4296" /> <img width="1902" height="1079" alt="image" src="https://github.com/user-attachments/assets/c43843c6-38e9-4969-adfc-1d788ad7acc1" />

#### 11. Trang sản phẩm flash sale
- **Page**: /flash-sales
- **Tính năng**: Là page sẽ hiển thị chương trình flash sale(nếu có) do admin quản trị.
- **Screenshot**: <img width="1899" height="1079" alt="image" src="https://github.com/user-attachments/assets/66a393f2-ed18-4bd6-ab3c-dd473070ff78" /> <img width="1899" height="862" alt="image" src="https://github.com/user-attachments/assets/5e060faa-8db0-4919-ab07-e1dfa4dbbde1" />

#### 12. Trang sản khuyến mãi
- **Page**: /khuyen-mai
- **Tính năng**: Thông tin về các chương trình ưu đãi trong tháng. Banner hero, banners grid, modal from banner, list product, mục lục sticky, thông tin thể lệ chương trình
- **Screenshot**: <img width="1903" height="1079" alt="image" src="https://github.com/user-attachments/assets/f97b07ed-721a-4d0f-a7a9-9646391e8019" /> <img width="829" height="871" alt="image" src="https://github.com/user-attachments/assets/8d97bd8c-6e55-4b8e-b1ea-1c549794c7bf" /> <img width="1898" height="1079" alt="image" src="https://github.com/user-attachments/assets/53cd386f-66b1-4552-8f08-73fa0e878497" /> <img width="1902" height="1079" alt="image" src="https://github.com/user-attachments/assets/b80f0f61-5903-4a06-85d9-cd3364640bcf" /> <img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/8bbb132d-1ac5-4ded-95f1-45772930aabf" />

#### 13. Trang sản hỗ trợ
- **Page**: /support/ + các biến thể khác
- **Tính năng**: Trang nội dung hỗ trợ về các điều khoản, chính sách, thể lệ,... 
- **Screenshot**: <img width="1899" height="1079" alt="image" src="https://github.com/user-attachments/assets/67ec7bbc-c3c5-49d6-a976-8c8200936d19" />

#### 15. Các sidebar khác
- **Sidebar**: Sidebar search, sidebar chatbox, sidebar cart, siderbar đổi quà, sidebar voucher + list vinamilk reward(nếu có)
- **Tính năng**: 
- **Screenshot**: <img width="1896" height="1079" alt="image" src="https://github.com/user-attachments/assets/aeb28ccd-9d73-49b3-9002-13eb219a6edd" /> <img width="534" height="867" alt="image" src="https://github.com/user-attachments/assets/3af03f3c-b56b-4daf-bb55-31cd059058ff" /> <img width="531" height="865" alt="image" src="https://github.com/user-attachments/assets/3772b0bf-fe0d-47ec-aa30-a46a69a7b21a" /> <img width="1905" height="865" alt="image" src="https://github.com/user-attachments/assets/7c414b26-2673-48fe-b803-27233321cbdd" /> <img width="1902" height="867" alt="image" src="https://github.com/user-attachments/assets/2a390723-161e-45c0-a4e3-fcdd909b6c58" /> <img width="1902" height="876" alt="image" src="https://github.com/user-attachments/assets/f172a5a4-0b3b-4a4c-8cc0-0f7fb939c754" />

### Trang dành cho Quản trị

#### 1. Dashboard monitor analyst count, sum,... order, quantity, money,...
- **Page**: /admin
- **Tính năng**: Hiển thị số liệu từ dữ liệu của website custom theo logic nghiệp vụ analyst.
- **Screenshot**: <img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/ef1d0af6-c6c5-4a3c-a01e-e53391e1a5f3" /> <img width="1480" height="537" alt="image" src="https://github.com/user-attachments/assets/b6c1ba7a-986c-4dbd-ab2e-104e0606da9e" /> <img width="1474" height="673" alt="image" src="https://github.com/user-attachments/assets/ee30ff18-79c9-4636-81b4-eb12e3e585d1" />

#### 2. Pages manager other include order, logistic order, packing order, vat-order, product, brand, categories, stores, payement, các thuộc tính của product, banner, blog, trending search, api documantation, GHN manager, activity log systemm, chatbox setting, users, role permissions, admin, promotions, marketings, voucher, gift, rewards, care subscription, stocks,....

### Quy trình client mua hàng -> admin xử lý -> status order -> log
- **Steps Client**:
  - B1: Khách hàng chọn các sản phẩm muốn mua thêm vào giỏ hàng.
  - B2: Vào cart để xem các sản phẩm đã thêm vào, badge marketing promotions của hệ thống sẽ xử lý việc tính toán logic các điều kiện của chương trình khuyến mãi active, nếu thỏa điều kiện trong danh sách conditions(reward thuộc condistion) thì sẽ hiển thị điều kiện thỏa đó cho khách hàng trong đơn hàng đó.
  - B3: Khách hàng điều chỉnh quà tặng, voucher(nếu có)
  - B4: Tiến hành thanh toán -> checkout
  - B5: Checkout sẽ có các option required accept trước khi tới payment. Chỉnh sửa địa chỉ giao nhận, phương thức giao nhận, information VAT of order(nếu có), phương thức thanh toán(api key payment momo, stripe, paypal, vnpay), hiển thị thông tin chi tiết sản phẩm đơn hàng, chỉnh sửa quà tặng(nếu có), chỉnh sửa voucher(nếu có)
  - B6: Chọn phương thức thanh toán -> success -> order detail(create status order)
  - B7: Sau khi hoàn thành sẽ trỏ tới page chi tiết đơn hàng đó. Test order with order code: ES-260602195125GGJQ
  - B8: Đơn hàng sẽ có status tree cập nhật theo action admin system sau khi đơn hàng success.
  - B9: Đơn hàng có mã đơn, trong đơn hàng có các kiện hàng riêng(mã kiện) hiển thị thông tin về thanh toán, địa chỉ client, VAT(nếu có), sản phẩm, giá tiền,... Kiện hàng 0đ là kiện hàng quà tặng từ promotions + rewards(nếu có)
- **Screenshot**: <img width="622" height="868" alt="image" src="https://github.com/user-attachments/assets/b6c346a9-bda1-480c-bb3a-0fa5d3b4488a" /> <img width="1193" height="864" alt="image" src="https://github.com/user-attachments/assets/e555e0bc-abc5-4a76-a92c-cc75daf7e2f9" /> <img width="1186" height="868" alt="image" src="https://github.com/user-attachments/assets/1f929e85-3d5f-44cd-9e3f-2c0eb9d5a090" /> <img width="806" height="717" alt="image" src="https://github.com/user-attachments/assets/86387a1e-bf25-4cda-b461-1e9f6d41d2a0" /> <img width="1356" height="606" alt="image" src="https://github.com/user-attachments/assets/2b7e0f3a-6003-40c4-9015-425fea2f234c" /> <img width="684" height="964" alt="image" src="https://github.com/user-attachments/assets/5b565687-c18e-4e77-bc51-e16556565fe6" /> <img width="682" height="762" alt="image" src="https://github.com/user-attachments/assets/d4f97349-4ec0-4256-ad23-db2461655083" /> <img width="680" height="788" alt="image" src="https://github.com/user-attachments/assets/e9ddb3db-1edd-46a8-9303-990999cc31ba" />

- **Steps Admin**:
  - B1: Vào order để xem đơn hàng #ES-260602195125GGJQ. Ở đây sẽ hiển thị chi tiết thông tin đơn hàng, kiện hàng,...
  - B2: Admin role cập nhật Trạng thái đơn hàng từ 'chờ tiếp nhận' -> 'chờ xử lý đóng gói'
  - B3: Khi order đang ở status 'chờ xử lý đóng gói' thì bên bộ phận packing order sẽ nhận đơn và tiến hành chuẩn bị. Sau khi chuẩn bị xong thì bộ phận này sẽ cập nhật status order sang 'đã đóng gói' (*lưu ý: mỗi bộ phận chỉ được chọn status của bộ phận đó, không cấp quyền sửa status khác)
  - B3: Sau khi bộ phận packing cập nhật order status sang 'đã đóng gói' thì lúc này đơn sẽ change status order sang 'đã đóng gói/chờ giao hàng'. Lúc này bộ phận logistics sẽ tiếp nhận đơn này.
  - B4: Khi bộ phận logistics tiếp nhận và chuyển status sang 'đang giao hàng' thì hệ thống sẽ tiến hành dùng api collab with GHN để tạo mã vận đơn GHN cho đơn hàng để giao. Lúc này bên GHN sẽ đảm nhận vai trò giao hàng và cập nhật lại cho system. Bên phía client cũng sẽ cập nhật trạng thái đơn tương tự.
- **Screenshot**: <img width="1727" height="159" alt="image" src="https://github.com/user-attachments/assets/3c734035-deae-4835-9a5a-0ed9428306c4" /> <img width="1431" height="188" alt="image" src="https://github.com/user-attachments/assets/ccba72a4-7d5e-4b0f-b976-175d20d1ddb8" /> <img width="1419" height="265" alt="image" src="https://github.com/user-attachments/assets/940d1f6b-0ebf-4d98-832b-47d68be7ddee" /> <img width="1022" height="578" alt="image" src="https://github.com/user-attachments/assets/83374697-59b1-455b-9ff7-f17c3f5ec9a5" />

 - Quản trị VAT đơn hàng
 - **Screenshot**: <img width="1622" height="981" alt="image" src="https://github.com/user-attachments/assets/43bc1289-fe20-4736-934e-d839600ce069" />
 - Quản trị log payment
 - **Screenshot**: <img width="1624" height="784" alt="image" src="https://github.com/user-attachments/assets/7488f19a-5951-436d-babb-ba0e39d39dcf" />
 - Hệ thống có logic giữ kho sản phẩm được mua trong khoảng thời gian để tránh các trường hợp ví dụ như nhiều khách mua 1 sản phẩm đó cùng 1 thời điểm, khách đã trừ tiền nhưng đơn hàng bị hủy do hết kho,... Khi khách nhấn nút đặt hàng, hệ thống sẽ giữ (reserve) hoặc trừ tạm thời số lượng kho đó trong một khoảng thời gian quy định (ví dụ: 15-30 phút) để chờ thanh toán. Thời gian giữ kho theo phương thức thanh toán: COD: 30 phút, VNPay/MoMo: 10 phút, Credit Card: 5 phút.
 - **Screenshot**: <img width="1594" height="132" alt="image" src="https://github.com/user-attachments/assets/a0d8816f-d025-4a64-a6d7-ec4043082d9c" /> <img width="1587" height="182" alt="image" src="https://github.com/user-attachments/assets/97e10eff-9e56-4bb4-b95c-2849ecbb608f" />
 - Activity log action system detail,...

## Quy trình đóng góp

1. Fork repository và tạo branch từ `main`.
2. Đặt tên branch theo convention: `feature/<tên-tính-năng>` hoặc `fix/<mô-tả-lỗi>`.
3. Commit theo [Conventional Commits](https://www.conventionalcommits.org): `feat:`, `fix:`, `refactor:`, `docs:`, v.v.
4. Đảm bảo toàn bộ test pass trước khi mở Pull Request.
5. Mô tả rõ trong PR: vấn đề đang giải quyết, hướng tiếp cận, cách kiểm thử.

### Tiêu chuẩn code

- PHP: PSR-12
- TypeScript: cấu hình ESLint trong repo
- Viết unit/feature test cho business logic mới
- Không commit thông tin nhạy cảm (API key, credential)

---

## Giấy phép

MIT License. Xem file [LICENSE](LICENSE) để biết chi tiết.

---

Liên hệ: vyquy633@gmail.com / +84 945449758
