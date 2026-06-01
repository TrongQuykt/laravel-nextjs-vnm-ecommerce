# Vinamilk Core Ecommerce - Frontend Architecture Documentation

## Table of Contents
1. [Frontend Overview](#frontend-overview)
2. [Technology Stack](#technology-stack)
3. [Directory Structure](#directory-structure)
4. [Architecture Patterns](#architecture-patterns)
5. [Component Architecture](#component-architecture)
6. [State Management](#state-management)
7. [Routing Strategy](#routing-strategy)
8. [API Integration](#api-integration)
9. [Performance Optimization](#performance-optimization)
10. [Styling Strategy](#styling-strategy)
11. [Type Safety](#type-safety)
12. [Build & Deployment](#build--deployment)

---

## Frontend Overview

### Application Type
- **Framework:** Next.js 16.2.4 (App Router)
- **Language:** TypeScript
- **Rendering:** Server-Side Rendering (SSR) + Client-Side Rendering (CSR)
- **Styling:** TailwindCSS 4.x
- **State Management:** React Context + Zustand
- **UI Components:** Custom component library

### Key Features
- **Server-Side Rendering:** Improved SEO and initial load performance
- **Static Generation:** Optimized for static pages
- **Incremental Static Regeneration:** Dynamic content updates
- **Image Optimization:** Next.js Image component
- **Font Optimization:** Next.js Font component
- **Code Splitting:** Automatic route-based splitting

### Performance Targets
- **First Contentful Paint (FCP):** < 1.5s
- **Largest Contentful Paint (LCP):** < 2.5s
- **Time to Interactive (TTI):** < 3.5s
- **Cumulative Layout Shift (CLS):** < 0.1
- **First Input Delay (FID):** < 100ms

---

## Technology Stack

### Core Framework
- **Next.js:** 16.2.4 (App Router)
- **React:** 19.2.4
- **TypeScript:** 5.x
- **Node.js:** 18.x+

### Styling
- **TailwindCSS:** 4.x
- **PostCSS:** 8.x
- **CSS Modules:** For component-specific styles

### State Management
- **React Context:** Global state (user, theme)
- **Zustand:** Complex state (cart, wishlist)
- **React Hook Form:** Form state
- **React Query:** Server state (API data)

### UI Components
- **Lucide React:** Icons
- **Framer Motion:** Animations
- **Leaflet:** Maps
- **React Leaflet:** Map components

### Utilities
- **date-fns:** Date manipulation
- **clsx:** Conditional classes
- **tailwind-merge:** Class merging

### Development Tools
- **ESLint:** Code linting
- **TypeScript:** Type checking
- **Turbopack:** Fast builds (Next.js 16)

---

## Directory Structure

```
src/
├── app/                      # Next.js App Router pages
│   ├── account/              # Account pages
│   ├── best-selling/         # Best selling products
│   ├── care/                 # Care program pages
│   ├── cart/                 # Shopping cart
│   ├── checkout/             # Checkout flow
│   ├── collections/          # Product collections
│   ├── flash-sales/          # Flash sales
│   ├── khuyen-mai/           # Promotions
│   ├── login/                # Authentication
│   ├── payment-result/       # Payment results
│   ├── products/             # Product pages
│   ├── promotions/           # Promotions
│   ├── recover/              # Password recovery
│   ├── register/             # Registration
│   ├── search/               # Search
│   ├── store-list/           # Store locations
│   ├── support/              # Support pages
│   ├── tin-tuc/              # Blog/news
│   ├── vinamilk-rewards/     # Rewards program
│   ├── layout.tsx            # Root layout
│   ├── page.tsx              # Home page
│   └── globals.css           # Global styles
├── components/               # Reusable components
│   ├── account/              # Account-related components
│   ├── blogs/                # Blog components
│   ├── care/                 # Care program components
│   ├── catalog/              # Product catalog components
│   ├── chat/                 # AI chat widget
│   ├── checkout/             # Checkout components
│   ├── ecommerce/            # E-commerce components
│   ├── home/                 # Home page components
│   ├── layout/               # Layout components
│   ├── promotions/           # Promotion components
│   ├── store-list/           # Store list components
│   ├── support/              # Support components
│   └── ui/                   # UI components
├── context/                  # React Context providers
│   ├── AuthContext.tsx       # Authentication context
│   ├── CartContext.tsx       # Shopping cart context
│   └── ThemeContext.tsx      # Theme context
├── hooks/                    # Custom React hooks
├── lib/                     # Utility functions
│   ├── api.ts                # API client
│   ├── utils.ts              # Utility functions
│   └── validations.ts        # Validation schemas
└── types/                    # TypeScript types
    ├── api.ts                # API response types
    └── index.ts              # Common types
```

---

## Architecture Patterns

### 1. Component-Based Architecture
**Purpose:** Reusable, composable UI components

**Implementation:**
- Atomic Design principles
- Component composition
- Props drilling prevention with Context
- Component lazy loading

**Example:**
```typescript
// Atomic component
interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  children: React.ReactNode;
  onClick?: () => void;
}

export function Button({ variant = 'primary', size = 'md', children, onClick }: ButtonProps) {
  return (
    <button
      className={cn(
        'rounded-lg font-medium transition-colors',
        {
          'bg-blue-600 text-white hover:bg-blue-700': variant === 'primary',
          'bg-gray-200 text-gray-900 hover:bg-gray-300': variant === 'secondary',
        },
        {
          'px-3 py-1.5 text-sm': size === 'sm',
          'px-4 py-2 text-base': size === 'md',
          'px-6 py-3 text-lg': size === 'lg',
        }
      )}
      onClick={onClick}
    >
      {children}
    </button>
  );
}
```

### 2. Container/Presenter Pattern
**Purpose:** Separate business logic from UI

**Implementation:**
- Container components handle data fetching
- Presenter components handle UI rendering
- Props interface for communication

**Example:**
```typescript
// Container component
export function ProductListContainer() {
  const { products, loading, error } = useProducts();
  
  if (loading) return <LoadingSpinner />;
  if (error) return <ErrorMessage message={error} />;
  
  return <ProductList products={products} />;
}

// Presenter component
interface ProductListProps {
  products: Product[];
}

export function ProductList({ products }: ProductListProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
      {products.map(product => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}
```

### 3. Higher-Order Components (HOC)
**Purpose:** Component composition and reuse

**Implementation:**
- Wrapper components for common functionality
- Props enhancement
- Cross-cutting concerns

**Example:**
```typescript
function withAuth<P extends object>(Component: React.ComponentType<P>) {
  return function AuthenticatedComponent(props: P) {
    const { user, loading } = useAuth();
    
    if (loading) return <LoadingSpinner />;
    if (!user) return <LoginPage />;
    
    return <Component {...props} />;
  };
}

export const ProtectedProfile = withAuth(ProfilePage);
```

### 4. Custom Hooks Pattern
**Purpose:** Reusable stateful logic

**Implementation:**
- Custom hooks for common logic
- Encapsulated state management
- Testable logic

**Example:**
```typescript
function useCart() {
  const [cart, setCart] = useState<CartItem[]>([]);
  const [loading, setLoading] = useState(false);
  
  const addToCart = async (item: CartItem) => {
    setLoading(true);
    try {
      const response = await api.post('/cart', item);
      setCart(response.data.items);
    } catch (error) {
      console.error('Failed to add to cart', error);
    } finally {
      setLoading(false);
    }
  };
  
  return { cart, loading, addToCart };
}
```

### 5. Render Props Pattern
**Purpose:** Share code between components via props

**Implementation:**
- Render prop functions
- Component composition
- Flexible component APIs

**Example:**
```typescript
interface DataFetcherProps {
  render: (data: any, loading: boolean) => React.ReactNode;
  url: string;
}

export function DataFetcher({ render, url }: DataFetcherProps) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    fetch(url)
      .then(res => res.json())
      .then(data => {
        setData(data);
        setLoading(false);
      });
  }, [url]);
  
  return render(data, loading);
}

// Usage
<DataFetcher url="/api/products" render={(data, loading) => (
  loading ? <LoadingSpinner /> : <ProductList products={data} />
)} />
```

---

## Component Architecture

### Component Hierarchy

```
App
├── Layout Components
│   ├── MainLayout
│   │   ├── Header
│   │   ├── MegaMenu
│   │   ├── Footer
│   │   └── ChatWidget
│   ├── AuthLayout
│   └── CheckoutLayout
├── Page Components
│   ├── HomePage
│   ├── ProductPage
│   ├── CartPage
│   ├── CheckoutPage
│   └── AccountPage
├── Feature Components
│   ├── ProductCard
│   ├── CartItem
│   ├── OrderSummary
│   └── SearchBar
└── Shared Components
│   ├── Button
│   ├── Input
│   ├── Modal
│   ├── LoadingSpinner
│   └── Toast
```

### Component Categories

#### 1. Layout Components
**Location:** `src/components/layout/`

**Purpose:** Page layout structure

**Components:**
- `Header` - Site header with navigation
- `Footer` - Site footer with links
- `MegaMenu` - Dropdown menu system
- `Sidebar` - Mobile sidebar navigation
- `ChatWidget` - AI chat floating widget

**Example:**
```typescript
export function Header() {
  const { user } = useAuth();
  const { cartItems } = useCart();
  
  return (
    <header className="sticky top-0 z-50 bg-white border-b">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between h-16">
          <Logo />
          <MegaMenu />
          <div className="flex items-center gap-4">
            <SearchBar />
            <CartIcon count={cartItems.length} />
            {user ? <UserMenu /> : <LoginButton />}
          </div>
        </div>
      </div>
    </header>
  );
}
```

#### 2. Catalog Components
**Location:** `src/components/catalog/`

**Purpose:** Product catalog display

**Components:**
- `ProductCard` - Product display card
- `ProductGrid` - Grid of products
- `ProductFilter` - Filter sidebar
- `ProductSort` - Sort options
- `ProductPagination` - Pagination controls

**Example:**
```typescript
interface ProductCardProps {
  product: Product;
  onAddToCart?: (product: Product) => void;
}

export function ProductCard({ product, onAddToCart }: ProductCardProps) {
  const { addToCart } = useCart();
  
  return (
    <div className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
      <div className="relative">
        <Image
          src={product.mainImage}
          alt={product.name}
          width={300}
          height={300}
          className="w-full h-64 object-cover"
        />
        {product.comparePrice > product.price && (
          <Badge className="absolute top-2 left-2">Sale</Badge>
        )}
      </div>
      <div className="p-4">
        <h3 className="font-semibold text-lg mb-2">{product.name}</h3>
        <div className="flex items-center gap-2 mb-4">
          <span className="text-xl font-bold text-red-600">
            {formatPrice(product.price)}
          </span>
          {product.comparePrice > product.price && (
            <span className="text-sm text-gray-500 line-through">
              {formatPrice(product.comparePrice)}
            </span>
          )}
        </div>
        <Button onClick={() => addToCart(product)} className="w-full">
          Thêm vào giỏ
        </Button>
      </div>
    </div>
  );
}
```

#### 3. Checkout Components
**Location:** `src/components/checkout/`

**Purpose:** Checkout flow components

**Components:**
- `CheckoutSteps` - Progress indicator
- `ShippingForm` - Shipping address form
- `PaymentMethodSelector` - Payment method selection
- `OrderSummary` - Order summary
- `CouponInput` - Coupon code input

**Example:**
```typescript
export function CheckoutSteps({ currentStep }: { currentStep: number }) {
  const steps = [
    { id: 1, name: 'Giỏ hàng' },
    { id: 2, name: 'Thông tin giao hàng' },
    { id: 3, name: 'Thanh toán' },
    { id: 4, name: 'Hoàn tất' },
  ];
  
  return (
    <div className="flex items-center justify-between mb-8">
      {steps.map((step, index) => (
        <div key={step.id} className="flex items-center">
          <div className={cn(
            "w-10 h-10 rounded-full flex items-center justify-center",
            currentStep >= step.id ? "bg-blue-600 text-white" : "bg-gray-200"
          )}>
            {currentStep > step.id ? "✓" : step.id}
          </div>
          <span className="ml-2">{step.name}</span>
          {index < steps.length - 1 && (
            <div className="w-24 h-1 mx-4 bg-gray-200" />
          )}
        </div>
      ))}
    </div>
  );
}
```

#### 4. UI Components
**Location:** `src/components/ui/`

**Purpose:** Reusable UI elements

**Components:**
- `Button` - Button component
- `Input` - Input field
- `Select` - Select dropdown
- `Modal` - Modal dialog
- `Toast` - Notification toast
- `LoadingSpinner` - Loading indicator
- `Badge` - Status badge

**Example:**
```typescript
interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
}

export function Modal({ isOpen, onClose, title, children }: ModalProps) {
  if (!isOpen) return null;
  
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/50" onClick={onClose} />
      <div className="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        {title && <h2 className="text-xl font-bold mb-4">{title}</h2>}
        {children}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        >
          ✕
        </button>
      </div>
    </div>
  );
}
```

---

## State Management

### 1. React Context
**Purpose:** Global state for authentication, theme

**Location:** `src/context/`

#### AuthContext
```typescript
interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
}

export const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    checkAuth();
  }, []);
  
  const login = async (email: string, password: string) => {
    const response = await api.post('/login', { email, password });
    setUser(response.data.user);
    localStorage.setItem('token', response.data.token);
  };
  
  const logout = async () => {
    await api.post('/logout');
    setUser(null);
    localStorage.removeItem('token');
  };
  
  return (
    <AuthContext.Provider value={{ user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}
```

#### CartContext
```typescript
interface CartContextType {
  items: CartItem[];
  addItem: (item: CartItem) => void;
  removeItem: (itemId: number) => void;
  updateQuantity: (itemId: number, quantity: number) => void;
  clearCart: () => void;
  total: number;
}

export const CartContext = createContext<CartContextType | undefined>(undefined);

export function CartProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([]);
  
  const addItem = (item: CartItem) => {
    setItems(prev => {
      const existing = prev.find(i => i.product_id === item.product_id);
      if (existing) {
        return prev.map(i => 
          i.product_id === item.product_id 
            ? { ...i, quantity: i.quantity + item.quantity }
            : i
        );
      }
      return [...prev, item];
    });
  };
  
  const total = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
  
  return (
    <CartContext.Provider value={{ items, addItem, removeItem, updateQuantity, clearCart, total }}>
      {children}
    </CartContext.Provider>
  );
}
```

### 2. Zustand
**Purpose:** Complex state management

**Location:** `src/lib/store.ts`

**Example:**
```typescript
import { create } from 'zustand';

interface CartStore {
  items: CartItem[];
  addItem: (item: CartItem) => void;
  removeItem: (id: number) => void;
  clearCart: () => void;
  total: () => number;
}

export const useCartStore = create<CartStore>((set, get) => ({
  items: [],
  
  addItem: (item) => set((state) => {
    const existing = state.items.find(i => i.id === item.id);
    if (existing) {
      return {
        items: state.items.map(i => 
          i.id === item.id 
            ? { ...i, quantity: i.quantity + item.quantity }
            : i
        )
      };
    }
    return { items: [...state.items, item] };
  }),
  
  removeItem: (id) => set((state) => ({
    items: state.items.filter(i => i.id !== id)
  })),
  
  clearCart: () => set({ items: [] }),
  
  total: () => get().items.reduce((sum, item) => sum + item.price * item.quantity, 0),
}));
```

### 3. React Query
**Purpose:** Server state management

**Location:** Custom hooks

**Example:**
```typescript
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';

export function useProducts() {
  return useQuery({
    queryKey: ['products'],
    queryFn: () => api.get('/products').then(res => res.data),
  });
}

export function useProduct(id: string) {
  return useQuery({
    queryKey: ['product', id],
    queryFn: () => api.get(`/products/${id}`).then(res => res.data),
  });
}

export function useAddToCart() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (item: CartItem) => api.post('/cart', item),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['cart'] });
    },
  });
}
```

---

## Routing Strategy

### App Router Structure
**Location:** `src/app/`

**Route Organization:**
- File-based routing
- Dynamic routes with `[slug]`
- Route groups with `(group)`
- Layouts with `layout.tsx`

### Route Examples

#### Static Routes
```
src/app/
├── page.tsx                    → /
├── about/page.tsx              → /about
├── contact/page.tsx            → /contact
```

#### Dynamic Routes
```
src/app/
├── products/[slug]/page.tsx    → /products/vinamilk-100
├── collections/[slug]/page.tsx → /collections/sua-tuoi
```

#### Route Groups
```
src/app/
├── (auth)/
│   ├── login/page.tsx         → /login
│   └── register/page.tsx      → /register
├── (account)/
│   ├── profile/page.tsx       → /account/profile
│   └── orders/page.tsx        → /account/orders
```

### Route Parameters

#### Dynamic Route Parameters
```typescript
// src/app/products/[slug]/page.tsx
export default function ProductPage({ params }: { params: { slug: string } }) {
  const { data: product } = useProduct(params.slug);
  
  return (
    <div>
      <h1>{product.name}</h1>
      {/* Product details */}
    </div>
  );
}
```

#### Search Parameters
```typescript
// src/app/catalog/page.tsx
export default function CatalogPage({ searchParams }: { searchParams: { page?: string, category?: string } }) {
  const page = searchParams.page || '1';
  const category = searchParams.category;
  
  const { data: products } = useProducts({ page, category });
  
  return <ProductList products={products} />;
}
```

### Route Guards

#### Protected Routes
```typescript
// src/app/account/profile/page.tsx
import { redirect } from 'next/navigation';
import { getServerSession } from '@/lib/auth';

export default async function ProfilePage() {
  const session = await getServerSession();
  
  if (!session) {
    redirect('/login');
  }
  
  return <ProfileContent user={session.user} />;
}
```

---

## API Integration

### API Client
**Location:** `src/lib/api.ts`

**Implementation:**
```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Redirect to login
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### API Hooks
**Location:** Custom hooks in components

**Example:**
```typescript
// src/hooks/useProducts.ts
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

export function useProducts(params?: { category?: string; search?: string }) {
  return useQuery({
    queryKey: ['products', params],
    queryFn: () => api.get('/catalog', { params }).then(res => res.data),
  });
}

export function useProduct(slug: string) {
  return useQuery({
    queryKey: ['product', slug],
    queryFn: () => api.get(`/products/${slug}`).then(res => res.data),
  });
}
```

### Error Handling
```typescript
export function useApiCall() {
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  
  const call = async (fn: () => Promise<any>) => {
    setLoading(true);
    setError(null);
    try {
      const result = await fn();
      return result;
    } catch (err: any) {
      setError(err.response?.data?.message || 'An error occurred');
      throw err;
    } finally {
      setLoading(false);
    }
  };
  
  return { error, loading, call };
}
```

---

## Performance Optimization

### 1. Code Splitting
**Purpose:** Reduce initial bundle size

**Implementation:**
```typescript
// Dynamic imports
const ProductCard = dynamic(() => import('@/components/catalog/ProductCard'), {
  loading: () => <LoadingSkeleton />,
});

const ChatWidget = dynamic(() => import('@/components/chat/ChatWidget'), {
  ssr: false, // Client-side only
});
```

### 2. Image Optimization
**Purpose:** Optimize image loading

**Implementation:**
```typescript
import Image from 'next/image';

export function ProductImage({ src, alt }: { src: string; alt: string }) {
  return (
    <Image
      src={src}
      alt={alt}
      width={300}
      height={300}
      loading="lazy"
      placeholder="blur"
      blurDataURL="data:image/jpeg;base64,/9j/4AAQSkZJRg..."
    />
  );
}
```

### 3. Font Optimization
**Purpose:** Optimize font loading

**Implementation:**
```typescript
import { Inter } from 'next/font/google';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
});

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi" className={inter.variable}>
      <body>{children}</body>
    </html>
  );
}
```

### 4. Memoization
**Purpose:** Prevent unnecessary re-renders

**Implementation:**
```typescript
import { memo } from 'react';

export const ProductCard = memo(function ProductCard({ product }: { product: Product }) {
  return <div>{/* Product card content */}</div>;
});
```

### 5. Virtual Scrolling
**Purpose:** Handle large lists efficiently

**Implementation:**
```typescript
import { useVirtualizer } from '@tanstack/react-virtual';

export function ProductList({ products }: { products: Product[] }) {
  const parentRef = useRef<HTMLDivElement>(null);
  
  const virtualizer = useVirtualizer({
    count: products.length,
    getScrollElement: () => parentRef.current,
    estimateSize: () => 300,
    overscan: 5,
  });
  
  return (
    <div ref={parentRef} className="h-[600px] overflow-auto">
      <div style={{ height: `${virtualizer.getTotalSize()}px` }}>
        {virtualizer.getVirtualItems().map((virtualItem) => (
          <div
            key={virtualItem.key}
            style={{
              position: 'absolute',
              top: 0,
              left: 0,
              width: '100%',
              height: `${virtualItem.size}px`,
              transform: `translateY(${virtualItem.start}px)`,
            }}
          >
            <ProductCard product={products[virtualItem.index]} />
          </div>
        ))}
      </div>
    </div>
  );
}
```

---

## Styling Strategy

### TailwindCSS Configuration
**Location:** `tailwind.config.ts`

**Implementation:**
```typescript
import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './src/pages/**/*.{js,ts,jsx,tsx,mdx}',
    './src/components/**/*.{js,ts,jsx,tsx,mdx}',
    './src/app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#f0f9ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
        },
        vinamilk: {
          blue: '#0066cc',
          green: '#00a651',
          red: '#e31c23',
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
};

export default config;
```

### Component Styling
**Purpose:** Consistent styling approach

**Implementation:**
```typescript
import { cn } from '@/lib/utils';

interface ButtonProps {
  variant?: 'primary' | 'secondary' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  className?: string;
  children: React.ReactNode;
}

export function Button({ variant = 'primary', size = 'md', className, children, ...props }: ButtonProps) {
  return (
    <button
      className={cn(
        'rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2',
        {
          'bg-vinamilk-blue text-white hover:bg-blue-700 focus:ring-vinamilk-blue': variant === 'primary',
          'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500': variant === 'secondary',
          'border-2 border-vinamilk-blue text-vinamilk-blue hover:bg-blue-50 focus:ring-vinamilk-blue': variant === 'outline',
        },
        {
          'px-3 py-1.5 text-sm': size === 'sm',
          'px-4 py-2 text-base': size === 'md',
          'px-6 py-3 text-lg': size === 'lg',
        },
        className
      )}
      {...props}
    >
      {children}
    </button>
  );
}
```

### Responsive Design
**Purpose:** Mobile-first responsive design

**Implementation:**
```typescript
export function ProductGrid({ products }: { products: Product[] }) {
  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      {products.map(product => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}
```

---

## Type Safety

### TypeScript Configuration
**Location:** `tsconfig.json`

**Implementation:**
```json
{
  "compilerOptions": {
    "target": "ES2020",
    "lib": ["dom", "dom.iterable", "esnext"],
    "allowJs": true,
    "skipLibCheck": true,
    "strict": true,
    "noEmit": true,
    "esModuleInterop": true,
    "module": "esnext",
    "moduleResolution": "bundler",
    "resolveJsonModule": true,
    "isolatedModules": true,
    "jsx": "preserve",
    "incremental": true,
    "plugins": ["next"],
    "paths": {
      "@/*": ["./src/*"]
    }
  },
  "include": ["next-env.d.ts", "**/*.ts", "**/*.tsx"],
  "exclude": ["node_modules"]
}
```

### Type Definitions
**Location:** `src/types/`

**API Types:**
```typescript
// src/types/api.ts
export interface Product {
  id: number;
  name: string;
  slug: string;
  price: number;
  compare_price?: number;
  description: string;
  category: Category;
  brand: Brand;
  variants: ProductVariant[];
  images: ProductImage[];
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  avatar?: string;
}

export interface CartItem {
  id: number;
  product: Product;
  variant?: ProductVariant;
  quantity: number;
  subtotal: number;
}

export interface Order {
  id: number;
  order_number: string;
  status: 'pending' | 'confirmed' | 'processing' | 'shipped' | 'delivered' | 'cancelled';
  subtotal: number;
  discount: number;
  shipping_fee: number;
  total: number;
  items: OrderItem[];
  shipping_address: Address;
  payment: Payment;
  created_at: string;
}
```

### Component Props Types
```typescript
interface ProductCardProps {
  product: Product;
  onAddToCart?: (product: Product) => void;
  variant?: 'default' | 'compact' | 'detailed';
}

export function ProductCard({ product, onAddToCart, variant = 'default' }: ProductCardProps) {
  // Component implementation
}
```

---

## Build & Deployment

### Build Configuration
**Location:** `next.config.ts`

**Implementation:**
```typescript
import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'api.vinamilk.com',
      },
    ],
  },
  env: {
    NEXT_PUBLIC_API_URL: process.env.NEXT_PUBLIC_API_URL,
  },
  experimental: {
    turbo: {
      rules: {
        '*.svg': {
          loaders: ['@svgr/webpack'],
          as: '*.js',
        },
      },
    },
  },
};

export default nextConfig;
```

### Environment Variables
**Location:** `.env.local`

```bash
NEXT_PUBLIC_API_URL=https://api.vinamilk.com/api/v1
NEXT_PUBLIC_GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
NEXT_PUBLIC_FACEBOOK_PIXEL_ID=XXXXXXXXXX
```

### Deployment Strategies

#### Vercel Deployment
```bash
# Install Vercel CLI
npm i -g vercel

# Deploy
vercel --prod
```

#### Docker Deployment
```dockerfile
# Dockerfile
FROM node:18-alpine AS base

# Install dependencies
FROM base AS deps
WORKDIR /app
COPY package*.json ./
RUN npm ci

# Build application
FROM base AS builder
WORKDIR /app
COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN npm run build

# Production image
FROM base AS runner
WORKDIR /app
ENV NODE_ENV production
COPY --from=builder /app/public ./public
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static

EXPOSE 3000
CMD ["node", "server.js"]
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name vinamilk.com;

    root /var/www/vinamilk-fe;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    location /_next/static {
        proxy_pass http://localhost:3000;
    }

    location /_next/image {
        proxy_pass http://localhost:3000;
    }
}
```

---

## Monitoring & Analytics

### Google Analytics
**Implementation:**
```typescript
// src/app/layout.tsx
import Script from 'next/script';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="vi">
      <head>
        <Script
          src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"
          strategy="afterInteractive"
        />
        <Script id="google-analytics" strategy="afterInteractive">
          {`
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-XXXXXXXXXX');
          `}
        </Script>
      </head>
      <body>{children}</body>
    </html>
  );
}
```

### Error Tracking
**Implementation:**
```typescript
// src/app/error.tsx
'use client';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Log error to error tracking service
    console.error('Application error:', error);
  }, [error]);

  return (
    <div className="flex items-center justify-center min-h-screen">
      <div className="text-center">
        <h2 className="text-2xl font-bold mb-4">Đã có lỗi xảy ra</h2>
        <button onClick={reset} className="btn btn-primary">
          Thử lại
        </button>
      </div>
    </div>
  );
}
```

---

## Best Practices

### Code Organization
- Use atomic design principles
- Keep components small and focused
- Use TypeScript for type safety
- Follow consistent naming conventions
- Use absolute imports with path aliases

### Performance
- Implement code splitting
- Optimize images with Next.js Image
- Use lazy loading for heavy components
- Implement virtual scrolling for large lists
- Use React.memo for expensive components

### Accessibility
- Use semantic HTML
- Add ARIA labels where needed
- Ensure keyboard navigation
- Use proper color contrast
- Add alt text to images

### Testing
- Write unit tests for components
- Write integration tests for pages
- Use React Testing Library
- Mock API calls in tests
- Test responsive design

### Security
- Never expose sensitive data
- Validate all user inputs
- Use environment variables for secrets
- Implement CSRF protection
- Use HTTPS in production

---

## Development Workflow

### Local Development
```bash
# Install dependencies
npm install

