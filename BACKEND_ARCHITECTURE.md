# Vinamilk Core Ecommerce - Backend Architecture Documentation

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Directory Structure](#directory-structure)
3. [Design Patterns](#design-patterns)
4. [Layered Architecture](#layered-architecture)
5. [Service Layer](#service-layer)
6. [Controller Layer](#controller-layer)
7. [Middleware](#middleware)
8. [Event System](#event-system)
9. [Queue System](#queue-system)
10. [Cache Strategy](#cache-strategy)
11. [API Design](#api-design)
12. [Admin Panel Architecture](#admin-panel-architecture)
13. [Security Implementation](#security-implementation)
14. [Performance Optimization](#performance-optimization)

---

## Architecture Overview

### Technology Stack
- **Framework:** Laravel 10.x
- **PHP Version:** 8.2+
- **Database:** MySQL 8.0+
- **Cache:** Redis 7.x
- **Queue:** Redis + Laravel Horizon
- **Admin Panel:** Filament PHP 3.x
- **Authentication:** Laravel Sanctum (API) + Session (Admin)

### Architecture Pattern
- **Primary Pattern:** MVC with Service Layer
- **Secondary Patterns:** Repository, Factory, Observer, Strategy
- **API Style:** RESTful with JSON responses
- **Admin Panel:** Resource-based with Filament

### Key Principles
- **Separation of Concerns:** Clear separation between layers
- **SOLID Principles:** Single Responsibility, Dependency Injection
- **DRY:** Don't Repeat Yourself - reusable components
- **KISS:** Keep It Simple, Stupid
- **Testability:** Code designed for easy testing

---

## Directory Structure

```
app/
├── Console/
│   └── Commands/          # Artisan commands
├── Events/                # Domain events
├── Exports/               # Export classes
├── Filament/              # Admin panel resources
│   ├── Pages/            # Admin pages
│   ├── Resources/        # Admin resources
│   ├── Traits/           # Reusable traits
│   └── Widgets/          # Dashboard widgets
├── Http/
│   ├── Controllers/      # API controllers
│   ├── Middleware/       # HTTP middleware
│   ├── Requests/         # Form request validation
│   └── Resources/        # API resource transformers
├── Jobs/                 # Queue jobs
├── Mail/                 # Email classes
├── Models/               # Eloquent models
├── Observers/            # Model observers
├── Providers/            # Service providers
├── Services/             # Business logic layer
└── Traits/               # Reusable traits
```

---

## Design Patterns

### 1. MVC Pattern (Model-View-Controller)
**Purpose:** Separation of concerns in web applications

**Implementation:**
- **Models:** `app/Models/` - Data access and business rules
- **Views:** `resources/views/` - Blade templates
- **Controllers:** `app/Http/Controllers/` - Request handling

**Example:**
```php
// Controller
public function index()
{
    $products = $this->productService->getAllActive();
    return ProductResource::collection($products);
}

// Service
public function getAllActive()
{
    return Product::where('is_active', true)
        ->with(['category', 'brand'])
        ->get();
}

// Model
class Product extends Model
{
    protected $fillable = ['name', 'price', 'is_active'];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### 2. Service Layer Pattern
**Purpose:** Encapsulate business logic separate from controllers

**Implementation:**
- **Location:** `app/Services/`
- **Usage:** Controllers delegate to services
- **Benefits:** Reusability, testability, single responsibility

**Example:**
```php
// OrderService
class OrderService
{
    public function createOrder(array $data): Order
    {
        DB::beginTransaction();
        try {
            $order = $this->createOrderRecord($data);
            $this->processOrderItems($order, $data['items']);
            $this->applyPromotions($order, $data);
            $this->calculateTotals($order);
            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

### 3. Repository Pattern
**Purpose:** Abstract data access logic

**Implementation:**
- **Location:** `app/Repositories/` (if implemented)
- **Usage:** Services use repositories for data access
- **Benefits:** Easy testing, interchangeable data sources

**Example:**
```php
interface ProductRepositoryInterface
{
    public function findAll(): Collection;
    public function findById(int $id): ?Product;
    public function create(array $data): Product;
}

class ProductRepository implements ProductRepositoryInterface
{
    public function findAll(): Collection
    {
        return Product::with(['category', 'brand'])->get();
    }
}
```

### 4. Observer Pattern
**Purpose:** React to model events automatically

**Implementation:**
- **Location:** `app/Observers/`
- **Usage:** Auto-registered via `AppServiceProvider`
- **Benefits:** Decoupled event handling

**Example:**
```php
// OrderObserver
class OrderObserver
{
    public function created(Order $order)
    {
        // Send confirmation email
        Mail::to($order->user)->send(new OrderConfirmation($order));
        
        // Log activity
        ActivityLogger::logCreate('Order', $order->id);
    }
    
    public function updated(Order $order)
    {
        if ($order->status === 'shipped') {
            // Send shipping notification
            $this->sendShippingNotification($order);
        }
    }
}
```

### 5. Factory Pattern
**Purpose:** Create objects without specifying exact class

**Implementation:**
- **Location:** `database/factories/`
- **Usage:** Testing and seeding
- **Benefits:** Consistent test data generation

**Example:**
```php
class ProductFactory extends Factory
{
    protected $model = Product::class;
    
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'price' => fake()->randomFloat(2, 10000, 500000),
            'is_active' => true,
        ];
    }
}
```

### 6. Strategy Pattern
**Purpose:** Encapsulate interchangeable algorithms

**Implementation:**
- **Location:** `app/Services/Payment/`
- **Usage:** Different payment gateways
- **Benefits:** Easy to add new payment methods

**Example:**
```php
interface PaymentStrategyInterface
{
    public function processPayment(array $data): PaymentResult;
}

class VNPayStrategy implements PaymentStrategyInterface
{
    public function processPayment(array $data): PaymentResult
    {
        // VNPay specific implementation
    }
}

class MoMoStrategy implements PaymentStrategyInterface
{
    public function processPayment(array $data): PaymentResult
    {
        // MoMo specific implementation
    }
}
```

---

## Layered Architecture

### 1. Presentation Layer (Controllers)
**Responsibility:** Handle HTTP requests, validation, responses

**Location:** `app/Http/Controllers/`

**Key Components:**
- API Controllers
- Form Request Validation
- API Resource Transformers
- Middleware

**Example:**
```php
class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}
    
    public function index(IndexProductRequest $request)
    {
        $products = $this->productService->getFilteredProducts(
            $request->validated()
        );
        return ProductResource::collection($products);
    }
    
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct(
            $request->validated()
        );
        return new ProductResource($product);
    }
}
```

### 2. Business Logic Layer (Services)
**Responsibility:** Core business logic, complex operations

**Location:** `app/Services/`

**Key Services:**
- ProductService
- OrderService
- PaymentService
- ShippingService
- PromotionService
- UserService
- CartService

**Example:**
```php
class OrderService
{
    public function __construct(
        private CartService $cartService,
        private PaymentService $paymentService,
        private ShippingService $shippingService,
        private PromotionService $promotionService
    ) {}
    
    public function createOrderFromCart(int $userId): Order
    {
        $cart = $this->cartService->getUserCart($userId);
        $order = $this->createOrderRecord($cart);
        $this->processOrderItems($order, $cart->items);
        $discount = $this->promotionService->applyPromotions($order);
        $this->calculateTotals($order, $discount);
        return $order;
    }
}
```

### 3. Data Access Layer (Models)
**Responsibility:** Database operations, relationships

**Location:** `app/Models/`

**Key Components:**
- Eloquent Models
- Model Relationships
- Query Scopes
- Accessors & Mutators

**Example:**
```php
class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'sku', 'price', 'is_active'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
```

### 4. Infrastructure Layer
**Responsibility:** External integrations, caching, queues

**Key Components:**
- Cache Management
- Queue Workers
- File Storage
- External API Clients
- Logging

---

## Service Layer

### Core Services

#### ProductService
**Responsibilities:**
- Product CRUD operations
- Product search and filtering
- Product variant management
- Inventory management
- Product indexing

**Key Methods:**
```php
class ProductService
{
    public function getAllProducts(array $filters): Collection
    public function getProductById(int $id): Product
    public function createProduct(array $data): Product
    public function updateProduct(int $id, array $data): Product
    public function deleteProduct(int $id): bool
    public function searchProducts(string $query): Collection
    public function getFeaturedProducts(int $limit): Collection
    public function updateStock(int $variantId, int $quantity): bool
}
```

#### OrderService
**Responsibilities:**
- Order creation and management
- Order status transitions
- Order calculation
- Order validation
- Order notifications

**Key Methods:**
```php
class OrderService
{
    public function createOrder(array $data): Order
    public function updateOrderStatus(int $orderId, string $status): Order
    public function calculateOrderTotal(Order $order): decimal
    public function applyDiscount(Order $order, string $code): Order
    public function cancelOrder(int $orderId): bool
    public function getOrderHistory(int $userId): Collection
}
```

#### PaymentService
**Responsibilities:**
- Payment processing
- Payment gateway integration
- Payment verification
- Refund processing
- Payment logging

**Key Methods:**
```php
class PaymentService
{
    public function processPayment(Order $order, array $paymentData): Payment
    public function verifyPayment(string $transactionId): bool
    public function processRefund(Payment $payment, decimal $amount): bool
    public function getPaymentMethods(): Collection
    public function handleWebhook(array $data): bool
}
```

#### CartService
**Responsibilities:**
- Cart management
- Cart item operations
- Cart calculations
- Cart persistence
- Cart synchronization

**Key Methods:**
```php
class CartService
{
    public function getUserCart(int $userId): Cart
    public function addToCart(int $userId, array $itemData): CartItem
    public function removeFromCart(int $cartItemId): bool
    public function updateCartItem(int $cartItemId, int $quantity): CartItem
    public function clearCart(int $userId): bool
    public function getCartTotal(Cart $cart): decimal
}
```

#### PromotionService
**Responsibilities:**
- Coupon validation
- Discount calculation
- Promotion application
- Marketing rules engine
- Reward point management

**Key Methods:**
```php
class PromotionService
{
    public function validateCoupon(string $code, int $userId): bool
    public function calculateDiscount(Order $order, string $code): decimal
    public function applyMarketingRules(Order $order): Order
    public function getAvailablePromotions(): Collection
    public function addRewardPoints(int $userId, int $points): bool
}
```

#### UserService
**Responsibilities:**
- User management
- Authentication
- User profile
- User addresses
- User preferences

**Key Methods:**
```php
class UserService
{
    public function createUser(array $data): User
    public function updateUser(int $userId, array $data): User
    public function deleteUser(int $userId): bool
    public function getUserAddresses(int $userId): Collection
    public function addUserAddress(int $userId, array $data): Address
    public function updateUserProfile(int $userId, array $data): User
}
```

---

## Controller Layer

### API Controllers Structure

#### Base Controller
```php
abstract class ApiController extends Controller
{
    protected function successResponse($data, string $message = 'Success', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
    
    protected function errorResponse(string $message, int $statusCode = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}
```

#### Product Controller
```php
class ProductController extends ApiController
{
    public function __construct(
        private ProductService $productService
    ) {}
    
    public function index(IndexProductRequest $request)
    {
        $products = $this->productService->getFilteredProducts(
            $request->validated()
        );
        return $this->successResponse(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }
    
    public function show(int $id)
    {
        $product = $this->productService->getProductById($id);
        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }
        return $this->successResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }
    
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct(
            $request->validated()
        );
        return $this->successResponse(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }
}
```

### Request Validation

#### Form Request Example
```php
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('create products');
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'price.required' => 'Product price is required',
        ];
    }
}
```

### API Resources

#### Product Resource
```php
class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'variants' => ProductVariantResource::collection(
                $this->whenLoaded('variants')
            ),
            'images' => ProductImageResource::collection(
                $this->whenLoaded('images')
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

---

## Middleware

### Custom Middleware

#### Tenant Middleware
```php
class SetTenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->resolveTenant($request);
        if ($tenant) {
            tenant()->set($tenant);
        }
        return $next($request);
    }
    
    private function resolveTenant(Request $request): ?Tenant
    {
        // Resolve tenant from domain, subdomain, or header
        $domain = $request->getHost();
        return Tenant::where('domain', $domain)->first();
    }
}
```

#### Rate Limiting Middleware
```php
class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
            ], 429);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        return $next($request);
    }
}
```

#### API Logging Middleware
```php
class ApiLoggingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        
        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration' => round($duration * 1000, 2) . 'ms',
            'status' => $response->status(),
        ]);
        
        return $response;
    }
}
```

---

## Event System

### Event Listeners

#### Order Created Event
```php
// Event
class OrderCreated
{
    public function __construct(
        public Order $order
    ) {}
}

// Listener
class SendOrderConfirmationEmail
{
    public function handle(OrderCreated $event)
    {
        Mail::to($event->order->user)->send(
            new OrderConfirmation($event->order)
        );
    }
}

// Registration in EventServiceProvider
protected $listen = [
    OrderCreated::class => [
        SendOrderConfirmationEmail::class,
        UpdateInventory::class,
        CreateActivityLog::class,
    ],
];
```

### Event Broadcasting
```php
class OrderStatusUpdated implements ShouldBroadcast
{
    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}
    
    public function broadcastOn()
    {
        return new PrivateChannel('orders.' . $this->order->user_id);
    }
    
    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

---

## Queue System

### Job Classes

#### ProcessPaymentJob
```php
class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public Order $order,
        public array $paymentData
    ) {}
    
    public function handle(PaymentService $paymentService)
    {
        try {
            $payment = $paymentService->processPayment(
                $this->order,
                $this->paymentData
            );
            
            if ($payment->status === 'completed') {
                event(new PaymentCompleted($payment));
            }
        } catch (\Exception $e) {
            $this->fail($e);
            event(new PaymentFailed($this->order, $e->getMessage()));
        }
    }
    
    public function failed(\Throwable $exception)
    {
        Log::error('Payment processing failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

#### SendNotificationJob
```php
class SendNotificationJob implements ShouldQueue
{
    public function __construct(
        public User $user,
        public string $message,
        public string $type = 'info'
    ) {}
    
    public function handle(NotificationService $notificationService)
    {
        $notificationService->send(
            $this->user,
            $this->message,
            $this->type
        );
    }
    
    public function retryUntil()
    {
        return now()->addHours(24);
    }
    
    public function backoff()
    {
        return [1, 5, 10, 30]; // seconds
    }
}
```

### Queue Configuration

#### Horizon Configuration
```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'emails', 'payments'],
            'balance' => 'auto',
            'processes' => 10,
        ],
        'supervisor-2' => [
            'connection' => 'redis',
            'queue' => ['high-priority'],
            'balance' => 'auto',
            'processes' => 5,
        ],
    ],
],
```

---

## Cache Strategy

### Cache Implementation

#### Product Cache
```php
class ProductService
{
    public function getProductById(int $id): Product
    {
        return Cache::remember(
            "product:{$id}",
            now()->addHours(24),
            fn() => Product::with(['category', 'brand', 'variants'])
                ->findOrFail($id)
        );
    }
    
    public function updateProduct(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        
        // Clear cache
        Cache::forget("product:{$id}");
        Cache::tags(['products'])->flush();
        
        return $product;
    }
}
```

#### Cache Tags
```php
class CategoryService
{
    public function getAllCategories(): Collection
    {
        return Cache::tags(['categories'])->remember(
            'categories:all',
            now()->addHours(12),
            fn() => Category::with('children')->get()
        );
    }
    
    public function clearCategoryCache()
    {
        Cache::tags(['categories'])->flush();
    }
}
```

### Cache Configuration

#### Redis Cache
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'lock_connection' => 'default',
],
```

---

## API Design

### RESTful API Standards

#### Endpoint Structure
```
GET    /api/products           - List all products
GET    /api/products/{id}      - Get single product
POST   /api/products           - Create product
PUT    /api/products/{id}      - Update product
DELETE /api/products/{id}      - Delete product

GET    /api/orders             - List user orders
POST   /api/orders             - Create order
GET    /api/orders/{id}        - Get order details
PUT    /api/orders/{id}/status - Update order status
```

#### Response Format
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "products": [...],
    "pagination": {
      "current_page": 1,
      "per_page": 20,
      "total": 100,
      "last_page": 5
    }
  }
}
```

#### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be at least 0."]
  }
}
```

