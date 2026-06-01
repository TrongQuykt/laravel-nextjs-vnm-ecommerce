# Vinamilk Core Ecommerce - API Documentation

## Table of Contents
1. [API Overview](#api-overview)
2. [Authentication](#authentication)
3. [Base URL & Versioning](#base-url--versioning)
4. [Response Format](#response-format)
5. [Error Handling](#error-handling)
6. [Rate Limiting](#rate-limiting)
7. [Public Endpoints](#public-endpoints)
8. [Protected Endpoints](#protected-endpoints)
9. [Admin Endpoints](#admin-endpoints)
10. [Webhooks](#webhooks)
11. [API Reference](#api-reference)

---

## API Overview

### API Information
- **Base URL:** `https://api.vinamilk.com/api`
- **API Version:** v1
- **Content Type:** `application/json`
- **Character Encoding:** UTF-8
- **Authentication:** Bearer Token (Sanctum)

### API Standards
- **Architecture:** RESTful
- **Data Format:** JSON
- **Pagination:** Cursor-based and offset-based
- **Filtering:** Query parameters
- **Sorting:** Query parameters
- **Field Selection:** Sparse fieldsets

---

## Authentication

### Authentication Methods

#### 1. Bearer Token (Sanctum)
Used for API authentication for mobile apps and third-party integrations.

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Token Generation:**
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abcdef123456...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  }
}
```

#### 2. Session Authentication
Used for admin panel and web applications.

**Request Headers:**
```
Cookie: laravel_session=eyJpdiI6...
```

### Token Refresh
Tokens expire after 24 hours. Use the refresh endpoint to get a new token.

```http
POST /api/v1/refresh-token
Authorization: Bearer {token}
```

---

## Base URL & Versioning

### Base URL
- **Development:** `http://localhost:8000/api`
- **Staging:** `https://staging-api.vinamilk.com/api`
- **Production:** `https://api.vinamilk.com/api`

### Versioning
API versioning is done via URL path:
```
/api/v1/products
/api/v2/products
```

Current version: **v1**

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  },
  "meta": {
    // Metadata (pagination, etc.)
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error message"]
  },
  "code": "ERROR_CODE"
}
```

### Pagination Response
```json
{
  "success": true,
  "data": {
    "items": [...]
  },
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Internal Server Error |

### Error Codes

| Code | Description |
|------|-------------|
| VALIDATION_ERROR | Input validation failed |
| AUTHENTICATION_FAILED | Invalid credentials |
| AUTHORIZATION_FAILED | Insufficient permissions |
| RESOURCE_NOT_FOUND | Resource does not exist |
| RATE_LIMIT_EXCEEDED | Too many requests |
| SERVER_ERROR | Internal server error |

---

## Rate Limiting

### Rate Limits
- **Unauthenticated:** 60 requests per minute
- **Authenticated:** 120 requests per minute
- **Admin:** 300 requests per minute

### Rate Limit Headers
```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1634567890
```

### Rate Limit Exceeded Response
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "code": "RATE_LIMIT_EXCEEDED"
}
```

---

## Public Endpoints

### Tenant Info
Get tenant information.

```http
GET /api/v1/tenant
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tenant": "Default"
  }
}
```

---

### Home Page
Get home page data including featured products, banners, etc.

```http
GET /api/v1/home
```

**Response:**
```json
{
  "success": true,
  "data": {
    "featured_products": [...],
    "banners": [...],
    "categories": [...]
  }
}
```

---

### Menus
Get mega menu structure.

```http
GET /api/v1/menus
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Sản phẩm",
      "link_url": "/products",
      "featured_product": {
        "id": 1,
        "name": "Vinamilk 100%",
        "computed_main_image": "https://..."
      }
    }
  ]
}
```

---

### Catalog
Get product catalog with filters.

```http
GET /api/v1/catalog
```

**Query Parameters:**
- `page` (integer) - Page number (default: 1)
- `per_page` (integer) - Items per page (default: 20)
- `category` (string) - Filter by category slug
- `brand` (string) - Filter by brand slug
- `price_min` (decimal) - Minimum price
- `price_max` (decimal) - Maximum price
- `sort` (string) - Sort order (price_asc, price_desc, newest, popular)
- `search` (string) - Search query

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [...],
    "filters": {
      "categories": [...],
      "brands": [...],
      "price_range": {
        "min": 10000,
        "max": 500000
      }
    }
  },
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

---

### Catalog Filters
Get available filters for catalog.

```http
GET /api/v1/catalog/filters
```

**Response:**
```json
{
  "success": true,
  "data": {
    "categories": [...],
    "brands": [...],
    "flavors": [...],
    "volumes": [...],
    "price_ranges": [...]
  }
}
```

---

### Category Products
Get products by category.

```http
GET /api/v1/collections/{slug}
```

**Path Parameters:**
- `slug` (string) - Category slug

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `sort` (string) - Sort order

**Response:**
```json
{
  "success": true,
  "data": {
    "category": {
      "id": 1,
      "name": "Sữa tươi",
      "slug": "sua-tuoi"
    },
    "products": [...]
  },
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

---

### Product Detail
Get single product details.

```http
GET /api/v1/products/{slug}
```

**Path Parameters:**
- `slug` (string) - Product slug

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Vinamilk 100%",
    "slug": "vinamilk-100",
    "description": "...",
    "price": 25000,
    "compare_price": 30000,
    "category": {...},
    "brand": {...},
    "variants": [
      {
        "id": 1,
        "name": "180ml",
        "price": 25000,
        "stock_quantity": 100,
        "volume_ml": 180,
        "flavor": {...}
      }
    ],
    "images": [...],
    "features": [...],
    "nutritional_info": {...},
    "reviews": [...]
  }
}
```

---

### Search
Search products.

```http
GET /api/v1/search
```

**Query Parameters:**
- `q` (string) - Search query (required)
- `page` (integer) - Page number
- `per_page` (integer) - Items per page

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [...],
    "total": 25
  },
  "meta": {
    "current_page": 1,
    "total": 25
  }
}
```

---

### Search Suggestions
Get search suggestions.

```http
GET /api/v1/search/suggestions
```

**Query Parameters:**
- `q` (string) - Search query (required)

**Response:**
```json
{
  "success": true,
  "data": {
    "suggestions": [
      "Vinamilk 100%",
      "Vinamilk Organic",
      "Sữa chua Vinamilk"
    ]
  }
}
```

---

### Blogs
Get blog posts.

```http
GET /api/v1/blogs
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `category` (string) - Filter by category slug

**Response:**
```json
{
  "success": true,
  "data": {
    "posts": [...]
  },
  "meta": {
    "current_page": 1,
    "total": 30
  }
}
```

---

### Blog Detail
Get single blog post.

```http
GET /api/v1/blogs/{slug}
```

**Path Parameters:**
- `slug` (string) - Blog post slug

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Lợi ích của sữa tươi",
    "slug": "loi-ich-cua-sua-tuoi",
    "content": "...",
    "featured_image": "https://...",
    "category": {...},
    "author": {...},
    "published_at": "2024-01-15T10:00:00Z"
  }
}
```

---

### Banners
Get active banners.

```http
GET /api/v1/banners
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Khuyến mãi tháng 5",
      "image_url": "https://...",
      "link_url": "/promotions",
      "position": "home",
      "sort_order": 1
    }
  ]
}
```

---

### Support Pages
Get support pages.

```http
GET /api/v1/support-pages
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Chính sách đổi trả",
      "slug": "chinh-sach-doi-tra"
    }
  ]
}
```

---

### Support Page Detail
Get single support page.

```http
GET /api/v1/support-pages/{slug}
```

**Path Parameters:**
- `slug` (string) - Support page slug

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Chính sách đổi trả",
    "slug": "chinh-sach-doi-tra",
    "content": "..."
  }
}
```

---

### Login
User login.

```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abcdef123456...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "phone": "0901234567"
    }
  }
}
```

---

### Register
User registration.

```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "user@example.com",
  "phone": "0901234567",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "1|abcdef123456...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    }
  }
}
```

---

### Forgot Password
Request password reset.

```http
POST /api/v1/forgot-password
Content-Type: application/json