# Run development server
npm run dev

# Run in production mode
npm run build
npm start

# Run linter
npm run lint
```

### Code Quality
```bash
# Type checking
npx tsc --noEmit

# Linting
npm run lint

# Format code
npx prettier --write .
```

### Git Workflow
```bash
# Create feature branch
git checkout -b feature/new-feature

# Commit changes
git add .
git commit -m "feat: add new feature"

# Push to remote
git push origin feature/new-feature

# Create pull request
```

---

## Troubleshooting

### Common Issues

#### Build Errors
**Issue:** Build fails with TypeScript errors
**Solution:** Run `npx tsc --noEmit` to identify type errors

#### API Errors
**Issue:** API calls failing in production
**Solution:** Check CORS configuration and API URL environment variable

#### Performance Issues
**Issue:** Slow page load times
**Solution:** Implement code splitting, optimize images, use caching

#### Styling Issues
**Issue:** TailwindCSS classes not working
**Solution:** Check Tailwind configuration and content paths

---

## Future Enhancements

### Planned Features
- [ ] PWA support for offline access
- [ ] Service Worker for caching
- [ ] Web Push Notifications
- [ ] Progressive Image Loading
- [ ] Advanced Analytics Dashboard
- [ ] A/B Testing Integration
- [ ] Multi-language Support (i18n)
- [ ] Dark Mode Support

### Performance Improvements
- [ ] Implement Edge Functions
- [ ] Add CDN for static assets
- [ ] Optimize bundle size
- [ ] Implement request deduplication
- [ ] Add prefetching for navigation

---

## Resources

### Documentation
- [Next.js Documentation](https://nextjs.org/docs)
- [React Documentation](https://react.dev)
- [TypeScript Documentation](https://www.typescriptlang.org/docs)
- [TailwindCSS Documentation](https://tailwindcss.com/docs)

### Tools
- [Vercel](https://vercel.com) - Deployment platform
- [Framer Motion](https://www.framer.com/motion) - Animation library
- [React Query](https://tanstack.com/query) - Server state management
- [Zustand](https://zustand-demo.pmnd.rs) - State management