### API Versioning
```
/api/v1/products
/api/v2/products
```

### Pagination
```php
public function index(Request $request)
{
    $products = Product::paginate($request->get('per_page', 20));
    return ProductResource::collection($products);
}
```

---

## Admin Panel Architecture

### Filament Resources

#### Resource Structure
```php
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Sản phẩm';
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            // Form fields
        ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table->columns([
            // Table columns
        ]);
    }
}
```

#### Resource Pages
```
ListProducts      - Product listing page
CreateProduct     - Product creation page
EditProduct       - Product editing page
```

### Admin Widgets

#### Dashboard Widgets
```php
class StatsOverview extends Widget
{
    protected static string $view = 'filament.widgets.stats-overview';
    
    protected function getStats(): array
    {
        return [
            Stat::make('Tổng doanh thu', '1.2 tỷ đ'),
            Stat::make('Đơn hàng', '150'),
            Stat::make('Khách hàng', '50'),
        ];
    }
}
```

### Admin Permissions

#### Role-Based Access Control
```php
trait HasRolePermissions
{
    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if ($user->hasRole('Super Admin')) return true;
        
        return $user->can("view " . static::getNavigationIdentifier());
    }
}
```

---

## Security Implementation

### Authentication

#### API Authentication (Sanctum)
```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

#### Session Authentication (Admin)
```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'database'),
'lifetime' => env('SESSION_LIFETIME', 120),
'expire_on_close' => false,
```

### Authorization

#### Permission Gates
```php
// AuthServiceProvider
Gate::define('update-product', function ($user, $product) {
    return $user->id === $product->user_id || $user->hasRole('admin');
});
```

#### Policy Classes
```php
class ProductPolicy
{
    public function view(User $user, Product $product)
    {
        return true;
    }
    
