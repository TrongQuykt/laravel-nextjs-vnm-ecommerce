# Vinamilk Core Ecommerce

<div align="center">

![Vinamilk Logo](https://via.placeholder.com/150x150?text=Vinamilk)

**Nền tảng thương mại điện tử full-stack hiện đại cho Vinamilk**

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-15.x-black.svg)](https://nextjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue.svg)](https://www.typescriptlang.org)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-38B2AC.svg)](https://tailwindcss.com)

</div>

---

## 📋 Mục Lục

- [Giới thiệu](#giới-thiệu)
- [Tính năng](#tính-năng)
- [Công nghệ](#công-nghệ)
- [Kiến trúc](#kiến-trúc)
- [Cài đặt](#cài-đặt)
- [Cấu hình](#cấu-hình)
- [Cấu trúc dự án](#cấu-trúc-dự-án)
- [Trang & Tính năng](#trang--tính-năng)
- [Tài liệu API](#tài-liệu-api)
- [Admin Panel](#admin-panel)
- [Đóng góp](#đóng-góp)
- [Giấy phép](#giấy-phép)

---

## 🎯 Giới thiệu

Vinamilk Core Ecommerce là nền tảng thương mại điện tử full-stack toàn diện được xây dựng với các công nghệ hiện đại. Nó cung cấp giải pháp hoàn chỉnh cho bán lẻ trực tuyến, bao gồm quản lý sản phẩm, xử lý đơn hàng, tích hợp thanh toán, quản lý tồn kho và các tính năng tương tác khách hàng.

### Điểm nổi bật

- 🛒 **Giải pháp Ecommerce hoàn chỉnh**: Trải nghiệm mua sắm đầy đủ từ duyệt sản phẩm đến hoàn tất đơn hàng
- 📦 **Quản lý tồn kho**: Theo dõi tồn kho nâng cao với cảnh báo tồn kho thấp và hệ thống giữ hàng
- 💳 **Đa phương thức thanh toán**: Hỗ trợ COD, MoMo, VNPay, Stripe và PayPal
- 🤖 **Chatbot AI**: Hỗ trợ khách hàng thông minh với tích hợp cơ sở kiến thức
- 🎁 **Chương trình khách hàng thân thiết**: Hệ thống thưởng nhiều tầng với tích điểm đổi quà
- 📱 **Thiết kế Responsive**: Tiếp cận mobile-first với UI/UX hiện đại
- 🔐 **Quyền truy cập dựa trên vai trò**: Hệ thống quyền hạn toàn diện cho admin panel
- 📊 **Dashboard Analytics**: Thông tin thời gian thực và báo cáo

---

## ✨ Tính năng

### Tính năng cho Khách hàng
- **Danh mục sản phẩm**: Duyệt sản phẩm với bộ lọc và tìm kiếm nâng cao
- **Chi tiết sản phẩm**: Thông tin sản phẩm chi tiết với lựa chọn biến thể
- **Giỏ hàng**: Giỏ hàng liên tục với quản lý số lượng
- **Thanh toán**: Thanh toán nhiều bước với nhiều tùy chọn thanh toán
- **Theo dõi đơn hàng**: Cập nhật trạng thái đơn hàng thời gian thực
- **Tài khoản người dùng**: Quản lý hồ sơ, lịch sử đơn hàng, danh sách yêu thích
- **Chương trình khách hàng thân thiết**: Kiếm và đổi điểm để nhận thưởng
- **Hỗ trợ Chat**: Chatbot AI hỗ trợ khách hàng
- **Vinamilk Care**: Dịch vụ giao sữa định kỳ theo đăng ký

### Tính năng Admin
- **Dashboard**: Tổng quan toàn diện với các chỉ số chính
- **Quản lý sản phẩm**: Thao tác CRUD cho sản phẩm, biến thể, danh mục
- **Quản lý đơn hàng**: Xử lý đơn hàng, cập nhật trạng thái, hoàn tất
- **Quản lý tồn kho**: Theo dõi tồn kho, cảnh báo tồn kho thấp, di chuyển tồn kho
- **Quản lý khách hàng**: Tài khoản người dùng, lịch sử đơn hàng, hỗ trợ
- **Marketing**: Khuyến mãi, voucher, banner, bài viết blog
- **Analytics**: Báo cáo bán hàng, thông tin khách hàng, chỉ số hiệu suất
- **Cài đặt hệ thống**: Cấu hình thanh toán, vận chuyển, chatbot
- **Nhật ký hoạt động**: Theo dõi tất cả hành động admin để kiểm tra

### Tính năng Kỹ thuật
- **RESTful API**: API được tài liệu hóa tốt với versioning
- **Cập nhật thời gian thực**: Hỗ trợ WebSocket cho thông báo trực tiếp
- **Caching**: Tích hợp Redis để tối ưu hiệu suất
- **Hệ thống Queue**: Xử lý công việc nền cho các tác vụ nặng
- **Lưu trữ file**: Lưu trữ tương thích S3 cho hình ảnh và tài liệu
- **Multi-tenancy**: Hỗ trợ nhiều cửa hàng/tenant
- **SEO Friendly**: Tối ưu hóa meta tags và tạo sitemap

---

## 🛠 Công nghệ

### Backend (vinamilk-api)
- **Framework**: Laravel 11.x
- **Language**: PHP 8.2+
- **Database**: MySQL 8.0+
- **Cache**: Redis
- **Queue**: Redis + Laravel Horizon
- **Storage**: Tương thích S3 (MinIO/AWS S3)
- **API Documentation**: OpenAPI/Swagger
- **Admin Panel**: Filament PHP
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission

### Frontend (vinamilk-fe)
- **Framework**: Next.js 15.x
- **Language**: TypeScript 5.x
- **Styling**: TailwindCSS 4.x
- **State Management**: Zustand
- **Forms**: React Hook Form + Zod
- **HTTP Client**: Axios
- **Maps**: Leaflet + React Leaflet
- **Icons**: Lucide React
- **UI Components**: Custom components với shadcn/ui patterns

### Tích hợp bên thứ ba
- **Thanh toán**: MoMo, VNPay, Stripe, PayPal
- **Vận chuyển**: GHN (Giao Hang Nhanh)
- **AI/ML**: Google Gemini API cho chatbot
- **Email**: SMTP / SendGrid
- **SMS**: Twilio / Viettel SMS

---

## 🏗 Kiến trúc

### Kiến trúc hệ thống

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   External      │
│   (Next.js)     │◄──►│   (Laravel)     │◄──►│   Services      │
│                 │    │                 │    │                 │
│ - React/TSX     │    │ - REST API      │    │ - Payment GW    │
│ - TailwindCSS   │    │ - Filament      │    │ - Shipping GW   │
│ - Zustand       │    │ - Queue/Redis   │    │ - AI Services   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │   Database      │
                       │   (MySQL)       │
                       │                 │
                       │ - Products      │
                       │ - Orders        │
                       │ - Users         │
                       │ - Inventory     │
                       └─────────────────┘
```

### Database Schema

- **Users**: Tài khoản khách hàng, admin users
- **Products**: Danh mục sản phẩm, biến thể, danh mục
- **Orders**: Quản lý đơn hàng, items, thanh toán
- **Inventory**: Theo dõi tồn kho, di chuyển, giữ hàng
- **Marketing**: Khuyến mãi, voucher, chiến dịch
- **Content**: Bài viết blog, banner, trang
- **Chat**: Tin nhắn, cơ sở kiến thức, cài đặt

---

## 🚀 Cài đặt

### Yêu cầu

- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Redis
- Composer
- npm/yarn

### Cài đặt Backend

```bash
# Di chuyển đến thư mục backend
cd vinamilk-api

# Cài đặt dependencies
composer install

# Copy file môi trường
cp .env.example .env

# Tạo application key
php artisan key:generate

# Cấu hình database trong .env
# DB_DATABASE=vinamilk_ecommerce
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Chạy migrations
php artisan migrate

# Seed database (tùy chọn)
php artisan db:seed

# Link storage
php artisan storage:link

# Khởi động development server
php artisan serve
```

### Cài đặt Frontend

```bash
# Di chuyển đến thư mục frontend
cd vinamilk-fe

# Cài đặt dependencies
npm install

# Copy file môi trường
cp .env.example .env.local

# Cấu hình API URL trong .env.local
# NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1

# Khởi động development server
npm run dev
```

### Truy cập Admin Panel

- URL: `http://localhost:8000/admin`
- Thông tin mặc định (sau khi seed):
  - Email: `admin@vinamilk.com`
  - Password: `password`

---

## ⚙️ Cấu hình

### Biến môi trường

#### Backend (.env)

```env
APP_NAME="Vinamilk Ecommerce"
APP_ENV=local
APP_KEY=your-app-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vinamilk_ecommerce
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Payment Gateways
MOMO_PARTNER_CODE=your_momo_partner_code
MOMO_ACCESS_KEY=your_momo_access_key
MOMO_SECRET_KEY=your_momo_secret_key
MOMO_ENDPOINT=https://test-payment.momo.vn

VNPAY_TMN_CODE=your_vnpay_tmn_code
VNPAY_HASH_SECRET=your_vnpay_hash_secret
VNPAY_TEST_MODE=true

STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret

PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_SECRET=your_paypal_secret

# GHN Shipping
GHN_API_URL=https://dev-online-gateway.ghn.vn
GHN_TOKEN=your_ghn_token
GHN_SHOP_ID=your_ghn_shop_id

# AI Services
GEMINI_API_KEY=your_gemini_api_key

# Storage
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_access_key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=vinamilk-storage
AWS_URL=https://your-bucket.s3.amazonaws.com
```

#### Frontend (.env.local)

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_URL=http://localhost:3000
NEXT_PUBLIC_GTM_ID=GTM-XXXXXX
```

---

## 📁 Cấu trúc dự án

```
vinamilk-core-ecommerce/
├── vinamilk-api/                 # Laravel Backend
│   ├── app/
│   │   ├── Console/             # Artisan commands
│   │   ├── Filament/            # Admin panel resources
│   │   ├── Http/                # Controllers & Middleware
│   │   ├── Models/              # Eloquent models
│   │   ├── Services/            # Business logic services
│   │   └── Traits/              # Reusable traits
│   ├── config/                  # Configuration files
│   ├── database/                # Migrations & Seeders
│   ├── public/                  # Public assets
│   ├── resources/               # Views, assets
│   ├── routes/                  # API & Web routes
│   └── tests/                   # PHPUnit tests
│
├── vinamilk-fe/                 # Next.js Frontend
│   ├── src/
│   │   ├── app/                 # Next.js app router
│   │   ├── components/          # React components
│   │   ├── hooks/               # Custom hooks
│   │   ├── lib/                 # Utility functions
│   │   ├── stores/              # Zustand stores
│   │   └── types/               # TypeScript types
│   ├── public/                  # Static assets
│   └── styles/                  # Global styles
│
└── README.md                     # File này
```

---

## 📄 Trang & Tính năng

> **Lưu ý**: Phần này dùng để tài liệu hóa các trang cụ thể và tính năng của chúng với ảnh chụp màn hình.

### Trang dành cho Khách hàng

#### 1. Trang chủ
- **Tính năng**: Hero banner, sản phẩm nổi bật, khuyến mãi, đánh giá
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 2. Danh sách sản phẩm
- **Tính năng**: Chế độ xem grid/list, bộ lọc, sắp xếp, tìm kiếm
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 3. Chi tiết sản phẩm
- **Tính năng**: Thông tin sản phẩm, lựa chọn biến thể, thêm vào giỏ, đánh giá
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 4. Giỏ hàng
- **Tính năng**: Items giỏ hàng, quản lý số lượng, mã giảm giá
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 5. Thanh toán
- **Tính năng**: Thanh toán nhiều bước, phương thức thanh toán, tùy chọn vận chuyển
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 6. Tài khoản người dùng
- **Tính năng**: Hồ sơ, lịch sử đơn hàng, danh sách yêu thích, điểm tích lũy
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 7. Vinamilk Care
- **Tính năng**: Gói đăng ký, lên lịch giao hàng, quản lý
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

### Trang Admin Panel

#### 1. Dashboard
- **Tính năng**: Chỉ số tổng quan, đơn hàng gần đây, cảnh báo tồn kho thấp
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 2. Quản lý sản phẩm
- **Tính năng**: CRUD sản phẩm, biến thể, danh mục, thương hiệu
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 3. Quản lý đơn hàng
- **Tính năng**: Danh sách đơn hàng, xem chi tiết, cập nhật trạng thái, hoàn tất
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 4. Quản lý tồn kho
- **Tính năng**: Theo dõi tồn kho, di chuyển, giữ hàng, cảnh báo
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 5. Quản lý khách hàng
- **Tính năng**: Danh sách người dùng, hồ sơ, lịch sử đơn hàng, hỗ trợ
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 6. Marketing
- **Tính năng**: Khuyến mãi, voucher, banner, bài viết blog
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

#### 7. Cài đặt hệ thống
- **Tính năng**: Cấu hình thanh toán, vận chuyển, chatbot
- **Screenshot**: [Thêm ảnh chụp màn hình ở đây]
- **Trạng thái**: ✅ Đã hoàn thành

---

## 📚 Tài liệu API

Tài liệu API có sẵn tại `/api/documentation` (khi chạy local) hoặc có thể được tạo bằng OpenAPI/Swagger.

### Base URL
- Local: `http://localhost:8000/api/v1`
- Production: `https://api.vinamilk.com/api/v1`

### Authentication
Hầu hết các endpoint yêu cầu authentication sử dụng Laravel Sanctum tokens.

```bash
# Login để lấy token
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

# Sử dụng token trong headers
Authorization: Bearer {token}
```

### Các Endpoint chính

#### Products
- `GET /products` - Danh sách sản phẩm
- `GET /products/{id}` - Chi tiết sản phẩm
- `GET /products/{id}/variants` - Biến thể sản phẩm

#### Orders
- `POST /orders/checkout` - Tạo đơn hàng
- `GET /orders` - Danh sách đơn hàng người dùng
- `GET /orders/{id}` - Chi tiết đơn hàng

#### Cart
- `GET /cart` - Lấy items giỏ hàng
- `POST /cart/add` - Thêm item vào giỏ
- `PUT /cart/{id}` - Cập nhật item giỏ
- `DELETE /cart/{id}` - Xóa item giỏ

#### User
- `GET /user/profile` - Lấy hồ sơ người dùng
- `PUT /user/profile` - Cập nhật hồ sơ
- `GET /user/orders` - Lấy đơn hàng người dùng

---

## 🎛 Admin Panel

Admin panel được xây dựng với Filament PHP và cung cấp giao diện toàn diện để quản lý nền tảng thương mại điện tử.

### Truy cập
- URL: `/admin`
- Authentication: Bắt buộc
- Permissions: Role-based access control

### Tính năng
- **Dashboard**: Chỉ số thời gian thực và feed hoạt động
- **Quản lý sản phẩm**: CRUD đầy đủ với hỗ trợ biến thể
- **Quản lý đơn hàng**: Xử lý và hoàn tất đơn hàng
- **Quản lý tồn kho**: Theo dõi tồn kho và cảnh báo
- **Quản lý khách hàng**: Tài khoản người dùng và hỗ trợ
- **Công cụ Marketing**: Khuyến mãi, voucher, nội dung
- **Cài đặt hệ thống**: Quản lý cấu hình
- **Nhật ký hoạt động**: Audit trail cho tất cả hành động

### Nhóm điều hướng
- **Bán hàng**: Orders, products
- **Kho hàng**: Inventory, stock alerts
- **Chăm sóc khách hàng**: Customers, support
- **Khuyến mãi**: Promotions, vouchers
- **Tài khoản**: Users, roles, permissions
- **Hệ thống**: Settings, logs, configuration

---

## 🤝 Đóng góp

Đóng góp được chào đón! Vui lòng làm theo các bước sau:

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/amazing-feature`)
3. Commit thay đổi của bạn (`git commit -m 'Add amazing feature'`)
4. Push đến branch (`git push origin feature/amazing-feature`)
5. Mở Pull Request

### Hướng dẫn phát triển

- Tuân thủ chuẩn coding PSR-12 cho PHP
- Tuân thủ quy tắc ESLint cho TypeScript
- Viết tests cho các tính năng mới
- Cập nhật tài liệu khi cần thiết
- Sử dụng conventional commit messages

---

## 📄 Giấy phép

Dự án này được cấp phép theo MIT License - xem file [LICENSE](LICENSE) để biết chi tiết.

---

## 📞 Hỗ trợ

Để được hỗ trợ, email vyquy633@gmail.com

---

## Lời cảm ơn

- [Laravel](https://laravel.com) - The PHP framework for web artisans
- [Next.js](https://nextjs.org) - The React framework for production
- [Filament](https://filamentphp.com) - Elegant Laravel admin panel
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Vinamilk](https://vinamilk.com.vn) - Inspiration and use case

---

<div align="center">

**Xây dựng dựa theo Vinamilk**

</div>

