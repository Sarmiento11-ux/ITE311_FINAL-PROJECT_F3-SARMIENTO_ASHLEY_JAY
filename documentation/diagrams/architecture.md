# CraftMarket — System Architecture

**Project Title:** Startup-Oriented Web Application Development with CMS and System Integration (CraftMarket)  
**Courses:** Web Systems and Technologies 2 / System Integration and Architecture 1 (ITE311)  
**Pattern:** Decoupled three-tier architecture (SPA + REST API + relational database) with a headless CMS sidecar

---

## 1. Architectural Overview

CraftMarket separates presentation, business logic, and data so each layer can evolve independently:

- **Presentation tier** — Vue 3 SPA (Vite build, Vue Router, Axios, DOMPurify) served as static assets.
- **Application tier** — Laravel 12 REST API (PHP 8.2+), Sanctum bearer tokens, RBAC middleware, domain services, queued notifications.
- **Data tier** — MySQL 8 / InnoDB in production (SQLite for local development), schema owned by migrations.
- **Content sidecar** — Headless WordPress reads aggregate data through a shared-secret bridge; it never owns transactional data.
- **External services** — PayMongo hosted checkout and signed webhooks.

---

## 2. High-Level Architecture Diagram

```mermaid
flowchart TB
    subgraph Client["Presentation Tier"]
        Browser["Browser"]
        SPA["Vue 3 SPA<br/>Vite + Vue Router 4"]
        State["Reactive client state<br/>auth · cart · wishlist"]
        Services["Service layer<br/>Axios wrappers per domain"]
        Browser --> SPA
        SPA --- State
        SPA --- Services
    end

    subgraph App["Application Tier — Laravel 12"]
        Router["API router<br/>routes/api.php"]
        MW["Middleware<br/>auth:sanctum · role:* · throttle · cms.bridge"]
        Ctrl["Controllers<br/>Auth · Product · Cart · Order<br/>Payment · Seller · Admin · CMS"]
        Domain["Domain logic<br/>escrow · commission · payouts<br/>verification · analytics"]
        Models["Eloquent models<br/>+ relationships + casts"]
        Queue["Queue / notifications"]
        Router --> MW --> Ctrl --> Domain --> Models
        Domain --> Queue
    end

    subgraph Data["Data Tier"]
        DB[("MySQL 8 / InnoDB<br/>utf8mb4 · FKs · indexes")]
        Storage[("File storage<br/>product images")]
    end

    subgraph Ext["External Services"]
        PayMongo["PayMongo<br/>hosted checkout"]
        SMTP["SMTP / mail channel"]
        WP["WordPress<br/>headless CMS"]
    end

    Services -->|"HTTPS JSON + Bearer token"| Router
    Models --> DB
    Ctrl --> Storage
    Ctrl -->|"create checkout session"| PayMongo
    PayMongo -->|"signed webhook"| Router
    Queue --> SMTP
    WP -->|"X-CMS-Bridge-Key"| Router

    CORS["CORS allow-list"]
    Services -.-> CORS
```

---

## 3. Layer Responsibilities

| Layer | Components | Responsibilities | Must not do |
|---|---|---|---|
| **Presentation** | `frontend/src/views`, `components`, `services`, `router`, `composables` | Render UI, route guards, optimistic UI state, call the API through services | Compute authoritative prices, commissions, or permissions |
| **API / application** | `routes/api.php`, controllers, middleware | Validate input, authenticate, authorize, orchestrate transactions, shape JSON responses | Render HTML pages or trust client-calculated totals |
| **Domain** | Controller domain methods, models, services | Commission snapshot, escrow lifecycle, payout balance, verification state, analytics aggregation | Bypass database transactions for money movement |
| **Data** | Migrations, Eloquent models, MySQL | Persist normalized data, enforce FKs/uniques, retain financial snapshots | Store business rules only in application code |
| **Content sidecar** | `cms/craftmarket-sync-plugin` | Editorial content and aggregate reporting | Write transactional tables directly |
| **External** | PayMongo, SMTP | Payment capture, message delivery | Be trusted without signature/credential verification |

---

## 4. Request Lifecycle