    public function update(User $user, Product $product)
    {
        return $user->can('edit products');
    }
    
    public function delete(User $user, Product $product)
    {
        return $user->can('delete products');
    }
}
```

### Input Validation

#### Request Validation
```php
class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|array',
            'payment_method' => 'required|exists:payment_methods,id',
        ];
    }
}
```

### SQL Injection Prevention
```php
// Use parameterized queries (Eloquent)
$products = Product::where('category_id', $categoryId)->get();

// Never use raw SQL with user input
// BAD: Product::whereRaw("category_id = $categoryId")->get();
```

### XSS Protection
```php
// Blade templates automatically escape HTML
{{ $user->name }}

// For unescaped output (use with caution)
{!! $content !!}
```

### CSRF Protection
```php
// Automatically included in forms
@csrf

// For API requests, use Sanctum tokens
```

---

## Performance Optimization

### Query Optimization

#### Eager Loading
```php
// BAD - N+1 query problem
$products = Product::all();
foreach ($products as $product) {
    echo $product->category->name;
}

// GOOD - Eager loading
$products = Product::with('category')->get();
foreach ($products as $product) {
    echo $product->category->name;
}
```

#### Query Caching
```php
$products = Cache::remember('products:all', 3600, function () {
    return Product::with(['category', 'brand'])->get();
});
```

#### Database Indexing
```php
// Migration
$table->index('category_id');
$table->index('is_active');
$table->index(['is_active', 'created_at']);
```

### Response Optimization

#### Response Compression
```php
// Kernel.php
protected $middleware = [
    \Illuminate\Http\Middleware\CompressResponse::class,
];
```

#### API Resource Pagination
```php
return ProductResource::collection($products)->additional([
    'meta' => [
        'total' => $products->total(),
        'per_page' => $products->perPage(),
    ],
]);
```

### Memory Optimization

#### Chunk Processing
```php
// Process large datasets in chunks
Product::chunk(100, function ($products) {
    foreach ($products as $product) {
        // Process product
    }
});
```

#### Lazy Loading
```php
// Load relationships only when needed
$product = Product::find($id);
$category = $product->category; // Loaded on demand
```

---

## Monitoring & Logging

### Application Logging

#### Log Levels
```php
Log::debug('Debug message');
Log::info('Info message');
Log::warning('Warning message');
Log::error('Error message');
Log::critical('Critical message');
```

#### Custom Log Channels
```php
// config/logging.php
'channels' => [
    'orders' => [
        'driver' => 'daily',
        'path' => storage_path('logs/orders.log'),
        'level' => 'info',
        'days' => 30,
    ],
    'payments' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payments.log'),
        'level' => 'warning',
        'days' => 90,
    ],
],
```

### Error Tracking

#### Sentry Integration
```php
// app/Exceptions/Handler.php
public function report(Throwable $exception)
{
    if (app()->bound('sentry')) {
        app('sentry')->captureException($exception);
    }
    
    parent::report($exception);
}
```

### Performance Monitoring

#### Query Logging
```php
DB::enableQueryLog();

