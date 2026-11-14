# CraftMarket — Business Proposal & Revenue Generation
## Required Component A: Business Proposal Specification

| Field | Detail |
|---|---|
| **Project Title** | Startup-Oriented Web Application Development with CMS and System Integration |
| **Application** | CraftMarket — Local Handmade Products Marketplace |
| **Courses** | Web Systems and Technologies 2 / System Integration and Architecture 1 (ITE311) |
| **Student** | Ashley Jay Sarmiento (BSIT F3) |
| **Document version** | 2.1 |
| **Status** | Implemented and verified |
| **Last updated** | November 2026 |

---

## 1. Executive Summary

CraftMarket is a **commission-based multi-vendor marketplace** for Philippine handmade goods. It connects independent artisans (potters, weavers, jewelers, candle makers, woodcrafters) with conscious shoppers who value provenance and craftsmanship.

The platform earns money in two complementary ways:

1. **Transaction commission** — 8% of every completed Basic-plan sale, reduced to 3% on the Artisan Pro plan.
2. **Artisan Pro subscription** — ₱299/month for unlimited listings, the lower commission rate, and a verified Pro badge.

CraftMarket holds **no inventory and owns no warehouse**. It monetizes the transaction layer: discovery, trust (escrow + reviews), fulfilment coordination, and settlement. This keeps fixed costs low and lets the platform scale with artisan activity rather than capital.

---

## 2. Problem Statement (Component A.1)

### 2.1 The Problem Being Solved

| Stakeholder | Pain point |
|---|---|
| Artisan | Social-media selling is chaotic: DMs replace order records, payments are unverified, and buyers ghost after committing. |
| Artisan | Large marketplaces charge high fees and bury handmade goods under mass-produced imports. |
| Artisan | No credible way to prove legitimacy or build a repeat customer base. |
| Shopper | Hard to verify that a product is genuinely handmade and that the seller is real. |
| Shopper | Paying strangers online feels risky without buyer protection. |

---

## 3. Value Proposition (Component A.3)

### 3.1 What Makes the System Useful

| For artisans | For shoppers |
|---|---|
| A ready-made storefront with listings, inventory, and order records | A curated catalog of verified Filipino craftsmanship |
| Escrow that guarantees the artisan gets paid after delivery | Funds held until delivery, reducing fraud risk |
| Built-in analytics (sales trend, top crafts, AOV) | Verified-purchase reviews instead of anonymous ratings |
| Lower commission on Pro (3% vs 8%) | Free shipping threshold and transparent shipping fees |
| QR-based dispatch confirmation for courier hand-off | Order tracking from confirmation to delivery |

### 3.2 Positioning Statement

> For Filipino artisans who want to sell beyond social media, **CraftMarket** is a curated, escrow-protected marketplace that turns craft stories into trustworthy storefronts — unlike general e-commerce platforms, we verify every shop and charge a transparent commission instead of upfront listing fees.

---

## 4. Target Users (Component A.2)

### 4.1 Primary User & Customer Segments

| Segment | Profile | Size signal | Why they buy/sell |
|---|---|---|---|
| **Micro-artisan sellers** | Solo makers or 1–5 person workshops, ₱5k–₱80k monthly craft sales, Metro Manila + Cordillera + CALABARZON | Thousands of MSME craft producers | Need order management and credibility without building a website |
| **Conscious shoppers** | 22–45, urban, socially aware, willing to pay a premium for handmade | Growing ethical-consumption segment | Want authenticity, provenance, and buyer protection |
| **Gift buyers** | Seasonal (Christmas, weddings, corporate giveaways) | High AOV during Q4 | Need curated, presentable, reliably delivered gifts |
| **Diaspora buyers** | Filipinos abroad sending gifts home | Large remittance-adjacent demand | Need local payment and local delivery without a local account |

### 3.2 Beachhead strategy

Start with **ceramics, woven goods, and home decor** in Metro Manila and nearby craft communities. These categories have:

- High visual appeal (strong for social and CMS content).
- Manageable shipping (non-perishable, lightweight-to-moderate).
- Repeat-purchase potential (homeware, gifts).
- Existing artisan clusters for supply-side acquisition.

---

## 5. Revenue Model & Income Generation Strategy (Component A.4)

| # | Stream | Mechanism | Rate | Implementation |
|---|---|---|---|---|
| R1 | **Basic commission** | Deducted from each completed line item | **8%** of item subtotal | `order_items.commission_rate = 8.00`, `platform_fee`, `seller_payout` |
| R2 | **Artisan Pro commission** | Reduced commission for subscribers | **3%** of item subtotal | `users.commission_rate = 3.00`, snapshot on each line item |
| R3 | **Artisan Pro subscription** | Recurring monthly plan | **₱299/month** | `artisan_subscriptions`, `subscription_plan = pro` |
| R4 | **Shipping fee** (pass-through) | Charged to buyer, not revenue | ₱150 below ₱1,500; free at ₱1,500+ | `orders.shipping_fee` |
| R5 | *Future* — Featured placement | Paid homepage/category boost | TBD | `products.is_featured` already exists |
| R6 | *Future* — Pro annual plan | Prepaid yearly discount | TBD | `billing_cycle` supports annual |

> R4 is deliberately **not** counted as revenue. It covers courier cost and is passed through to fulfilment.

### 4.1 Why 8% and 3%?

- **8%** sits below the effective take rate of general marketplaces once ads and boosts are included, while still funding escrow operations and support.
- **3%** is a strong, concrete upgrade incentive: a Pro seller saves **5 percentage points** on every sale.
- **₱299** is priced below the monthly savings of a modestly active seller, making the upgrade an obvious rational decision (see §6.2).

### 4.2 Commission example

A Basic seller sells a ₱1,250 mug:

| Line | Amount |
|---|---|
| Item subtotal | ₱1,250.00 |
| Platform commission (8%) | ₱100.00 |
| Seller payout (92%) | ₱1,150.00 |

The same sale on Artisan Pro:

| Line | Amount |
|---|---|
| Item subtotal | ₱1,250.00 |
| Platform commission (3%) | ₱37.50 |
| Seller payout (97%) | ₱1,212.50 |

The seller keeps **₱62.50 more per sale** on Pro. Five such sales per month (₱312.50 saved) already exceed the ₱299 subscription.

---

## 5. Cost Structure

### 5.1 Variable costs (scale with GMV)

| Cost | Assumption | Basis |
|---|---|---|
| Payment processing | ~3.5% of GMV | PayMongo card/GCash processing + friction |
| Payout transfer | ~₱15 per payout | GCash/bank transfer fee |
| Transactional email/SMS | ~₱2 per order | Notification provider |
| Support/moderation | ~1.5% of GMV | Human review of disputes and listings |
| **Total variable** | **~5.2% of GMV** | Before fixed costs |

### 5.2 Fixed monthly costs (Year-1 lean operation)

| Cost | Monthly |
|---|---|
| Cloud hosting + database | ₱3,000 |
| Domain, TLS, object storage/CDN | ₱700 |
| Transactional email | ₱1,000 |
| WordPress CMS hosting | ₱1,000 |
| Tools (monitoring, backups) | ₱1,500 |
| **Total fixed** | **₱7,200** |

Founder labor (development, moderation, artisan onboarding) is treated as sweat equity in Year 1.

---

## 6. Unit Economics

### 6.1 Contribution per order (Basic plan)

Assumptions: AOV = **₱1,200**, commission = **8%**, variable cost = **5.2% of GMV**.

| Line | Amount |
|---|---|
| Gross merchandise value (GMV) | ₱1,200.00 |
| Commission revenue (8%) | ₱96.00 |
| Variable cost (5.2%) | −₱62.40 |
| **Contribution margin per order** | **₱33.60** |
| Contribution margin % of revenue | 35.0% |

### 6.2 Artisan Pro break-even for the seller

