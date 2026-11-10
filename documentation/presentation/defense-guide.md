# CraftMarket — Final Defense Guide

**Project:** CraftMarket — Local Handmade Products Marketplace  
**Course:** ITE311 — Web Systems and Technologies 2  
**Student:** Ashley Jay Sarmiento (BSIT F3)  
**Rubric total:** 100 points

---

## 1. Defense Strategy

The panel scores seven areas. Every demo step below is chosen to **produce visible evidence** for at least one rubric criterion.

| Rubric criterion | Weight | What the panel must see |
|---|---:|---|
| System Functionality | 30% | End-to-end flow: browse → cart → checkout → escrow → seller fulfilment → payout → admin reports |
| UI/UX & Responsiveness | 15% | Clean editorial design, consistent states, mobile/tablet/desktop layouts |
| Business Model & Revenue | 15% | Commission math, Pro subscription value, payout settlement, revenue reports |
| Database Design | 10% | ERD, schema, snapshots, FKs, transactions, indexes |
| Security & Validation | 10% | RBAC, verification gating, rate limiting, signed webhooks, tests |
| Documentation & System Design | 10% | SRS, use case, ERD, context, architecture, schema, wireframes |
| Presentation & Defense | 10% | Confident narrative, correct answers, honest limitations |

**Golden rule:** never demo a feature you cannot explain at the database level.

---

## 2. Pre-Defense Checklist

### 2.1 Environment

```powershell
# Backend
cd backend
php artisan migrate:fresh --seed
php artisan serve

# Frontend (new terminal)
cd frontend
npm run dev
```

- [ ] `backend/.env` has DB, PayMongo, `CMS_BRIDGE_KEY`, and CORS values.
- [ ] Database seeded successfully.
- [ ] Frontend opens at `http://localhost:5173`.
- [ ] API health check returns JSON at `http://127.0.0.1:8000/api`.
- [ ] Demo accounts verified (see below).
- [ ] Browser zoom at 100%; clean profile or incognito.

### 2.2 Demo accounts

| Role | Email | Password |
|---|---|---|
| Customer | `maria@example.com` | `password` |
| Seller — Cordillera Weaves | `artisan@example.com` | `password` |
| Seller — Studio Clayworks | `clayworks@example.com` | `password` |
| Admin | `admin@example.com` | `admin123` |

### 2.3 Evidence to have open in tabs

1. SPA — public catalog.
2. Seller dashboard — Overview (analytics).
3. Admin dashboard — Reports.
4. Admin dashboard — Sellers verification queue.
5. `documentation/diagrams/erd.md`
6. `documentation/diagrams/architecture.md`
7. `documentation/business-model.md`
8. Test output (`php artisan test`).

### 2.4 Run the tests before presenting

```powershell
cd backend
php artisan test
```

Expected: all suites pass, including `SecurityAndAuthorizationTest` (9 tests / 43 assertions).

---

## 3. Demo Script (12–15 minutes)

### 3.1 Opening (1 min) — Rubric: Presentation

> “CraftMarket is a decoupled marketplace that helps Filipino artisans sell beyond social media. It combines verified storefronts, escrow-protected payments, and two revenue streams: an 8% commission, reduced to 3% on our ₱299 Artisan Pro plan. The system is a Vue 3 SPA, a Laravel 12 REST API, and a MySQL database with a headless WordPress content sidecar.”

State the architecture and the business model **before** clicking anything. This frames the whole demo.

### 3.2 Public catalog and trust gating (2 min) — Functionality, UI/UX, Security

1. Open the Shop page.
2. Search “mug”, filter by Ceramics, adjust the price range, sort by price.
3. Point out the responsive grid and the persistent filter panel.
4. Open a product detail page; show gallery, stock, material, dimensions, seller, reviews.
5. Resize the browser to tablet and phone widths.

**Talking points**
- The public catalog only returns products from **approved** storefronts (`verification_status = approved` and `is_verified = true`).
- A direct URL to a pending seller's product returns `404`, so verification cannot be bypassed by guessing IDs.
- Filter facets use the same visibility rule, so counts never leak unverified listings.

### 3.3 Customer flow (3 min) — Functionality, Database

1. Log in as `maria@example.com`.
2. Add two crafts to the cart; show the persistent cart count.
3. Open the cart; update a quantity; remove an item.
4. Proceed to checkout; fill the shipping form.
5. Point out the shipping rule: **free at ₱1,500+, otherwise ₱150**.
6. Place the order (COD is fine for the demo).
7. Open My Orders; show the `CM-YYYYMMDD-XXXX` order number and per-item status.