{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

---

### Promotions
Get active promotions.

```http
GET /api/v1/promotions
```

**Response:**
```json
{
  "success": true,
  "data": {
    "campaigns": [...],
    "flash_sales": [...],
    "banners": [...]
  }
}
```

---

### Promotions Page Banners
Get promotions page banners.

```http
GET /api/v1/promotions-page-banners
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Flash Sale",
      "image_url": "https://...",
      "link_url": "/flash-sale"
    }
  ]
}
```

---

### Care Page
Get care program page data.

```http
GET /api/v1/care
```

**Response:**
```json
{
  "success": true,
  "data": {
    "page_settings": {...},
    "delivery_options": [...],
    "greeting_cards": [...]
  }
}
```

---

### Care Products
Get care products.

```http
GET /api/v1/care/products
```

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [...]
  }
}
```

---

### Care Greeting Cards
Get care greeting cards.

```http
GET /api/v1/care/greeting-cards
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Chúc mừng sinh nhật",
      "image": "https://...",
      "message_template": "Chúc mừng sinh nhật {name}!"
    }
  ]
}
```

---

### Care Calculate
Calculate care program price.

```http
POST /api/v1/care/calculate
Content-Type: application/json

{
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ],
  "delivery_option_id": 1,
  "greeting_card_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 500000,
    "delivery_fee": 20000,
    "total": 520000
  }
}
```

---

### Stores
Get store locations.

```http
GET /api/v1/stores
```

**Query Parameters:**
- `province` (string) - Filter by province
- `latitude` (decimal) - User latitude
- `longitude` (decimal) - User longitude
- `radius` (integer) - Search radius in km

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Vinamilk Store - Hà Nội",
      "address": "123 Đường ABC, Quận 1",
      "phone": "0901234567",
      "latitude": 21.0285,
      "longitude": 105.8542,
      "opening_hours": {
        "monday": "08:00-21:00",
        "tuesday": "08:00-21:00"
      }
    }
  ]
}
```