// Execute queries

$queries = DB::getQueryLog();
Log::info('Queries executed', ['queries' => $queries]);
```

#### Response Time Logging
```php
$start = microtime(true);

// Execute code

$duration = microtime(true) - $start;
Log::info('Request duration', ['seconds' => $duration]);
```

---

## Testing Strategy

### Unit Tests
```php
class ProductServiceTest extends TestCase
{
    public function test_it_can_create_product()
    {
        $data = [
            'name' => 'Test Product',
            'price' => 100000,
            'category_id' => 1,
        ];
        
        $product = $this->productService->createProduct($data);
        
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
        ]);
    }
}
```

### Feature Tests
```php
class ProductApiTest extends TestCase
{
    public function test_user_can_get_products()
    {
        $response = $this->getJson('/api/products');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['products'],
            ]);
    }
}
```

### Integration Tests
```php
class OrderFlowTest extends TestCase
{
    public function test_complete_order_flow()
    {
        // Create user
        $user = User::factory()->create();
        
        // Add to cart
        $this->actingAs($user)
            ->postJson('/api/cart', [
                'product_id' => 1,
                'quantity' => 2,
            ]);
        
        // Create order
        $response = $this->actingAs($user)
            ->postJson('/api/orders', [
                'shipping_address' => [...],
                'payment_method' => 1,
            ]);
        
        $response->assertStatus(201);
        
        // Verify order created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }
}
```

---

## Deployment

### Environment Configuration
```bash
# .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.vinamilk.com

DB_CONNECTION=mysql
DB_HOST=production-db.example.com
DB_DATABASE=vinamilk_production
DB_USERNAME=vinamilk_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### Deployment Steps
1. Pull latest code
2. Install dependencies: `composer install --no-dev`
3. Run migrations: `php artisan migrate --force`
4. Clear cache: `php artisan cache:clear`
5. Optimize: `php artisan optimize`
6. Restart queue workers: `php artisan horizon:terminate`
7. Restart PHP-FPM

### CI/CD Pipeline
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to server
        run: |
          ssh user@server 'cd /var/www/vinamilk && git pull && composer install --no-dev && php artisan migrate --force && php artisan cache:clear && php artisan optimize'
```

---

## Best Practices

### Code Organization
- Keep controllers thin - delegate to services
- Use form requests for validation
- Use API resources for response formatting
- Use observers for model events
- Use jobs for background processing

### Performance
- Use eager loading to prevent N+1 queries
- Cache frequently accessed data
- Use queue for time-consuming tasks
- Optimize database queries with indexes
- Use pagination for large datasets

### Security
- Never trust user input - always validate
- Use parameterized queries
- Implement rate limiting
- Use HTTPS in production
- Keep dependencies updated

### Testing
- Write unit tests for business logic
- Write feature tests for API endpoints
- Write integration tests for critical flows
- Use factories for test data
- Mock external services in tests

### Documentation
- Document API endpoints
- Document complex business logic
- Keep README files updated
- Use inline comments for complex code
- Maintain architecture documentation