**Talking points**
- The client never decides the price. The API locks the product rows, re-reads prices, recalculates subtotals, and snapshots `product_name` and `unit_price`.
- The order and line items are written in a single transaction; stock is decremented and the cart is cleared atomically.
- Each line item stores its own `commission_rate`, `platform_fee`, and `seller_payout` so historical payouts never change.

### 3.4 Seller flow (3 min) — Functionality, Business Model, UI/UX

1. Log out; log in as `artisan@example.com`.
2. Open the seller **Overview**.
3. Show Total Revenue, Pending/Completed Orders, Average Order Value, and the Revenue Analytics card.
4. Point out the six-month bar chart, month-over-month growth badge, and top-selling crafts list.
5. Open **Products**; show the listing limit for Basic.
6. Open **Payouts**; request a small payout within the available balance.

**Talking points**
- Analytics are computed server-side and scoped to the logged-in seller.
- Commission is 8% on Basic and 3% on Pro. The Pro plan costs ₱299/month; a seller making five ₱1,200 orders per month already saves ₱300, so Pro pays for itself.
- Payout requests are validated against `released earnings − reserved − paid`; a seller cannot withdraw money that has not cleared escrow.

### 3.5 Admin flow (3 min) — Functionality, Security, Business Model

1. Log out; log in as `admin@example.com`.
2. Open **Sellers**; show the verification queue.
3. Open a pending storefront, add a note, and approve it.
4. Switch back to the public catalog and show that the shop's products are now visible.
5. Open **Users**; open a user audit modal.
6. Show the role dropdown and **Save Role**.
7. Attempt to demote your own admin account — show the `422` error.
8. Open **Reports**; show commission revenue, Artisan Pro MRR, escrow ledger, and payout settlement cards.
9. Open **Payouts**; approve the seller's pending request, then mark it paid.

**Talking points**
- Admin product oversight uses `GET /api/admin/products`, which **can** see pending shops, while the public catalog cannot.
- Self-demotion, self-deactivation, and demoting the last admin are blocked.
- Promoting a customer to seller automatically provisions a pending storefront.
- Role changes and deactivations revoke all tokens, so the new permissions require a fresh login.
- The reports tab separates commission revenue, subscription MRR, and pass-through shipping.

### 3.6 Documentation and close (2 min) — Documentation, Presentation

1. Open `documentation/diagrams/erd.md`; point out relationships and the commission snapshot.
2. Open `documentation/diagrams/architecture.md`; explain the three tiers and trust boundaries.
3. Open `documentation/business-model.md`; quote break-even and unit economics.
4. Close with:

> “CraftMarket is not just a CRUD app. The escrow ledger, immutable commission snapshots, verification-gated catalog, role-safety guards, and payout ceiling are all enforced server-side and covered by automated tests. The business becomes self-sustaining at roughly 215 Basic orders per month, and Pro subscriptions can cover fixed costs before that.”

---

## 4. Rubric Mapping — What to Say

### 4.1 System Functionality (30%)

**Say:** “Every module is integrated end-to-end: a verified storefront feeds the catalog, the catalog feeds the cart and checkout, checkout creates an escrow ledger, delivery releases escrow, and the seller cashes out through a payout request that the admin settles.”

**Show:** complete order lifecycle + seller analytics + admin reports.

**Proof points**
- Multi-vendor line items: one order can contain items from different sellers, each with its own commission and payout.
- QR dispatch: `dispatch_code`, `dispatched_at`, `dispatch_confirmed_at`.
- Reviews require a delivered purchase.
- Artisan Pro changes commission and listing limits.
- CMS bridge feeds editorial content without touching transactional tables.

### 4.2 UI/UX & Responsiveness (15%)

**Say:** “The design uses an editorial craft palette, consistent design tokens, and a mobile-first responsive grid. Every async view has loading, empty, and error states.”

**Show:** resize the catalog, product page, seller dashboard, and admin reports.

**Proof points**
- Sidebar becomes tab pills on mobile.
- Tables scroll horizontally with sticky context.
- Touch targets meet 44 px.
- Destructive actions require confirmation.
- Status badges are consistent across orders, payouts, and verification.

### 4.3 Business Model & Revenue (15%)

**Say:** “We monetize two ways: an 8% commission, reduced to 3% for Pro subscribers at ₱299 per month. There is no inventory risk and fixed costs are under ₱8,000 per month.”

**Show:** admin Reports (commission, Pro MRR, payout summary) and seller Analytics.