---

### Shipping Methods
Get available shipping methods.

```http
GET /api/v1/shipping-methods
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Giao hàng tiêu chuẩn",
      "code": "standard",
      "carrier": "GHN",
      "base_fee": 20000,
      "estimated_days": "2-3"
    },
    {
      "id": 2,
      "name": "Giao hàng nhanh",
      "code": "express",
      "carrier": "GHN",
      "base_fee": 35000,
      "estimated_days": "1-2"
    }
  ]
}
```

---

### Calculate Shipping Fee
Calculate shipping fee.

```http
POST /api/v1/shipping/calculate-fee
Content-Type: application/json

{
  "shipping_method_id": 1,
  "province": "Hà Nội",
  "district": "Quận 1",
  "weight": 1000,
  "cart_value": 500000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "shipping_fee": 25000,
    "estimated_days": "2-3"
  }
}
```

---

### Chat
Send chat message to AI assistant.

```http
POST /api/v1/chat
Content-Type: application/json

{
  "message": "Sữa nào tốt cho trẻ em?",
  "session_id": "optional-session-id"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "message": "Đối với trẻ em, tôi khuyên dùng Vinamilk 100%...",
    "session_id": "abc123"
  }
}
```

---

### Cart Evaluate
Evaluate cart for promotions and discounts.

```http
POST /api/v1/cart/evaluate
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variant_id": 1,
      "quantity": 2
    }
  ],
  "user_id": 1,
  "coupon_code": "SALE20"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "subtotal": 100000,
    "discount": 20000,
    "shipping_fee": 20000,
    "total": 100000,
    "applied_promotions": [
      {
        "name": "Giảm giá 20%",
        "discount": 20000
      }
    ]
  }
}
```

---

### Validate Voucher Code
Validate voucher code.

```http
POST /api/v1/vouchers/validate-code
Content-Type: application/json

{
  "code": "VOUCHER50",
  "cart_value": 500000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "valid": true,
    "voucher": {
      "id": 1,
      "code": "VOUCHER50",
      "value": 50000,
      "min_order_value": 300000
    },
    "discount": 50000
  }
}
```

---

### Payment Success Callback
Payment gateway callback for successful payment.

```http
POST /api/v1/orders/{orderNumber}/payment-success
```

**Path Parameters:**
- `orderNumber` (string) - Order number

**Response:**
```json
{
  "success": true,
  "message": "Payment processed successfully"
}
```

---

## Protected Endpoints

### User Profile
Get user profile.

```http
GET /api/v1/user/profile
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "0901234567",
    "avatar": "https://...",
    "addresses": [...]
  }
}
```

---

### Update Profile
Update user profile.

```http
PUT /api/v1/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe",
  "phone": "0901234567",
  "date_of_birth": "1990-01-01"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "phone": "0901234567"
  }
}
```

---

### Cart
Get user cart.

```http
GET /api/v1/cart
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "items": [
      {
        "id": 1,
        "product": {...},
        "variant": {...},
        "quantity": 2,
        "subtotal": 50000
      }
    ],
    "subtotal": 100000,
    "total_items": 3
  }
}
```

---

### Add to Cart
Add item to cart.

```http
POST /api/v1/cart
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1,
  "variant_id": 1,
  "quantity": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Item added to cart",
  "data": {
    "cart_item": {...}
  }
}
```

---

### Update Cart Item
Update cart item quantity.

```http
PUT /api/v1/cart/{itemId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "quantity": 3
}
```

**Response:**
```json
{
  "success": true,
  "message": "Cart item updated",
  "data": {
    "cart_item": {...}
  }
}
```

