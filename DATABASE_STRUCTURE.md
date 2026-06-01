# Vinamilk Core Ecommerce - Database Structure Documentation

## Table of Contents
1. [Database Overview](#database-overview)
2. [Core Tables](#core-tables)
3. [Product Catalog Tables](#product-catalog-tables)
4. [Order Management Tables](#order-management-tables)
5. [Payment & Logistics Tables](#payment--logistics-tables)
6. [Marketing & Promotion Tables](#marketing--promotion-tables)
7. [Content Management Tables](#content-management-tables)
8. [User & Authentication Tables](#user--authentication-tables)
9. [Admin & Permission Tables](#admin--permission-tables)
10. [Care & Subscription Tables](#care--subscription-tables)
11. [Activity & Logging Tables](#activity--logging-tables)
12. [Entity Relationship Diagram](#entity-relationship-diagram)

---

## Database Overview

### Database Engine
- **Engine:** MySQL 8.0+
- **Character Set:** utf8mb4
- **Collation:** utf8mb4_unicode_ci
- **Storage Engine:** InnoDB (transactional)

### Database Naming Convention
- **Table Names:** snake_case, plural
- **Column Names:** snake_case
- **Foreign Keys:** `{table}_id`
- **Indexes:** `idx_{table}_{column}`
- **Unique Constraints:** `unique_{table}_{column}`

### Design Principles
- **Normalization:** 3NF (Third Normal Form)
- **Referential Integrity:** Foreign key constraints
- **Data Integrity:** NOT NULL, UNIQUE, CHECK constraints
- **Timestamps:** created_at, updated_at on all tables
- **Soft Deletes:** deleted_at on critical tables

---

## Core Tables

### tenants
Multi-tenancy support for multiple stores/brands.

```sql
CREATE TABLE tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    domain VARCHAR(255) NULL,
    settings JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: users, stores, products, orders

---

### users
Customer and admin user accounts.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other') NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: tenant
- Has Many: orders, cart_items, addresses, reviews, wishlists
- Belongs To Many: roles (Spatie)

---

### addresses
User shipping and billing addresses.

```sql
CREATE TABLE addresses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    recipient_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    ward VARCHAR(100) NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    address_type ENUM('shipping', 'billing', 'both') DEFAULT 'shipping',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Relationships:**
- Belongs To: user
- Has Many: orders (as shipping/billing address)

---

## Product Catalog Tables

### categories
Product categorization hierarchy.

```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    tenant_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    icon VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: parent (self), tenant
- Has Many: children (self), products, product_lines

---

### brands
Product brand information.

```sql
CREATE TABLE brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    logo VARCHAR(255) NULL,
    description TEXT NULL,
    website VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: products

---

### product_lines
Product line/grouping (e.g., Vinamilk 100%, Organic, etc.).

```sql
CREATE TABLE product_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: category
- Has Many: products

---

### products
Main product catalog.

```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    brand_id BIGINT UNSIGNED NULL,
    product_line_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    sku VARCHAR(100) NULL UNIQUE,
    barcode VARCHAR(100) NULL,
    description TEXT NULL,
    short_description VARCHAR(500) NULL,
    main_image VARCHAR(255) NULL,
    comparison_table JSON NULL,
    features JSON NULL,
    nutritional_info JSON NULL,
    storage_info TEXT NULL,
    shelf_life_days INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    seo_title VARCHAR(255) NULL,
    seo_description VARCHAR(500) NULL,
    seo_keywords VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (product_line_id) REFERENCES product_lines(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: tenant, category, brand, product_line
- Has Many: product_variants, product_images, product_attributes, order_items, cart_items, reviews

---

### product_variants
Product variants (size, flavor, packaging).

```sql
CREATE TABLE product_variants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(100) NULL UNIQUE,
    barcode VARCHAR(100) NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    compare_price DECIMAL(10, 2) NULL,
    cost_price DECIMAL(10, 2) NULL,
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    weight DECIMAL(8, 2) NULL,
    volume_ml INT NULL,
    flavor_id BIGINT UNSIGNED NULL,
    sugar_level_id BIGINT UNSIGNED NULL,
    packaging_type_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (flavor_id) REFERENCES flavors(id) ON DELETE SET NULL,
    FOREIGN KEY (sugar_level_id) REFERENCES sugar_levels(id) ON DELETE SET NULL,
    FOREIGN KEY (packaging_type_id) REFERENCES packaging_types(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: product, flavor, sugar_level, packaging_type
- Has Many: product_images, order_items, cart_items

---

### product_images
Product and variant images.

```sql
CREATE TABLE product_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NULL,
    product_variant_id BIGINT UNSIGNED NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) NULL,
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
);
```

**Relationships:**
- Belongs To: product, product_variant

---

### product_attributes
Product attributes (age group, nutritional need, etc.).

```sql
CREATE TABLE product_attributes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    attribute_type VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

**Relationships:**
- Belongs To: product

---

### flavors
Product flavors.

```sql
CREATE TABLE flavors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: product_variants

---

### sugar_levels
Sugar content levels.

```sql
CREATE TABLE sugar_levels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: product_variants

---

### packaging_types
Product packaging types.

```sql
CREATE TABLE packaging_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: product_variants

---

### age_groups
Target age groups.

```sql
CREATE TABLE age_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    min_age INT NULL,
    max_age INT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: products (via product_attributes)

---

### nutritional_needs
Nutritional requirements.

```sql
CREATE TABLE nutritional_needs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: products (via product_attributes)

---

### certificates
Product certifications.

```sql
CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: products (via pivot table)

---

### volumes
Product volumes.

```sql
CREATE TABLE volumes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    volume_ml INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: product_variants

---

## Order Management Tables

### orders
Customer orders.

```sql
CREATE TABLE orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    subtotal DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    shipping_fee DECIMAL(10, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'VND',
    
    -- Shipping Information
    shipping_address_id BIGINT UNSIGNED NULL,
    recipient_name VARCHAR(255) NOT NULL,
    recipient_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_province VARCHAR(100) NOT NULL,
    shipping_district VARCHAR(100) NOT NULL,
    shipping_ward VARCHAR(100) NOT NULL,
    
    -- Delivery Information
    shipping_method_id BIGINT UNSIGNED NULL,
    shipping_carrier VARCHAR(100) NULL,
    tracking_number VARCHAR(100) NULL,
    estimated_delivery_date DATE NULL,
    actual_delivery_date DATE NULL,
    
    -- Payment Information
    payment_method_id BIGINT UNSIGNED NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    
    -- Customer Notes
    customer_notes TEXT NULL,
    admin_notes TEXT NULL,
    
    -- Promotion
    coupon_code VARCHAR(50) NULL,
    voucher_id BIGINT UNSIGNED NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (shipping_address_id) REFERENCES addresses(id) ON DELETE SET NULL,
    FOREIGN KEY (shipping_method_id) REFERENCES shipping_methods(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: tenant, user, shipping_address, shipping_method, payment_method, voucher
- Has Many: order_items, order_status_logs, payments

---

### order_items
Items in an order.

```sql
CREATE TABLE order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100) NULL,
    variant_name VARCHAR(255) NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: order, product, product_variant

---

### order_status_logs
Order status change history.

```sql
CREATE TABLE order_status_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    from_status VARCHAR(50) NULL,
    to_status VARCHAR(50) NOT NULL,
    changed_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: order, user (changed_by)

---

### carts
Shopping carts.

```sql
CREATE TABLE carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(255) NULL,
    tenant_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: user, tenant
- Has Many: cart_items

---

### cart_items
Items in shopping cart.

```sql
CREATE TABLE cart_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: cart, product, product_variant

---

### wishlists
User wishlists.

```sql
CREATE TABLE wishlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);
```

**Relationships:**
- Belongs To: user, product

---

## Payment & Logistics Tables

### payments
Payment transactions.

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT UNSIGNED NOT NULL,
    payment_method_id BIGINT UNSIGNED NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'VND',
    status ENUM('pending', 'processing', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(255) NULL,
    gateway_response JSON NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: order, payment_method
- Has Many: payment_logs

---

### payment_logs
Payment transaction logs.

```sql
CREATE TABLE payment_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    request_data JSON NULL,
    response_data JSON NULL,
    status_code INT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
);
```

**Relationships:**
- Belongs To: payment

---

### payment_methods
Available payment methods.

```sql
CREATE TABLE payment_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: orders, payments

---

### shipping_methods
Available shipping methods.

```sql
CREATE TABLE shipping_methods (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    carrier VARCHAR(100) NULL,
    description TEXT NULL,
    base_fee DECIMAL(10, 2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: orders

---

### stores
Physical store locations.

```sql
CREATE TABLE stores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    address TEXT NOT NULL,
    province VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    opening_hours JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: tenant
- Has Many: orders (as pickup location)

---

## Marketing & Promotion Tables

### coupons
Discount coupons.

```sql
CREATE TABLE coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    discount_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    discount_value DECIMAL(10, 2) NOT NULL,
    min_order_value DECIMAL(10, 2) DEFAULT 0,
    max_discount_amount DECIMAL(10, 2) NULL,
    usage_limit INT NULL,
    used_count INT DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: orders

---

### vouchers
Gift vouchers.

```sql
CREATE TABLE vouchers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    value DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'VND',
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    usage_limit INT NULL,
    used_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: orders

---

### promotion_campaigns
Marketing campaigns.

```sql
CREATE TABLE promotion_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: promotion_flash_sales, promotion_banners

---

### promotion_flash_sales
Flash sale events.

```sql
CREATE TABLE promotion_flash_sales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (campaign_id) REFERENCES promotion_campaigns(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: promotion_campaign
- Has Many: product_variants (via pivot)

---

### promotion_banners
Promotional banners.

```sql
CREATE TABLE promotion_banners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    link_url VARCHAR(500) NULL,
    position VARCHAR(50) NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- None (standalone)

---

### marketing_rules
Advanced marketing rules engine.

```sql
CREATE TABLE marketing_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    rule_type ENUM('discount', 'free_shipping', 'gift', 'buy_x_get_y') NOT NULL,
    conditions JSON NOT NULL,
    rewards JSON NOT NULL,
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    valid_from DATE NULL,
    valid_until DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: marketing_rule_conditions, marketing_rule_rewards, marketing_rule_user_usages

---

### marketing_gifts
Marketing gifts.

```sql
CREATE TABLE marketing_gifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    stock_quantity INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: marketing_rule_rewards

---

### rewards
Customer reward points.

```sql
CREATE TABLE rewards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    points INT DEFAULT 0,
    level VARCHAR(50) DEFAULT 'bronze',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Relationships:**
- Belongs To: user
- Has Many: reward_redemptions

---

### reward_redemptions
Reward redemption history.

```sql
CREATE TABLE reward_redemptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reward_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    points_used INT NOT NULL,
    reward_type VARCHAR(50) NOT NULL,
    reward_value DECIMAL(10, 2) NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Relationships:**
- Belongs To: reward, user

---

## Content Management Tables

### blog_posts
Blog articles.

```sql
CREATE TABLE blog_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    author_id BIGINT UNSIGNED NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    seo_title VARCHAR(255) NULL,
    seo_description VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: blog_category, user (author)
- Has Many: blog_tags (via pivot)

---

### blog_categories
Blog categorization.

```sql
CREATE TABLE blog_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (parent_id) REFERENCES blog_categories(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: parent (self)
- Has Many: children (self), blog_posts

---

### banners
Website banners.

```sql
CREATE TABLE banners (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    link_url VARCHAR(500) NULL,
    position VARCHAR(50) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- None (standalone)

---

### mega_menus
Mega menu structure.

```sql
CREATE TABLE mega_menus (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    link_url VARCHAR(500) NULL,
    icon VARCHAR(255) NULL,
    mega_menu_content JSON NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (parent_id) REFERENCES mega_menus(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: parent (self)
- Has Many: children (self)

---

## Admin & Permission Tables

### admin_users
Admin user accounts (separate from regular users).

```sql
CREATE TABLE admin_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Belongs To Many: roles (Spatie)
- Has Many: activity_logs

---

### roles (Spatie)
User roles for permission management.

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Belongs To Many: permissions, users, admin_users

---

### permissions (Spatie)
Granular permissions.

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Belongs To Many: roles

---

### role_has_permissions (Spatie)
Role-permission pivot table.

```sql
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);
```

---

### model_has_permissions (Spatie)
Model-permission pivot table.

```sql
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, model_id, model_type)
);
```

---

### model_has_roles (Spatie)
Model-role pivot table.

```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, model_id, model_type)
);
```

---

## Care & Subscription Tables

### care_products
Care program products.

```sql
CREATE TABLE care_products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    price DECIMAL(10, 2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- Has Many: care_subscriptions

---

### care_subscriptions
Customer care subscriptions.

```sql
CREATE TABLE care_subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    care_product_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('active', 'paused', 'cancelled', 'expired') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (care_product_id) REFERENCES care_products(id) ON DELETE RESTRICT
);
```

**Relationships:**
- Belongs To: user, care_product

---

### care_delivery_options
Care delivery options.

```sql
CREATE TABLE care_delivery_options (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10, 2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- None (standalone)

---

### care_greeting_cards
Care greeting cards.

```sql
CREATE TABLE care_greeting_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL,
    message_template TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- None (standalone)

---

### care_page_settings
Care page configuration.

```sql
CREATE TABLE care_page_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- None (standalone)

---

## Activity & Logging Tables

### activity_logs
User activity tracking.

```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(50) NOT NULL,
    resource_type VARCHAR(255) NOT NULL,
    resource_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    url VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: user

---

### audit_logs
System audit logs.

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    event_type VARCHAR(100) NOT NULL,
    description TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: user

---

### chat_messages
AI chat messages.

```sql
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(255) NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

**Relationships:**
- Belongs To: user

---

### chat_knowledge
AI chat knowledge base.

```sql
CREATE TABLE chat_knowledge (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(255) NULL,
    priority INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- None (standalone)

---

### chat_settings
Chat configuration.

```sql
CREATE TABLE chat_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Relationships:**
- None (standalone)

---

## Entity Relationship Diagram

### Core Relationships

```
tenants (1) ----< (N) users
users (1) ----< (N) orders
users (1) ----< (N) addresses
users (1) ----< (N) cart_items
users (1) ----< (N) wishlists

categories (1) ----< (N) products
brands (1) ----< (N) products
product_lines (1) ----< (N) products

products (1) ----< (N) product_variants
product_variants (1) ----< (N) product_images
product_variants (1) ----< (N) order_items
product_variants (1) ----< (N) cart_items

orders (1) ----< (N) order_items
orders (1) ----< (N) payments
orders (1) ----< (N) order_status_logs

roles (M) ----< (M) permissions
admin_users (M) ----< (M) roles
users (M) ----< (M) roles
```

### Detailed ERD

For a visual ERD diagram, use tools like:
- MySQL Workbench
- dbdiagram.io
- Lucidchart
- Draw.io

---

## Indexing Strategy

### Primary Indexes
- All tables have primary key `id`

### Foreign Key Indexes
- All foreign key columns are automatically indexed

### Additional Indexes
- **users:** email (unique), phone
- **products:** slug (unique), sku (unique), category_id, brand_id
- **product_variants:** sku (unique), product_id, is_active
- **orders:** order_number (unique), user_id, status, created_at
- **order_items:** order_id, product_id
- **activity_logs:** user_id, resource_type, created_at

---

## Data Integrity Constraints

### Foreign Key Constraints
- All foreign keys have ON DELETE CASCADE or SET NULL
- Referential integrity enforced at database level

### Unique Constraints
- emails, slugs, SKUs, order numbers are unique
- Composite unique constraints where needed

### Check Constraints
- Enum values validated at database level
- Numeric ranges validated

---

## Performance Considerations

### Query Optimization
- Use indexes for frequently queried columns
- Avoid SELECT * in production
- Use eager loading to prevent N+1 queries
- Implement query caching for read-heavy operations

### Partitioning Strategy (Future)
- Partition orders by date range
- Partition activity_logs by date range
- Partition chat_messages by date range

### Archival Strategy
- Archive old orders (> 2 years)
- Archive old activity logs (> 1 year)
- Archive old chat messages (> 6 months)

---

## Backup & Recovery

### Backup Strategy
- Daily full backups
- Hourly incremental backups
- Real-time replication to standby server

### Recovery Plan
- Point-in-time recovery enabled
- Backup retention: 30 days
- Off-site backup storage (S3)

---

## Migration Strategy

### Version Control
- All schema changes in migration files
- Rollback capability for all migrations
- Migration testing in staging environment

### Data Migration
- Use seeders for reference data
- Use custom migration scripts for data transformation
- Backup before major migrations
