# Vinamilk Core Ecommerce - System Architecture Overview

## Table of Contents
1. [Executive Summary](#executive-summary)
2. [Technology Stack](#technology-stack)
3. [System Architecture](#system-architecture)
4. [Deployment Architecture](#deployment-architecture)
5. [Security Architecture](#security-architecture)
6. [Scalability Considerations](#scalability-considerations)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring & Logging](#monitoring--logging)

---

## Executive Summary

**Project Name:** Vinamilk Core Ecommerce Platform  
**Type:** Enterprise E-commerce System  
**Architecture:** Monolithic with Microservices-ready design  
**Target Scale:** Enterprise-level, high-traffic e-commerce platform

### Business Overview
Vinamilk Core Ecommerce is a comprehensive e-commerce platform designed for dairy and nutrition products. The system supports:
- Multi-channel sales (web, mobile, in-store)
- Complex product catalog with variants
- Advanced promotion engine
- Multi-warehouse logistics
- Customer loyalty programs
- Content management system
- AI-powered customer support

### Key Business Requirements
- **High Availability:** 99.9% uptime requirement
- **Scalability:** Handle 10,000+ concurrent users
- **Performance:** <2s page load time
- **Security:** PCI DSS compliance for payments
- **Multi-tenant:** Support multiple stores/brands
- **Internationalization:** Multi-language, multi-currency support

---

## Technology Stack

### Backend Technology Stack

#### Core Framework
- **Language:** PHP 8.2+
- **Framework:** Laravel 10.x
- **Architecture Pattern:** MVC with Service Layer
- **Database:** MySQL 8.0+
- **Cache:** Redis 7.x
- **Queue:** Redis + Laravel Horizon
- **Search:** Elasticsearch (optional)
- **File Storage:** AWS S3 / Local

#### Admin Panel
- **Framework:** Filament PHP 3.x
- **UI Components:** TailwindCSS + Alpine.js
- **Charts:** Chart.js
- **Icons:** Heroicons

#### Authentication & Authorization
- **Package:** Spatie Laravel Permission
- **Multi-tenancy:** Custom implementation
- **JWT:** Laravel Sanctum (API)
- **Session:** Database-backed sessions

#### Third-party Integrations
- **Payment:** VNPay, MoMo, ZaloPay
- **Shipping:** GHN, GHTK, Viettel Post
- **SMS:** Viettel SMS Gateway
- **Email:** SMTP / SendGrid
- **AI/Chat:** OpenAI API / Custom LLM

### Frontend Technology Stack

#### Core Framework
- **Language:** TypeScript
- **Framework:** Next.js 14.x (App Router)
- **State Management:** React Context + Zustand
- **Styling:** TailwindCSS + Custom CSS
- **UI Components:** Custom component library

#### Performance & Optimization
- **Build Tool:** Turbopack (Next.js 14)
- **Code Splitting:** Automatic route-based splitting
- **Image Optimization:** Next.js Image component
- **Font Optimization:** Next.js Font component
- **Lazy Loading:** React.lazy + dynamic imports

#### Third-party Libraries
- **Forms:** React Hook Form + Zod validation
- **HTTP Client:** Axios / Fetch API
- **Date Handling:** date-fns
- **Icons:** Lucide React
- **Animations:** Framer Motion

---

## System Architecture

### High-Level Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│  Web Browser (Next.js)  │  Mobile App (React Native)  │  PWA   │
└──────────────────────────┴──────────────────────────────────────┘
                                   │
                                   │ HTTPS / REST API
                                   ▼
┌─────────────────────────────────────────────────────────────────┐
│                      CDN / LOAD BALANCER                        │
│                    (Cloudflare / AWS CloudFront)                │
└─────────────────────────────────────────────────────────────────┘
                                   │
                                   │
                ┌──────────────────┴──────────────────┐
                │                                     │
                ▼                                     ▼
┌───────────────────────────┐         ┌───────────────────────────┐
│   APPLICATION LAYER       │         │   STATIC ASSETS           │
│   (Laravel API)            │         │   (AWS S3 / CDN)          │
├───────────────────────────┤         └───────────────────────────┘
│  ┌─────────────────────┐ │
│  │  Admin Panel        │ │
│  │  (Filament)         │ │
│  ├─────────────────────┤ │
│  │  API Endpoints      │ │
│  │  (REST/JSON)        │ │
│  ├─────────────────────┤ │
│  │  WebSocket Server   │ │
│  │  (Real-time Chat)   │ │
│  └─────────────────────┘ │
└───────────────────────────┘
                │
                │
    ┌───────────┼───────────┐
    │           │           │
    ▼           ▼           ▼
┌────────┐  ┌────────┐  ┌────────┐
│  DB    │  │ Redis  │  │ Queue  │
│ MySQL  │  │ Cache  │  │ Worker │
└────────┘  └────────┘  └────────┘
```

### Backend Architecture Layers

#### 1. Presentation Layer (Controllers)
- **API Controllers:** Handle HTTP requests, validation, responses
- **Filament Resources:** Admin panel UI components
- **Middleware:** Authentication, authorization, rate limiting
- **Request/Response Transformers:** Data formatting

#### 2. Business Logic Layer (Services)
- **Service Classes:** Core business logic implementation
- **Domain Services:** Complex business operations
- **External Service Integrations:** Payment, shipping, SMS
- **Event Handlers:** Domain event processing

#### 3. Data Access Layer (Repositories)
- **Eloquent Models:** Database ORM
- **Query Scopes:** Reusable query builders
- **Relationships:** Model associations
- **Migrations:** Database schema management

#### 4. Infrastructure Layer
- **Cache Management:** Redis caching strategies
- **Queue Management:** Background job processing
- **File Storage:** S3 integration
- **Logging:** Application logging

### Frontend Architecture

#### Component Hierarchy
```
App
├── Layout Components
│   ├── MainLayout
│   ├── AdminLayout
│   └── AuthLayout
├── Page Components
│   ├── Home
│   ├── Products
│   ├── Cart
│   ├── Checkout
│   └── User Dashboard
├── Feature Components
│   ├── ProductCard
│   ├── CartItem
│   ├── OrderSummary
│   └── ChatWidget
└── Shared Components
    ├── Button
    ├── Input
    ├── Modal
    └── LoadingSpinner
```

#### State Management Strategy
- **Global State:** Zustand for cart, user, theme
- **Local State:** React useState for component state
- **Server State:** React Query / SWR for API data
- **Form State:** React Hook Form for form management

---

## Deployment Architecture

### Development Environment
- **Local Development:** Docker Compose
- **Version Control:** Git + GitHub
- **CI/CD:** GitHub Actions
- **Code Quality:** PHPStan, ESLint, Prettier

### Staging Environment
- **Infrastructure:** AWS EC2 / DigitalOcean
- **Database:** MySQL RDS
- **Cache:** Redis ElastiCache
- **File Storage:** S3 Development Bucket
- **Monitoring:** Sentry, New Relic

### Production Environment
- **Infrastructure:** AWS / Cloudflare
- **Load Balancing:** Application Load Balancer
- **Auto Scaling:** EC2 Auto Scaling Groups
- **Database:** MySQL RDS (Multi-AZ)
- **Cache:** Redis Cluster
- **CDN:** Cloudflare / AWS CloudFront
- **File Storage:** S3 + CloudFront
- **Monitoring:** AWS CloudWatch, Sentry

---

## Security Architecture

### Authentication & Authorization
- **Multi-factor Authentication:** Optional for admin users
- **Role-based Access Control:** 12 admin roles with granular permissions
- **API Authentication:** JWT tokens with refresh mechanism
- **Session Management:** Secure session handling with timeout

### Data Security
- **Encryption at Rest:** AES-256 for sensitive data
- **Encryption in Transit:** TLS 1.3
- **Password Hashing:** bcrypt (cost factor: 12)
- **PII Protection:** GDPR compliance measures

### API Security
- **Rate Limiting:** Per-IP and per-user limits
- **Input Validation:** Comprehensive validation rules
- **SQL Injection Prevention:** Parameterized queries (Eloquent)
- **XSS Protection:** Content Security Policy
- **CSRF Protection:** Token-based validation

### Payment Security
- **PCI DSS Compliance:** Payment card data handling
- **Tokenization:** Payment gateway token storage
- **Fraud Detection:** Basic fraud detection rules
- **Secure Callbacks:** Webhook signature verification

---

## Scalability Considerations

### Horizontal Scaling
- **Stateless Application:** Easy horizontal scaling
- **Database Sharding:** Potential for future sharding
- **Cache Layer:** Distributed Redis cluster
- **Queue Processing:** Multiple queue workers

### Vertical Scaling
- **Resource Optimization:** Code profiling and optimization
- **Database Indexing:** Strategic index placement
- **Query Optimization:** N+1 query prevention
- **Memory Management:** Efficient memory usage

### Caching Strategy
- **Application Cache:** Redis for frequently accessed data
- **Page Cache:** Full page caching for static content
- **CDN Cache:** Static assets and API responses
- **Browser Cache:** Cache headers for static resources

---

## Performance Optimization

### Backend Optimization
- **Query Optimization:** Eager loading, query caching
- **Queue Processing:** Background job offloading
- **Image Optimization:** On-the-fly image processing
- **Response Compression:** Gzip compression
- **Database Connection Pooling:** Efficient connection management

### Frontend Optimization
- **Code Splitting:** Route-based code splitting
- **Tree Shaking:** Dead code elimination
- **Image Optimization:** WebP format, lazy loading
- **Font Optimization:** Subset fonts, preload critical fonts
- **Bundle Size:** Minification and compression

### Database Optimization
- **Indexing Strategy:** Strategic index placement
- **Query Optimization:** Slow query monitoring
- **Connection Pooling:** Database connection management
- **Read Replicas:** Read-heavy query offloading

---

## Monitoring & Logging

### Application Monitoring
- **Error Tracking:** Sentry for error monitoring
- **Performance Monitoring:** New Relic APM
- **Uptime Monitoring:** UptimeRobot / Pingdom
- **Log Aggregation:** ELK Stack / CloudWatch Logs

### Business Metrics
- **Sales Analytics:** Custom dashboard
- **User Behavior:** Google Analytics
- **Conversion Tracking:** E-commerce events
- **A/B Testing:** Integration with testing tools

### Alerting
- **Error Alerts:** Immediate error notifications
- **Performance Alerts:** Response time thresholds
- **Uptime Alerts:** Downtime notifications
- **Business Alerts:** Critical business events

---

## Next Steps

For detailed documentation on specific areas, refer to:
- [Database Structure](DATABASE_STRUCTURE.md)
- [Backend Architecture](BACKEND_ARCHITECTURE.md)
- [API Documentation](API_DOCUMENTATION.md)
- [Frontend Architecture](FRONTEND_ARCHITECTURE.md)
- [System Flows](SYSTEM_FLOWS.md)
- [Functionality Trees](FUNCTIONALITY_TREES.md)
- [Test Cases](TEST_CASES.md)