---

### Remove from Cart
Remove item from cart.

```http
DELETE /api/v1/cart/{itemId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Item removed from cart"
}
```

---

### Clear Cart
Clear all items from cart.

```http
DELETE /api/v1/cart
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Cart cleared"
}
```

---

### Create Order
Create new order.

```http
POST /api/v1/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipping_address_id": 1,
  "shipping_method_id": 1,
  "payment_method_id": 1,
  "coupon_code": "SALE20",
  "customer_notes": "Giao giờ hành chính"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-2024-000001",
    "status": "pending",
    "subtotal": 100000,
    "discount": 20000,
    "shipping_fee": 20000,
    "total": 100000,
    "payment_url": "https://payment-gateway.com/..."
  }
}
```

---

### Get Orders
Get user orders.

```http
GET /api/v1/orders
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `status` (string) - Filter by status

**Response:**
```json
{
  "success": true,
  "data": {
    "orders": [...]
  },
  "meta": {
    "current_page": 1,
    "total": 10
  }
}
```

---

### Get Order Detail
Get single order details.

```http
GET /api/v1/orders/{orderNumber}
Authorization: Bearer {token}
```

**Path Parameters:**
- `orderNumber` (string) - Order number

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "ORD-2024-000001",
    "status": "confirmed",
    "subtotal": 100000,
    "total": 100000,
    "items": [...],
    "shipping_address": {...},
    "payment": {...},
    "timeline": [...]
  }
}
```

---

### Cancel Order
Cancel order.

```http
POST /api/v1/orders/{orderNumber}/cancel
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Order cancelled successfully"
}
```

---

### Wishlist
Get user wishlist.

```http
GET /api/v1/wishlist
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [...]
  }
}
```

---

### Add to Wishlist
Add product to wishlist.

```http
POST /api/v1/wishlist
Authorization: Bearer {token}
Content-Type: application/json

{
  "product_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product added to wishlist"
}
```

---

### Remove from Wishlist
Remove product from wishlist.

```http
DELETE /api/v1/wishlist/{productId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Product removed from wishlist"
}
```

---

### Addresses
Get user addresses.

```http
GET /api/v1/addresses
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "recipient_name": "John Doe",
      "phone": "0901234567",
      "province": "Hà Nội",
      "district": "Quận 1",
      "ward": "Phường 1",
      "street_address": "123 Đường ABC",
      "is_default": true
    }
  ]
}
```

---

### Add Address
Add new address.

```http
POST /api/v1/addresses
Authorization: Bearer {token}
Content-Type: application/json

{
  "recipient_name": "John Doe",
  "phone": "0901234567",
  "province": "Hà Nội",
  "district": "Quận 1",
  "ward": "Phường 1",
  "street_address": "123 Đường ABC",
  "is_default": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Address added successfully",
  "data": {
    "id": 1,
    "recipient_name": "John Doe"
  }
}
```

---

### Update Address
Update address.

```http
PUT /api/v1/addresses/{addressId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "recipient_name": "John Doe",
  "phone": "0901234567"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Address updated successfully"
}
```

---

### Delete Address
Delete address.

```http
DELETE /api/v1/addresses/{addressId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Address deleted successfully"
}
```

---

### Set Default Address
Set default address.

```http
POST /api/v1/addresses/{addressId}/set-default
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Default address updated"
}
```

---

## Admin Endpoints

### Admin Authentication
Admin login.

```http
POST /api/admin/login
Content-Type: application/json

{
  "email": "admin@vinamilk.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "1|admin_token...",
    "admin": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@vinamilk.com",
      "role": "Super Admin"
    }
  }
}
```

---

### Admin Dashboard Stats
Get dashboard statistics.

```http
GET /api/admin/dashboard/stats
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_revenue": 1500000000,
    "total_orders": 500,
    "total_products": 1000,
    "total_users": 2000,
    "revenue_chart": [...],
    "order_chart": [...]
  }
}
```

---

### Admin Products
Get all products (admin).

```http
GET /api/admin/products
Authorization: Bearer {admin_token}
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page
- `search` (string) - Search query
- `category` (string) - Filter by category
- `status` (string) - Filter by status

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [...]
  },
  "meta": {
    "current_page": 1,
    "total": 1000
  }
}
```

---

### Admin Create Product
Create new product (admin).

```http
POST /api/admin/products
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "New Product",
  "slug": "new-product",
  "category_id": 1,
  "brand_id": 1,
  "price": 25000,
  "description": "Product description",
  "is_active": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1001,
    "name": "New Product"
  }
}
```

---