| Line | Amount |
|---|---|
| Commission saved per ₱1,200 order (8% → 3%) | ₱60.00 |
| Monthly subscription | ₱299.00 |
| Orders needed to break even | 299 ÷ 60 = **≈ 5 orders/month** |

A seller doing **one order every six days** is better off on Pro. This is the core upgrade message.

### 6.3 Platform break-even

Using the conservative Basic-only contribution of ₱33.60/order and ₱7,200 fixed cost:

```text
Break-even orders/month = 7,200 ÷ 33.60 ≈ 215 orders/month
                        ≈ 7 orders/day
```

With Pro subscription revenue, break-even arrives sooner:

| Scenario | Pro sellers | Pro MRR | Remaining fixed cost | Orders needed |
|---|---:|---:|---:|---:|
| Basic only | 0 | ₱0 | ₱7,200 | 215 |
| Early traction | 15 | ₱4,485 | ₱2,715 | 81 |
| Healthy mix | 40 | ₱11,960 | ₱0 (covered) | 0 |

At 40 Pro subscribers, subscription revenue alone covers fixed costs, and every commission peso becomes contribution.

### 6.4 Year-1 illustrative projection (Month 12 run-rate)

| Metric | Conservative | Base | Optimistic |
|---|---:|---:|---:|
| Active sellers | 80 | 180 | 300 |
| Pro subscribers | 10 | 35 | 70 |
| Orders / month | 500 | 1,200 | 2,400 |
| AOV | ₱1,000 | ₱1,200 | ₱1,350 |
| GMV / month | ₱500,000 | ₱1,440,000 | ₱3,240,000 |
| Commission revenue (8%) | ₱40,000 | ₱115,200 | ₱259,200 |
| Pro MRR | ₱2,990 | ₱10,465 | ₱20,930 |
| **Total revenue / month** | **₱42,990** | **₱125,665** | **₱280,130** |
| Variable costs (~5.2%) | −₱26,000 | −₱74,880 | −₱168,480 |
| Fixed costs | −₱7,200 | −₱7,200 | −₱7,200 |
| **Operating contribution** | **₱9,790** | **₱43,585** | **₱104,450** |

Assumptions: blended 8% commission (conservative; Pro reduces it slightly), no paid marketing spend, founder-led operations.

---

## 7. Go-to-Market Strategy

| Phase | Focus | Tactics | Success signal |
|---|---|---|---|
| **0 — Seed (Months 1–2)** | Supply quality | Onboard 20–30 artisans from craft fairs, Facebook craft groups, and local maker communities; personally verify storefronts | 20 approved shops, 100+ listings |
| **1 — Trust (Months 3–4)** | Buyer confidence | Escrow messaging, verified-purchase reviews, artisan stories on the CMS, free-shipping threshold | First 100 completed orders, ≥ 4.5 average rating |
| **2 — Conversion (Months 5–8)** | Monetization | In-app Pro upgrade prompts using the seller's own analytics (“You'd save ₱X/month”), seasonal gift campaigns | 15+ Pro subscribers, 30% of GMV from repeat buyers |
| **3 — Scale (Months 9–12)** | Retention | Referral credits, featured placements, corporate gifting bundles, diaspora campaign | 180 sellers, 1,200 orders/month, positive contribution |

**Channels**
- **Social proof:** artisan reels and behind-the-scenes content republished through the headless CMS.
- **Community:** partner with local craft fairs and university organizations for seller onboarding.
- **SEO:** CMS-published artisan stories and gift guides targeting long-tail craft queries.
- **Referral:** give both buyer and seller a small credit after the first delivered order (future feature).

---

## 8. Competitive Landscape

| Alternative | Strength | Weakness CraftMarket exploits |
|---|---|---|
| Facebook/Instagram selling | Free, huge reach | No order records, no escrow, no catalog, DMs don't scale |
| Shopee / Lazada | Massive traffic, logistics | Handmade buried among mass-produced goods; ad costs erode margin |
| Etsy | Global craft brand | High fees, USD-first, weak local payment/logistics fit |
| Own Shopify site | Full control | Monthly fee + setup + no built-in local demand |
| **CraftMarket** | Verified local artisans, escrow, transparent commission, local payments | New marketplace, limited initial demand — mitigated by curation and community-led growth |

