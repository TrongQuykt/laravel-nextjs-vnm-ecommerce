# Vinamilk Core Ecommerce - Test Case Documentation

## Table of Contents
1. [Testing Strategy](#testing-strategy)
2. [Unit Test Cases](#unit-test-cases)
3. [Integration Test Cases](#integration-test-cases)
4. [End-to-End Test Cases](#end-to-end-test-cases)
5. [API Test Cases](#api-test-cases)
6. [Performance Test Cases](#performance-test-cases)
7. [Security Test Cases](#security-test-cases)
8. [Test Data Management](#test-data-management)
9. [Test Execution](#test-execution)

---

## Testing Strategy

### Testing Pyramid
```
        /\
       /E2E\        - 10% (End-to-End)
      /------\
     /Integration\  - 30% (Integration)
    /------------\
   /   Unit Tests  \ - 60% (Unit)
  /----------------\
```

### Testing Tools
- **Backend:** PHPUnit, Pest PHP
- **Frontend:** Jest, React Testing Library, Playwright
- **API:** Postman, Newman
- **Performance:** JMeter, k6
- **Security:** OWASP ZAP, Burp Suite

### Test Coverage Goals
- **Critical Features:** 90%+ coverage
- **Important Features:** 80%+ coverage
- **Optional Features:** 70%+ coverage

---

## Unit Test Cases

### Backend Unit Tests

#### User Service Tests
```php
class UserServiceTest extends TestCase
{
    public function test_it_can_create_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
        
        $user = $this->userService->createUser($data);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
        $this->assertEquals('John Doe', $user->name);
    }
    
    public function test_it_validates_email_uniqueness()
    {
        User::factory()->create(['email' => 'john@example.com']);
        
        $data = [
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
        
        $this->expectException(ValidationException::class);
        $this->userService->createUser($data);
    }
    
    public function test_it_hashes_password()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];
        
        $user = $this->userService->createUser($data);
        
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
```

#### Product Service Tests
```php
class ProductServiceTest extends TestCase
{
    public function test_it_can_create_product()
    {
        $category = Category::factory()->create();
        $data = [
            'name' => 'Vinamilk 100%',
            'slug' => 'vinamilk-100',
            'price' => 25000,
            'category_id' => $category->id,
        ];
        
        $product = $this->productService->createProduct($data);
        
        $this->assertDatabaseHas('products', [
            'slug' => 'vinamilk-100',
        ]);
        $this->assertEquals(25000, $product->price);
    }
    
    public function test_it_can_update_product()
    {
        $product = Product::factory()->create();
        $data = ['price' => 30000];
        
        $updated = $this->productService->updateProduct($product->id, $data);
        
        $this->assertEquals(30000, $updated->price);
    }
    
    public function test_it_can_delete_product()
    {
        $product = Product::factory()->create();
        
        $this->productService->deleteProduct($product->id);
        
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
    
    public function test_it_can_get_active_products()
    {
        Product::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => false]);
        
        $products = $this->productService->getActiveProducts();
        
        $this->assertCount(1, $products);
        $this->assertTrue($products->first()->is_active);
    }
}
```

#### Order Service Tests
```php
class OrderServiceTest extends TestCase
{
    public function test_it_can_create_order()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create(['cart_id' => $cart->id]);
        
        $order = $this->orderService->createOrderFromCart($user->id);
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }
    
    public function test_it_calculates_order_total()
    {
        $order = Order::factory()->create([
            'subtotal' => 100000,
            'discount' => 10000,
            'shipping_fee' => 20000,
        ]);
        
        $total = $this->orderService->calculateOrderTotal($order);
        
        $this->assertEquals(110000, $total);
    }
    
    public function test_it_can_update_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $updated = $this->orderService->updateOrderStatus($order->id, 'confirmed');
        
        $this->assertEquals('confirmed', $updated->status);
    }
    
    public function test_it_logs_status_change()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $this->orderService->updateOrderStatus($order->id, 'confirmed');
        
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'to_status' => 'confirmed',
        ]);
    }
}
```

#### Cart Service Tests
```php
class CartServiceTest extends TestCase
{
    public function test_it_can_add_item_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $item = $this->cartService->addToCart($user->id, [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }
    
    public function test_it_updates_quantity_if_item_exists()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        
        $this->cartService->addToCart($user->id, [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }
    
    public function it_can_remove_item_from_cart()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $item = CartItem::factory()->create(['cart_id' => $cart->id]);
        
        $this->cartService->removeFromCart($user->id, $item->id);
        
        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }
}
```

### Frontend Unit Tests

#### Component Tests
```typescript
describe('ProductCard', () => {
  it('renders product information', () => {
    const product = {
      id: 1,
      name: 'Vinamilk 100%',
      price: 25000,
      mainImage: '/product.jpg',
    };
    
    render(<ProductCard product={product} />);
    
    expect(screen.getByText('Vinamilk 100%')).toBeInTheDocument();
    expect(screen.getByText('25.000 đ')).toBeInTheDocument();
  });
  
  it('calls onAddToCart when button is clicked', () => {
    const onAddToCart = jest.fn();
    const product = {
      id: 1,
      name: 'Vinamilk 100%',
      price: 25000,
    };
    
    render(<ProductCard product={product} onAddToCart={onAddToCart} />);
    
    fireEvent.click(screen.getByText('Thêm vào giỏ'));
    
    expect(onAddToCart).toHaveBeenCalledWith(product);
  });
  
  it('displays discount badge when compare price is higher', () => {
    const product = {
      id: 1,
      name: 'Vinamilk 100%',
      price: 25000,
      comparePrice: 30000,
    };
    
    render(<ProductCard product={product} />);
    
    expect(screen.getByText('Sale')).toBeInTheDocument();
  });
});
```

#### Hook Tests
```typescript
describe('useCart', () => {
  it('initializes with empty cart', () => {
    const { result } = renderHook(() => useCart());
    
    expect(result.current.items).toEqual([]);
    expect(result.current.total).toBe(0);
  });
  
  it('adds item to cart', () => {
    const { result } = renderHook(() => useCart());
    
    act(() => {
      result.current.addItem({
        product_id: 1,
        quantity: 2,
        price: 25000,
      });
    });
    
    expect(result.current.items).toHaveLength(1);
    expect(result.current.total).toBe(50000);
  });
  
  it('removes item from cart', () => {
    const { result } = renderHook(() => useCart());
    
    act(() => {
      result.current.addItem({
        product_id: 1,
        quantity: 2,
        price: 25000,
      });
    });
    
    act(() => {
      result.current.removeItem(1);
    });
    
    expect(result.current.items).toHaveLength(0);
  });
});
```

---

## Integration Test Cases

### Backend Integration Tests

#### Order Flow Integration Test
```php
class OrderFlowIntegrationTest extends TestCase
{
    public function test_complete_order_flow()
    {
        // Create user
        $user = User::factory()->create();
        
        // Add products to cart
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['price' => 25000]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        
        // Create order
        $order = $this->orderService->createOrderFromCart($user->id);
        
        // Verify order created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        
        // Verify order items
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        
        // Verify cart cleared
        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
        ]);
        
        // Verify order total
        $this->assertEquals(50000, $order->total);
    }
}
```

#### Payment Integration Test
```php
class PaymentIntegrationTest extends TestCase
{
    public function test_payment_flow()
    {
        $order = Order::factory()->create([
            'total' => 100000,
            'status' => 'pending',
        ]);
        
        // Process payment
        $payment = $this->paymentService->processPayment($order, [
            'method' => 'vnpay',
            'amount' => 100000,
        ]);
        
        // Verify payment created
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'amount' => 100000,
        ]);
        
        // Verify order status updated
        $this->assertEquals('paid', $payment->status);
    }
}
```

### Frontend Integration Tests

#### Checkout Flow Integration Test
```typescript
describe('Checkout Flow', () => {
  it('completes checkout successfully', async () => {
    const user = userEvent.setup();
    
    // Mock API calls
    mockApi.post('/cart/evaluate').mockResolvedValue({
      data: { total: 100000 },
    });
    mockApi.post('/orders').mockResolvedValue({
      data: { order_number: 'ORD-001' },
    });
    
    render(<CheckoutPage />);
    
    // Fill shipping address
    await user.type(screen.getByLabelText('Tên'), 'John Doe');
    await user.type(screen.getByLabelText('Số điện thoại'), '0901234567');
    await user.type(screen.getByLabelText('Địa chỉ'), '123 Đường ABC');
    
    // Select shipping method
    await user.click(screen.getByLabelText('Giao hàng tiêu chuẩn'));
    
    // Select payment method
    await user.click(screen.getByLabelText('Thanh toán khi nhận hàng'));
    
    // Confirm order
    await user.click(screen.getByText('Đặt hàng'));
    
    // Verify order created
    await waitFor(() => {
      expect(screen.getByText('Đặt hàng thành công')).toBeInTheDocument();
    });
  });
});
```

---

## End-to-End Test Cases

### E2E Test Scenarios

#### User Registration and First Purchase
```typescript
test('user can register and make first purchase', async ({ page }) => {
  // Navigate to registration page
  await page.goto('/register');
  
  // Fill registration form
  await page.fill('input[name="name"]', 'John Doe');
  await page.fill('input[name="email"]', 'john@example.com');
  await page.fill('input[name="password"]', 'password123');
  await page.fill('input[name="password_confirmation"]', 'password123');
  
  // Submit registration
  await page.click('button[type="submit"]');
  
  // Verify registration successful
  await expect(page).toHaveURL('/');
  
  // Browse products
  await page.goto('/products');
  await page.click('.product-card:first-child');
  
  // Add to cart
  await page.click('button:has-text("Thêm vào giỏ")');
  
  // Verify cart updated
  await expect(page.locator('.cart-count')).toHaveText('1');
  
  // Proceed to checkout
  await page.goto('/cart');
  await page.click('button:has-text("Thanh toán")');
  
  // Fill shipping address
  await page.fill('input[name="recipient_name"]', 'John Doe');
  await page.fill('input[name="phone"]', '0901234567');
  await page.fill('input[name="address"]', '123 Đường ABC');
  
  // Select shipping method
  await page.click('input[value="standard"]');
  
  // Confirm order
  await page.click('button:has-text("Đặt hàng")');
  
  // Verify order success
  await expect(page.locator('text=Đặt hàng thành công')).toBeVisible();
});
```

#### Order Tracking Flow
```typescript
test('user can track order status', async ({ page }) => {
  // Login
  await page.goto('/login');
  await page.fill('input[name="email"]', 'john@example.com');
  await page.fill('input[name="password"]', 'password123');
  await page.click('button[type="submit"]');
  
  // Navigate to orders
  await page.goto('/account/orders');
  
  // Click on first order
  await page.click('.order-item:first-child');
  
  // Verify order details visible
  await expect(page.locator('.order-number')).toBeVisible();
  await expect(page.locator('.order-status')).toBeVisible();
  
  // Click track order
  await page.click('button:has-text("Theo dõi")');
  
  // Verify tracking information visible
  await expect(page.locator('.tracking-timeline')).toBeVisible();
});
```

#### Admin Order Processing Flow
```typescript
test('admin can process order', async ({ page }) => {
  // Login as admin
  await page.goto('/admin/login');
  await page.fill('input[name="email"]', 'admin@vinamilk.com');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');
  
  // Navigate to orders
  await page.goto('/admin/orders');
  
  // Click on pending order
  await page.click('.order-item[data-status="pending"]:first-child');
  
  // Confirm order
  await page.click('button:has-text("Xác nhận")');
  
  // Verify status updated
  await expect(page.locator('.order-status')).toHaveText('confirmed');
  
  // Process payment
  await page.click('button:has-text("Xử lý thanh toán")');
  
  // Verify payment processed
  await expect(page.locator('.payment-status')).toHaveText('paid');
  
  // Generate shipping label
  await page.click('button:has-text("Tạo vận đơn")');
  
  // Verify shipping label generated
  await expect(page.locator('.tracking-number')).toBeVisible();
  
  // Mark as shipped
  await page.click('button:has-text("Giao hàng")');
  
  // Verify status updated
  await expect(page.locator('.order-status')).toHaveText('shipped');
});
```

---

## API Test Cases

### API Endpoint Tests

#### Authentication API Tests
```typescript
describe('Authentication API', () => {
  it('should login with valid credentials', async () => {
    const response = await api.post('/login', {
      email: 'john@example.com',
      password: 'password123',
    });
    
    expect(response.status).toBe(200);
    expect(response.data.success).toBe(true);
    expect(response.data.data.token).toBeDefined();
  });
  
  it('should fail login with invalid credentials', async () => {
    const response = await api.post('/login', {
      email: 'john@example.com',
      password: 'wrongpassword',
    });
    
    expect(response.status).toBe(401);
    expect(response.data.success).toBe(false);
  });
  
  it('should register new user', async () => {
    const response = await api.post('/register', {
      name: 'John Doe',
      email: 'john@example.com',
      password: 'password123',
      password_confirmation: 'password123',
    });
    
    expect(response.status).toBe(201);
    expect(response.data.success).toBe(true);
  });
});
```

#### Product API Tests
```typescript
describe('Product API', () => {
  it('should get all products', async () => {
    const response = await api.get('/catalog');
    
    expect(response.status).toBe(200);
    expect(response.data.success).toBe(true);
    expect(Array.isArray(response.data.data.products)).toBe(true);
  });
  
  it('should get single product', async () => {
    const response = await api.get('/products/vinamilk-100');
    
    expect(response.status).toBe(200);
    expect(response.data.success).toBe(true);
    expect(response.data.data.id).toBeDefined();
  });
  
  it('should filter products by category', async () => {
    const response = await api.get('/catalog?category=sua-tuoi');
    
    expect(response.status).toBe(200);
    expect(response.data.success).toBe(true);
  });
  
  it('should search products', async () => {
    const response = await api.get('/search?q=vinamilk');
    
    expect(response.status).toBe(200);
    expect(response.data.success).toBe(true);
  });
});
```

#### Order API Tests
```typescript
describe('Order API', () => {
  it('should create order', async () => {
    const response = await api.post('/orders', {
      shipping_address_id: 1,
      shipping_method_id: 1,
      payment_method_id: 1,
    }, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    
    expect(response.status).toBe(201);
    expect(response.data.success).toBe(true);
    expect(response.data.data.order_number).toBeDefined();
  });
  
  it('should get user orders', async () => {
    const response = await api.get('/orders', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    
    expect(response.status).toBe(200);
    expect(response.data.success).toBe(true);
  });
  
  it('should get order detail', async () => {
    const response = await api.get('/orders/ORD-001', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });
    
    expect(response.status).toBe(200);
    expect(response.data.success).toBe(true);
  });
});
```

---

## Performance Test Cases

### Load Testing Scenarios

#### Homepage Load Test
```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '2m', target: 100 }, // Ramp up to 100 users
    { duration: '5m', target: 100 }, // Stay at 100 users
    { duration: '2m', target: 200 }, // Ramp up to 200 users
    { duration: '5m', target: 200 }, // Stay at 200 users
    { duration: '2m', target: 0 },   // Ramp down to 0 users
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
    http_req_failed: ['rate<0.01'],   // Error rate must be less than 1%
  },
};

export default function () {
  let res = http.get('https://vinamilk.com');
  check(res, {
    'status is 200': (r) => r.status === 200,
    'response time < 500ms': (r) => r.timings.duration < 500,
  });
  sleep(1);
}
```

#### Checkout Load Test
```javascript
export default function () {
  // Login
  let loginRes = http.post('https://api.vinamilk.com/api/v1/login', {
    email: 'user@example.com',
    password: 'password123',
  });
  let token = loginRes.json('data.token');
  
  // Add to cart
  http.post('https://api.vinamilk.com/api/v1/cart', {
    product_id: 1,
    quantity: 2,
  }, {
    headers: { Authorization: `Bearer ${token}` },
  });
  
  // Create order
  let orderRes = http.post('https://api.vinamilk.com/api/v1/orders', {
    shipping_address_id: 1,
    shipping_method_id: 1,
    payment_method_id: 1,
  }, {
    headers: { Authorization: `Bearer ${token}` },
  });
  
  check(orderRes, {
    'order created': (r) => r.status === 201,
  });
}
```

### Performance Benchmarks

#### Response Time Targets
- **Homepage:** < 2s
- **Product Page:** < 1.5s
- **Cart Page:** < 1s
- **Checkout:** < 2s
- **API Endpoints:** < 500ms (p95)

#### Throughput Targets
- **Concurrent Users:** 10,000
- **Requests/Second:** 1,000
- **Orders/Minute:** 100

---

## Security Test Cases

### Authentication Security Tests

#### SQL Injection Test
```php
class SecurityTest extends TestCase
{
    public function test_prevents_sql_injection_in_login()
    {
        $response = $this->post('/login', [
            'email' => "' OR '1'='1",
            'password' => 'anything',
        ]);
        
        $response->assertStatus(401);
    }
    
    public function test_prevents_sql_injection_in_search()
    {
        $response = $this->get('/search?q=vinamilk\' OR \'1\'=\'1');
        
        $response->assertStatus(200);
        $this->assertEmpty($response->json('data.products'));
    }
}
```

#### XSS Prevention Test
```php
public function test_prevents_xss_in_product_name()
{
    $response = $this->post('/products', [
        'name' => '<script>alert("XSS")</script>',
        'price' => 25000,
    ]);
    
    $this->assertDatabaseHas('products', [
        'name' => '<script>alert("XSS")</script>',
    ]);
    
    // Verify script not executed in response
    $this->assertStringNotContainsString('<script>', $response->getContent());
}
```

#### CSRF Protection Test
```php
public function test_requires_csrf_token_for_post_requests()
{
    $response = $this->post('/products', [
        'name' => 'Test Product',
        'price' => 25000,
    ]);
    
    $response->assertStatus(419); // CSRF token mismatch
}
```

### Authorization Security Tests

#### Role-Based Access Control Test
```php
class AuthorizationTest extends TestCase
{
    public function test_customer_cannot_access_admin_panel()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->get('/admin/products');
        
        $response->assertStatus(403);
    }
    
    public function test_admin_can_access_admin_panel()
    {
        $admin = AdminUser::factory()->create();
        $admin->assignRole('Super Admin');
        $this->actingAs($admin);
        
        $response = $this->get('/admin/products');
        
        $response->assertStatus(200);
    }
}
```

### Rate Limiting Test
```php
public function test_rate_limiting_on_api()
{
    // Make 100 requests in quick succession
    for ($i = 0; $i < 100; $i++) {
        $response = $this->get('/api/v1/products');
        
        if ($i < 60) {
            $response->assertStatus(200);
        } else {
            $response->assertStatus(429);
        }
    }
}
```

---

## Test Data Management

### Factories

#### User Factory
```php
class UserFactory extends Factory
{
    protected $model = User::class;
    
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
    
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
```

#### Product Factory
```php
class ProductFactory extends Factory
{
    protected $model = Product::class;
    
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'price' => fake()->randomFloat(2, 10000, 500000),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'is_featured' => false,
        ];
    }
    
    public function featured(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
```

#### Order Factory
```php
class OrderFactory extends Factory
{
    protected $model = Order::class;
    
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . fake()->unique()->randomNumber(6),
            'status' => fake()->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered']),
            'subtotal' => fake()->randomFloat(2, 100000, 1000000),
            'discount' => fake()->randomFloat(2, 0, 100000),
            'shipping_fee' => fake()->randomFloat(2, 10000, 50000),
            'total' => fake()->randomFloat(2, 100000, 1000000),
        ];
    }
    
    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
```

### Seeders

#### Development Seeder
```php
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        User::factory(10)->create();
        
        // Create categories
        Category::factory(5)->create();
        
        // Create products
        Product::factory(50)->create();
        
        // Create orders
        Order::factory(20)->create();
    }
}
```

---

## Test Execution

### Running Tests

#### Backend Tests
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/UserServiceTest.php

# Run specific test method
php artisan test --filter test_it_can_create_user

# Run with coverage
php artisan test --coverage

# Run in parallel
php artisan test --parallel
```

#### Frontend Tests
```bash
# Run all tests
npm test

# Run specific test file
npm test ProductCard.test.tsx

# Run with coverage
npm test -- --coverage

# Run in watch mode
npm test -- --watch
```

#### E2E Tests
```bash
# Run all E2E tests
npx playwright test

# Run specific test file
npx playwright test checkout.spec.ts

# Run headed mode
npx playwright test --headed

# Run with UI
npx playwright test --ui
```

### CI/CD Integration

#### GitHub Actions Example
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: vinamilk_test
        ports:
          - 3306:3306
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo_mysql
      
      - name: Install Dependencies
        run: composer install --no-progress --no-interaction
      
      - name: Run Tests
        run: php artisan test --coverage
      
      - name: Upload Coverage
        uses: codecov/codecov-action@v2
```

---

## Test Reporting

### Coverage Reports

#### Backend Coverage
```bash
php artisan test --coverage --coverage-html=coverage
```

#### Frontend Coverage
```bash
npm test -- --coverage --coverageReporters=html
```

### Test Reports

#### PHPUnit Report
```xml
<testsuites>
  <testsuite name="UserServiceTest" tests="5" failures="0" errors="0" time="0.5">
    <testcase name="test_it_can_create_user" time="0.1"/>
    <testcase name="test_it_validates_email_uniqueness" time="0.15"/>
    <testcase name="test_it_hashes_password" time="0.1"/>
    <testcase name="test_it_can_update_user" time="0.1"/>
    <testcase name="test_it_can_delete_user" time="0.05"/>
  </testsuite>
</testsuites>
```

---

## Best Practices

### Test Organization
- **Arrange-Act-Assert:** Structure tests clearly
- **Descriptive Names:** Use descriptive test names
- **Single Responsibility:** One assertion per test
- **Independent Tests:** Tests should not depend on each other
- **Fast Tests:** Keep tests fast and focused

### Test Maintenance
- **Regular Updates:** Update tests with code changes
- **Refactor Tests:** Refactor tests when needed
- **Remove Obsolete Tests:** Remove tests for deprecated features
- **Document Tests:** Document complex test scenarios

### Continuous Testing
- **Pre-commit Hooks:** Run tests before committing
- **CI/CD Pipeline:** Run tests in CI/CD
- **Automated Testing:** Automate test execution
- **Test Alerts:** Alert on test failures

---

## Troubleshooting

### Common Issues

#### Flaky Tests
**Issue:** Tests pass sometimes, fail sometimes
**Solution:** 
- Add proper waits
- Use deterministic test data
- Avoid time-based assertions
- Isolate test dependencies

#### Slow Tests
**Issue:** Tests take too long to run
**Solution:**
- Use in-memory database
- Mock external services
- Parallelize test execution
- Optimize test data

#### Test Environment Issues
**Issue:** Tests fail in CI but pass locally
**Solution:**
- Ensure consistent environments
- Use Docker for consistency
- Check CI configuration
- Verify environment variables

---

## Test Metrics

### Coverage Targets
- **Overall Coverage:** 80%+
- **Critical Code:** 95%+
- **Business Logic:** 90%+
- **UI Components:** 80%+

### Performance Targets
- **Test Execution Time:** < 10 minutes
- **Unit Tests:** < 2 minutes
- **Integration Tests:** < 5 minutes
- **E2E Tests:** < 10 minutes

### Quality Targets
- **Test Pass Rate:** 95%+
- **Flaky Test Rate:** < 5%
- **Test Maintenance Time:** < 2 hours/week

---

## Future Enhancements

### Planned Test Improvements
- [ ] Implement visual regression testing
- [ ] Add accessibility testing
- [ ] Implement contract testing
- [ ] Add chaos engineering tests
- [ ] Implement mutation testing
- [ ] Add performance regression testing

### Test Automation
- [ ] Automated test scheduling
- [ ] Automated test reporting
- [ ] Automated test data generation
- [ ] Automated test environment provisioning