### Admin Update Product
Update product (admin).

```http
PUT /api/admin/products/{id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "name": "Updated Product",
  "price": 30000
}
```

**Response:**
```json
{
  "success": true,
  "message": "Product updated successfully"
}
```

---

### Admin Delete Product
Delete product (admin).

```http
DELETE /api/admin/products/{id}
Authorization: Bearer {admin_token}
```

**Response:**
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

---

### Admin Orders
Get all orders (admin).

```http
GET /api/admin/orders
Authorization: Bearer {admin_token}
```

**Query Parameters:**
- `page` (integer) - Page number
- `status` (string) - Filter by status
- `date_from` (date) - Filter from date
- `date_to` (date) - Filter to date

**Response:**
```json
{
  "success": true,
  "data": {
    "orders": [...]
  }
}
```

---

### Admin Update Order Status
Update order status (admin).

```http
POST /api/admin/orders/{orderNumber}/status
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "status": "shipped",
  "notes": "Order shipped via GHN"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order status updated successfully"
}
```

---

### Admin Export
Export data (admin).

```http
GET /api/admin/export/orders
Authorization: Bearer {admin_token}
```

**Query Parameters:**
- `format` (string) - Export format (csv, xlsx)
- `date_from` (date) - Filter from date
- `date_to` (date) - Filter to date

**Response:**
```
Binary file download
```

---

## Webhooks

### Payment Webhook
Payment gateway webhook for payment status updates.

```http
POST /api/webhooks/payment
Content-Type: application/json

{
  "transaction_id": "TXN123456",
  "order_number": "ORD-2024-000001",
  "status": "success",
  "amount": 100000,
  "signature": "abc123..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Webhook received"
}
```

### Shipping Webhook
Shipping provider webhook for shipment status updates.

```http
POST /api/webhooks/shipping
Content-Type: application/json

{
  "tracking_number": "GHN123456",
  "order_number": "ORD-2024-000001",
  "status": "delivered",
  "signature": "xyz789..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Webhook received"
}
```

---

## API Reference

### Common Query Parameters

#### Pagination
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

#### Sorting
- `sort` - Sort field
- `order` - Sort direction (asc, desc)

#### Filtering
- `filter[field]` - Filter by field value
- `search` - Search query

#### Field Selection
- `fields` - Comma-separated list of fields to return

### Common Response Fields

#### Product
- `id` - Product ID
- `name` - Product name
- `slug` - URL slug
- `price` - Current price
- `compare_price` - Original price
- `description` - Product description
- `category` - Category object
- `brand` - Brand object
- `variants` - Product variants array
- `images` - Product images array
- `is_active` - Active status
- `created_at` - Creation timestamp
- `updated_at` - Update timestamp

#### Order
- `id` - Order ID
- `order_number` - Order number
- `status` - Order status
- `subtotal` - Subtotal amount
- `discount` - Discount amount
- `shipping_fee` - Shipping fee
- `total` - Total amount
- `items` - Order items array
- `shipping_address` - Shipping address object
- `payment` - Payment object
- `created_at` - Creation timestamp

#### User
- `id` - User ID
- `name` - User name
- `email` - User email
- `phone` - Phone number
- `avatar` - Avatar URL
- `created_at` - Creation timestamp

---

## Testing the API

### Using cURL

#### Login
```bash
curl -X POST https://api.vinamilk.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'
```

#### Get Products
```bash
curl -X GET https://api.vinamilk.com/api/v1/catalog \
  -H "Authorization: Bearer {token}"
```

### Using Postman

1. Import API collection
2. Set base URL: `https://api.vinamilk.com/api`
3. Add authorization header with Bearer token
4. Send requests

---

## SDK & Libraries

### JavaScript/TypeScript
```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://api.vinamilk.com/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### PHP
```php
use Illuminate\Support\Facades\Http;

$response = Http::withToken($token)
    ->get('https://api.vinamilk.com/api/v1/products');

$data = $response->json();
```

---

## Changelog

### Version 1.0.0 (2024-01-01)
- Initial API release
- Core endpoints for products, orders, users
- Authentication with Sanctum
- Admin panel endpoints

### Version 1.1.0 (2024-02-01)
- Added care program endpoints
- Added AI chat endpoint
- Improved search functionality
- Added marketing engine evaluation

### Version 1.2.0 (2024-03-01)
- Added voucher validation
- Improved error handling
- Added rate limiting
- Performance optimizations

---

## Support

For API support, contact:
- **Email:** api-support@vinamilk.com
- **Documentation:** https://docs.vinamilk.com/api
- **Status Page:** https://status.vinamilk.com