**Moat:** verified-artisan trust network, transaction and payout data, and artisan switching costs created by analytics and escrow history.

---

## 9. Key Metrics (KPIs)

| KPI | Definition | Year-1 target |
|---|---|---|
| GMV | Total value of completed orders | ₱1.4M/month |
| Take rate | Revenue ÷ GMV | 8–9% |
| Active sellers | Sellers with ≥ 1 listing and login in 30 days | 180 |
| Pro conversion | Pro subscribers ÷ active sellers | 20% |
| Repeat buyer rate | Buyers with ≥ 2 orders ÷ buyers | 30% |
| Order completion rate | Delivered ÷ placed | ≥ 90% |
| Dispute rate | Refunded/cancelled ÷ placed | ≤ 5% |
| Average rating | Mean verified review score | ≥ 4.5 |
| Contribution margin | Revenue − variable − fixed | Positive by Month 8 |
| Payout cycle time | Request → paid | ≤ 7 days |

These map directly to the admin Reports and seller Analytics features already implemented.

---

## 10. Risks and Mitigations

| Risk | Impact | Likelihood | Mitigation |
|---|---|---|---|
| **Disintermediation** (artisans move buyers off-platform) | High | High | Escrow protection, verified reviews, analytics, Pro pricing that is cheaper than losing platform demand, terms of service |
| Low initial buyer demand | High | Medium | Supply-first curation, CMS content, social campaigns, referral credits, campus/craft-fair partnerships |
| Payment gateway fees erode margin | Medium | High | Transparent pricing, Pro conversion, monitor take rate, negotiate volume rates |
| Counterfeit / non-handmade listings | High | Medium | Storefront verification, manual review, buyer reports, admin moderation |
| Logistics delays damage trust | High | Medium | QR dispatch confirmation, tracking numbers, seller SLA, escrow release only on delivery |
| Seller concentration (few sellers dominate GMV) | Medium | Medium | Recruit across categories and regions, feature emerging artisans |
| Data privacy incident | High | Low | RBAC, token expiry, shared-secret CMS bridge, least-privilege access, Data Privacy Act alignment |
| Regulatory/tax compliance (BIR, DTI) | Medium | Medium | Keep auditable ledgers, issue payout records, consult accountant before scaling |
| Seasonality (Q4 spike, Q1 slump) | Medium | High | Corporate gifting, non-seasonal homeware, pre-order campaigns |

---

## 11. Financial Controls Built Into the Product

| Control | Where it lives |
|---|---|
| Commission computed server-side, never by the client | `OrderController` line-item snapshot |
| Historical payouts immune to plan changes | `order_items.commission_rate` snapshot |
| Escrow released only on delivery | `OrderController` / `SellerController` status transitions |
| Payout cannot exceed released earnings | `SellerController::requestPayout` balance check |
| Admin cannot overpay a rejected request | `AdminController::updatePayoutRequest` state machine |
| Refunds reverse escrow | Cancel/refund flow updates order and items transactionally |
| Revenue reporting separated from pass-through shipping | Reports sum commission and subscriptions, not shipping |
| Subscription revenue visible as MRR | `AdminController::reports → subscription_revenue` |

---

## 12. Sustainability Statement

CraftMarket is designed to be **capital-light and contribution-positive at modest scale**:

- No inventory, warehousing, or logistics assets.
- Fixed costs under ₱8,000/month at launch.
- Two revenue streams that reinforce each other: commissions grow with GMV, subscriptions grow with seller success.
- Every financial movement is recorded in an auditable ledger, which is also a requirement for future compliance and fundraising diligence.

The business becomes self-sustaining at roughly **215 Basic orders/month**, or earlier as Pro subscriptions accumulate. Beyond break-even, incremental orders carry a ~35% contribution margin, giving the platform room to reinvest in demand generation and artisan support.