**Numbers to memorize**

| Metric | Value |
|---|---|
| Basic commission | 8% (seller keeps 92%) |
| Pro commission | 3% (seller keeps 97%) |
| Pro price | ₱299/month |
| Seller Pro break-even | ~5 orders/month at ₱1,200 AOV |
| Shipping | ₱150, free at ₱1,500+ |
| Fixed monthly cost | ₱7,200 |
| Contribution per Basic order | ₱33.60 |
| Platform break-even | ~215 orders/month |
| Pro MRR coverage | 40 Pro sellers ≈ ₱11,960/month |

**Why the model is defensible:** the commission is tied to completed value, so artisans pay when they earn; Pro is priced below the savings of an active seller; escrow and reviews create switching costs.

### 4.4 Database Design & Data Management (10%)

**Say:** “The schema is in 3NF with controlled denormalization for financial audit. Every order line snapshots its price and commission so history cannot be rewritten.”

**Show:** ERD, then the `order_items` columns.

**Proof points**
- Foreign keys with cascade rules; financial rows retained.
- Unique constraints: email, slugs, order number, dispatch code, review/cart/wishlist pairs.
- Indexes on seller, category, status, escrow, payout status.
- Transactions around stock, escrow, refunds, and payouts.
- `lockForUpdate()` prevents overselling.

### 4.5 Security & Validation (10%)

**Say:** “Security is layered: Sanctum tokens, role middleware, ownership checks, server-side validation, rate limiting, signed webhooks, and a fail-closed CMS bridge.”

**Show:** RBAC test output + self-demotion block + unverified product 404 + CMS bridge 401.

**Proof points**
- Bcrypt passwords, hidden serialization.
- Token expiry and revocation on role change.
- `throttle:5,1` on login/register.
- HMAC-SHA256 webhook verification.
- CMS bridge shared secret with `hash_equals`.
- Upload MIME/size validation with UUID names.
- 9 security tests / 43 assertions.

### 4.6 Documentation & System Design (10%)

**Say:** “The documentation set includes an SRS with requirement IDs and acceptance criteria, a use case diagram with 22 specifications, an ERD, a Level-0 context diagram, an architecture diagram, a full schema, wireframes, a business model, and security controls.”

**Show:** the documentation index in `documentation/README.md`.

### 4.7 Presentation & Defense (10%)

- Rehearse the 12-minute path twice.
- Keep one browser window with prepared tabs.
- Never read from the screen; narrate the **why**.
- If something breaks, explain the intended flow and show the test or code that proves it.

---

## 5. Anticipated Questions & Answers

### Architecture

**Q: Why split the frontend and backend instead of using Blade?**  
A: The SPA gives a smoother, app-like shopping experience, and the REST API can later serve a mobile app. It also enforces a clean contract: the browser never computes authoritative totals.

**Q: Why Laravel and Vue?**  
A: Laravel provides migrations, Eloquent, Sanctum, validation, queues, and testing out of the box. Vue 3 with Vite gives fast builds and a component model that fits a dashboard-heavy marketplace.

**Q: What happens if the API goes down?**  
A: The SPA has a sample-data fallback for the catalog and home page so the interface remains demonstrable. Authenticated actions correctly fail with an error state.

### Database

**Q: Why store commission on each line item instead of only on the seller?**  
A: Because plans change. If a seller upgrades to Pro, past sales must still pay out at the old rate. The snapshot preserves audit correctness.

**Q: Where is the transaction boundary?**  
A: Order placement runs in one transaction: stock validation, line-item creation, total computation, stock decrement, and cart clearing. Escrow release and refunds also update the order and all affected items atomically.

**Q: How do you prevent two buyers from taking the last item?**  
A: The checkout reloads the product with `lockForUpdate()`, re-checks stock inside the transaction, and only then decrements. The second transaction sees the updated stock and fails validation.

**Q: Is the schema normalized?**  
A: Yes, 3NF. The only intentional denormalization is the financial snapshot on `order_items`, which is required for audit and reporting.

### Security

**Q: How do you stop a customer from reading another customer's orders?**  
A: The controller scopes queries to the authenticated user; sellers are scoped to their own `seller_id`; admins are the only role allowed to query everything. Feature tests verify `401`/`403` behavior.

**Q: What stops an admin from deleting themselves and locking the system?**  
A: Three guards: no self-demotion, no self-deactivation, and no demotion of the last remaining administrator. Each returns `422`.

**Q: How are payments secured?**  
A: CraftMarket never receives card data. PayMongo hosts checkout, and the webhook is verified with an HMAC-SHA256 signature before any state changes.