```mermaid
sequenceDiagram
    autonumber
    participant U as User (SPA)
    participant R as Laravel Router
    participant M as Middleware
    participant C as Controller
    participant D as Domain / Model
    participant DB as MySQL

    U->>R: HTTPS request + Bearer token
    R->>M: Resolve route middleware
    M->>M: CORS, throttle, auth:sanctum, role:*
    alt Unauthenticated
        M-->>U: 401 JSON
    else Wrong role
        M-->>U: 403 JSON
    else Authorized
        M->>C: Validated request
        C->>C: Form request validation
        C->>D: Business operation
        D->>DB: Transaction (SELECT / UPDATE / INSERT)
        DB-->>D: Result
        D-->>C: Domain result
        C-->>U: { success, message, data }
    end
```

### 4.1 Checkout and escrow sequence

```mermaid
sequenceDiagram
    autonumber
    participant Cus as Customer
    participant API as Laravel API
    participant DB as MySQL
    participant PM as PayMongo
    participant Sel as Seller

    Cus->>API: POST /api/orders {items, address}
    API->>DB: BEGIN TRANSACTION
    API->>DB: Lock products, verify stock
    API->>API: Snapshot price + commission (8% / 3%)
    API->>API: Compute shipping (free ≥ ₱1,500, else ₱150)
    API->>DB: Insert order + order_items (escrow pending)
    API->>DB: Decrement stock, clear cart
    API->>DB: COMMIT
    API-->>Cus: Order CM-YYYYMMDD-XXXX

    Cus->>API: POST /api/payments/checkout-session
    API->>PM: Create checkout session
    PM-->>Cus: Hosted checkout page
    Cus->>PM: Pay
    PM->>API: POST /api/webhooks/paymongo (signed)
    API->>API: Verify HMAC-SHA256 signature
    API->>DB: Payment = paid, order = confirmed, escrow = held
    API-->>PM: 200 OK

    Sel->>API: PATCH /seller/order-items/{id}/status (delivered)
    API->>DB: Item delivered, escrow released, payout credited
    Sel->>API: POST /seller/payouts
    API->>DB: Reserve amount (pending)
```

---

## 5. Component Diagram

```mermaid
flowchart LR
    subgraph Frontend["frontend/"]
        V1["Public views<br/>Home · Shop · Product"]
        V2["Customer views<br/>Cart · Checkout · Orders · Wishlist"]
        V3["Seller views<br/>Dashboard · Products · Orders · Payouts"]
        V4["Admin views<br/>Dashboard · Users · Sellers<br/>Products · Orders · Payouts · Reports"]
        SV["services/*.js"]
        RT["router/ + guards"]
        V1 --> SV
        V2 --> SV
        V3 --> SV
        V4 --> SV
        RT --> V1 & V2 & V3 & V4
    end

    subgraph Backend["backend/"]
        AC["AuthController"]
        PC["ProductController"]
        CC["CartController"]
        OC["OrderController"]
        PAY["PaymentController"]
        WH["WebhookController"]
        SC["SellerController"]
        AD["AdminController"]
        UC["UserController"]
        SUB["SubscriptionController"]
        REV["ProductReviewController"]
        MOD["Models + relationships"]
        MID["CheckAuth / CheckRole / VerifyCmsBridgeKey"]
        AC --> MOD
        PC --> MOD
        CC --> MOD
        OC --> MOD
        PAY --> MOD
        WH --> MOD
        SC --> MOD
        AD --> MOD
        UC --> MOD
        SUB --> MOD
        REV --> MOD
        MID --> AC
    end

    subgraph Infra["Infrastructure"]
        DB[("MySQL")]
        FS[("Image storage")]
        Q["Queue"]
    end

    SV -->|"JSON"| MID
    MOD --> DB
    PC --> FS
    OC --> Q
```

---

## 6. Deployment Architecture