**Q: What about the public CMS bridge you mentioned?**  
A: It is no longer public. It now requires `X-CMS-Bridge-Key`, compared with `hash_equals`, and returns `503` if no key is configured, so it fails closed. There is a test for `401` and `200`.

**Q: How is the API protected from brute force?**  
A: Login and registration use `throttle:5,1` per IP, returning `429` after five attempts in a minute.

### Business

**Q: Why would a seller pay 8%?**  
A: Because the alternative is unpaid DMs and no buyer protection. The 8% funds escrow, dispute handling, and demand generation. Pro reduces it to 3%, and five orders a month already pays for the subscription.

**Q: How do you make money if everyone stays on Basic?**  
A: 8% of GMV. At a ₱1,200 average order and ~5.2% variable cost, each order contributes about ₱33.60. Fixed costs are ₱7,200/month, so break-even is about 215 orders/month.

**Q: What is your biggest business risk?**  
A: Disintermediation — artisans moving buyers off-platform after the first sale. We mitigate it with escrow, verified reviews, analytics, and Pro pricing that is cheaper than losing platform demand.

### UI/UX

**Q: How did you ensure responsiveness?**  
A: Mobile-first CSS with defined breakpoints, collapsing sidebars into tab pills, single-column card layouts, and horizontally scrollable tables. Verified at 360, 768, and 1440 px.

**Q: How do you handle empty and error states?**  
A: Every async panel has a loading skeleton, an empty-state message, and a retry/fallback path. The catalog also falls back to sample data when the API is offline.

### Testing

**Q: What did you test?**  
A: Escrow math and lifecycle, Pro payout, refund on cancellation, storefront verification, payout balance rules, RBAC, role-safety guards, catalog visibility, admin oversight, seller analytics, CMS bridge authentication, and report summaries.

**Q: Show me a failing case.**  
A: A guest hitting `/api/admin/reports` gets `401`; a customer or seller gets `403`; an unverified product detail returns `404`; an admin demoting themselves gets `422`.

---

## 6. Slide Outline (10–12 slides)

1. **Title** — CraftMarket, Ashley Jay Sarmiento, BSIT F3.
2. **Problem** — artisans rely on DMs; buyers lack trust.
3. **Solution** — verified storefronts + escrow + curated catalog.
4. **Architecture** — Vue SPA → Laravel API → MySQL; PayMongo; WordPress sidecar.
5. **Core flow** — browse → cart → checkout → escrow → delivery → payout.
6. **Database design** — ERD highlights, snapshots, transactions.
7. **Security** — RBAC, verification gating, rate limiting, signed webhooks, CMS secret.
8. **Business model** — 8%/3% commission, ₱299 Pro, unit economics, break-even.
9. **Demo screenshots** — catalog, seller analytics, admin reports.
10. **Testing evidence** — test counts and key cases.
11. **Roadmap** — 2FA, audit logs, courier API, referrals.
12. **Close** — “Handmade, human-made — and finally, fairly paid.”

---

## 7. Common Pitfalls to Avoid

| Pitfall | Better approach |
|---|---|
| Demoing only CRUD screens | Show the order → escrow → payout lifecycle |
| Claiming “secure” without evidence | Point to the specific test and middleware |
| Reading slides | Tell the story; use slides as backdrop |
| Hiding limitations | State them, then give the mitigation and roadmap |
| Forgetting the business numbers | Memorize commission, Pro price, break-even |
| Ignoring mobile | Resize live during the demo |
| Overclaiming “AI” or features not built | Only demo what exists in the repo |
| Not knowing the schema | Be ready to explain `order_items` snapshots and FKs |

---

## 8. One-Sentence Answers (Emergency Cheat Sheet)

- **What is CraftMarket?** A verified, escrow-protected marketplace for Filipino handmade crafts.
- **How does it make money?** 8% commission (3% on Pro) plus ₱299/month Artisan Pro subscriptions.
- **What is escrow?** The platform holds payment until the buyer receives the item, then releases the seller's share.
- **Why is verification important?** It prevents fake shops and keeps the public catalog trustworthy.
- **What is the core security control?** Server-side RBAC plus ownership checks, backed by Sanctum tokens.
- **What makes the database audit-safe?** Immutable line-item snapshots and transactional money movement.
- **What is the biggest risk?** Disintermediation, mitigated by escrow, reviews, analytics, and Pro pricing.
- **What would you build next?** MFA, centralized audit logs, courier API integration, and referral credits.