```mermaid
flowchart TB
    subgraph Dev["Development"]
        D1["Vite dev server<br/>localhost:5173"]
        D2["php artisan serve<br/>127.0.0.1:8000"]
        D3[("SQLite database")]
        D4["WordPress (optional)<br/>local"]
        D1 -->|"VITE_API_BASE_URL"| D2 --> D3
        D4 -->|"X-CMS-Bridge-Key"| D2
    end

    subgraph Prod["Production target"]
        CDN["Static host / CDN<br/>built SPA"]
        LB["HTTPS reverse proxy<br/>TLS termination"]
        PHP["PHP-FPM workers<br/>Laravel API"]
        MY[("Managed MySQL 8<br/>InnoDB · backups")]
        OBJ[("Object storage<br/>product images")]
        QW["Queue worker"]
        WP2["WordPress host"]
        PM2["PayMongo"]
        CDN --> LB --> PHP
        PHP --> MY
        PHP --> OBJ
        QW --> MY
        WP2 -->|"secret header"| LB
        PM2 -->|"signed webhook"| LB
    end
```

| Concern | Development | Production |
|---|---|---|
| Frontend | `npm run dev` (Vite) | `npm run build` → static CDN |
| API | `php artisan serve` | PHP-FPM behind HTTPS reverse proxy |
| Database | SQLite file | Managed MySQL 8, automated backups |
| Images | `storage/app/public` | Object storage + CDN URLs |
| Queue | `QUEUE_CONNECTION=database` | Dedicated queue worker + supervisor |
| Mail | `MAIL_MAILER=log` or Mailpit | SMTP / transactional email provider |
| Secrets | `.env` (gitignored) | Secret manager / environment variables |
| CORS | localhost allow-list + pattern | Explicit production origins only |
| Tokens | Sanctum, 7-day expiry | Same, with rotation on role change |

---

## 7. Key Architectural Decisions

| Decision | Choice | Rationale | Trade-off |
|---|---|---|---|
| Frontend/backend split | Vue SPA + Laravel REST API | Clear ownership, independently deployable, testable API | More moving parts than a monolith |
| Authentication | Laravel Sanctum bearer tokens | First-party SPA friendly, simple revocation | Requires careful token lifetime handling |
| Authorization | Route middleware + controller guards | Defense in depth for RBAC | Rules duplicated in two places |
| Money storage | `DECIMAL(10,2)` + line-item snapshots | Audit-safe historical totals | Controlled denormalization |
| Escrow | Status on order and line item | Multi-seller orders release independently | More state transitions to test |
| Commission | Snapshot per line item | Plan changes never rewrite past payouts | Must compute at checkout |
| CMS | Headless WordPress sidecar | Marketing autonomy without DB coupling | Extra integration surface |
| CMS security | Shared secret + fail-closed | Prevents PII/report exposure | Manual key rotation |
| Payments | Hosted checkout + signed webhook | No card data touches the app | Dependency on PayMongo |
| Images | UUID filenames + MIME validation | Prevents traversal/collision | Cleanup job needed for orphans |

---

## 8. Non-Functional Architecture Qualities

- **Security:** HTTPS, CORS allow-list, rate limiting, Bcrypt, Sanctum expiry, RBAC, signed webhooks, shared-secret CMS bridge, server-side validation.
- **Integrity:** Transactions around stock, orders, escrow, and payouts; foreign keys; unique constraints; immutable financial snapshots.
- **Performance:** Indexed foreign keys and status columns, paginated catalog, eager loading (`with`) to avoid N+1 queries, aggregated analytics in one request.
- **Scalability:** Stateless API workers behind a load balancer; queue for notifications; object storage for images; read-heavy catalog cacheable at the CDN.
- **Maintainability:** Migrations as schema source of truth, service layer in `frontend/src/services`, consistent JSON envelope, feature tests.
- **Observability:** Structured Laravel logs, webhook audit entries, payout and escrow timestamps, health endpoint at `/up` and `/api`.

---

## 9. Related Documents

- [Use case diagram & specifications](./use-case.md)
- [Context diagram (Level-0 DFD)](./context.md)
- [Entity Relationship Diagram](./erd.md)
- [Database schema](../database/schema.sql)
- [Software Requirements Specification](../SRS/system-requirements.md)
- [Security controls](../security/security-controls.md)
- [Wireframes](./wireframes.md)
