# CraftMarket — Wireframes & UI/UX Specification

**Project:** CraftMarket — Local Handmade Products Marketplace  
**Design language:** Editorial Filipino-craft storefront — warm cream surfaces, forest green actions, terracotta accents, serif display headings  
**Responsive strategy:** Mobile-first fluid grid → tablet two-column → desktop twelve-column with persistent navigation

---

## 1. Design Tokens

| Token | Value | Usage |
|---|---|---|
| `--forest` | `#3E5943` | Primary buttons, active nav, headings |
| `--forest-deep` | `#2F4533` | Hover, footer, dashboard sidebar |
| `--cream` | `#FAF6F0` | Page background, cards |
| `--sand` | `#E8DFD5` | Borders, dividers, input outlines |
| `--terracotta` | `#C25E3E` | Sale accents, artisan badges, alerts |
| `--amber` | `#F59E0B` | Pending states, warnings |
| `--success` | `#2E7D32` | Paid, delivered, approved |
| `--danger` | `#C62828` | Rejected, cancelled, destructive |
| `--ink` | `#2D2420` | Body text |
| `--muted` | `#7A6F68` | Secondary text, metadata |
| Display font | Serif (e.g. Playfair-style) | Hero, section headings |
| Body font | Humanist sans-serif | UI text, tables, forms |

**Spacing scale:** 4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 px  
**Radius:** 8 px inputs, 10–12 px cards, 999 px pills  
**Breakpoints:** `< 480` small phone · `480–767` phone · `768–1023` tablet · `≥ 1024` desktop

---

## 2. Global Navigation — Desktop

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  ✦ CRAFTMARKET      Shop   Categories   Artisans   Journal        🔍  ♡  🛒  │
│  ──────────────────────────────────────────────────────────────────────────  │
│  [ Announcement bar: "Free shipping on orders ₱1,500+" · CMS-driven ]        │
└──────────────────────────────────────────────────────────────────────────────┘
```

| Region | Elements | Behaviour |
|---|---|---|
| Brand | Logo + wordmark | Links to `/` |
| Primary nav | Shop, Categories, Artisans, Journal | Active route underlined in forest |
| Utility | Search, wishlist count, cart count | Sticky on scroll; hidden labels on tablet |
| Account | Sign in / Register, or avatar menu | Avatar menu: Dashboard, Orders, Wishlist, Sign out |
| Announcement | CMS/WordPress content | Dismissible, hidden on small phones |

### 2.1 Global Navigation — Mobile

```text
┌───────────────────────────────┐
│ ☰   ✦ CRAFTMARKET     ♡  🛒  │
└───────────────────────────────┘

☰ opens a full-height drawer:
┌───────────────────────────────┐
│ ✕  CRAFTMARKET                │
│ ───────────────────────────── │
│ Shop                          │
│ Categories                    │
│ Artisans                      │
│ Journal                       │
│ ───────────────────────────── │
│ My Orders                     │
│ Wishlist                      │
│ Seller Dashboard (role-based) │
│ Admin Dashboard (role-based)  │
│ ───────────────────────────── │
│ Sign out                      │
└───────────────────────────────┘
```

**Accessibility:** `aria-expanded` on the hamburger, focus trap inside the drawer, `Esc` closes it, minimum 44×44 px touch targets.

---

## 3. Landing Page — Desktop

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  NAVIGATION                                                                  │
├──────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   ┌──────────────────────────────┐    ┌──────────────────────────────────┐  │
│   │  HANDMADE,                    │    │                                  │  │
│   │  HUMAN-MADE.                  │    │        [ Hero craft image ]      │  │
│   │                               │    │                                  │  │
│   │  Discover one-of-a-kind       │    │                                  │  │
│   │  crafts from Filipino         │    │                                  │  │
│   │  artisans.                    │    │                                  │  │
│   │                               │    │                                  │  │
│   │  [ Explore the Market → ]     │    │                                  │  │
│   └──────────────────────────────┘    └──────────────────────────────────┘  │
│                                                                              │
├──────────────────────────────────────────────────────────────────────────────┤
│  SHOP BY DISCIPLINE                                                          │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐                      │
│  │Ceramics│ │ Woven  │ │Jewelry │ │Candles │ │Woodcraft│                      │
│  │  12    │ │  8     │ │  15    │ │  9     │ │  11     │                      │
│  └────────┘ └────────┘ └────────┘ └────────┘ └────────┘                      │
├──────────────────────────────────────────────────────────────────────────────┤
│  FEATURED CRAFTS                                          View all →        │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐                 │
│  │ [image]    │ │ [image]    │ │ [image]    │ │ [image]    │                 │
│  │ ♡          │ │ ♡          │ │ ♡          │ │ ♡          │                 │
│  │ Name       │ │ Name       │ │ Name       │ │ Name       │                 │
│  │ Shop · ★4.8│ │ Shop · ★4.6│ │ Shop · ★5.0│ │ Shop · ★4.7│                 │
│  │ ₱1,250     │ │ ₱890       │ │ ₱2,100     │ │ ₱650       │                 │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘                 │
├──────────────────────────────────────────────────────────────────────────────┤
│  MEET THE MAKERS                                    [ artisan story cards ]  │
├──────────────────────────────────────────────────────────────────────────────┤
│  WHY CRAFTMARKET                                                             │
│  🛡 Escrow-protected   ✅ Verified artisans   📦 Tracked dispatch           │
├──────────────────────────────────────────────────────────────────────────────┤
│  JOURNAL / COMMUNITY UPDATES (CMS-driven)                                    │
├──────────────────────────────────────────────────────────────────────────────┤
│  FOOTER: Shop · Artisans · Support · Legal · Newsletter                      │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Mobile:** hero stacks (headline → CTA → image), disciplines become a horizontal scroll rail, featured crafts become a single-column list, why-cards stack.

---

## 4. Shop / Catalog Page

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  NAVIGATION                                                                  │
├──────────────────────────────────────────────────────────────────────────────┤
│  Shop                                                                        │
│  Home / Shop                                                                 │
│  ┌────────────────────────────────────────────────────────────────────────┐  │
│  │ 🔍 Search crafts, materials, or artisans…                              │  │
│  └────────────────────────────────────────────────────────────────────────┘  │
├───────────────┬──────────────────────────────────────────────────────────────┤
│ FILTERS       │  Showing 1–12 of 48 crafts            Sort: [ Newest ▾ ]     │
│               │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│ Category      │  │ [image]  │ │ [image]  │ │ [image]  │ │ [image]  │         │
│ ☐ Ceramics    │  │ ♡        │ │ ♡        │ │ ♡        │ │ ♡        │         │
│ ☐ Woven       │  │ Name     │ │ Name     │ │ Name     │ │ Name     │         │
│ ☐ Jewelry     │  │ ₱1,250   │ │ ₱890     │ │ ₱2,100   │ │ ₱650     │         │
│ ☐ Candles     │  │ ★4.8 (24)│ │ ★4.6 (9) │ │ ★5.0 (3) │ │ ★4.7 (11)│         │
│ ☐ Woodcraft   │  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│               │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐         │
│ Price         │  │   …      │ │   …      │ │   …      │ │   …      │         │
│ ₱0 ────● ₱5k  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘         │
│               │                                                              │
│ Availability  │                 [ ← Prev ]  Page 1 of 4  [ Next → ]          │
│ ☐ In stock    │                                                              │
│ ☐ Out of stock│                                                              │
│               │                                                              │
│ Artisan       │                                                              │
│ ☐ Cordillera  │                                                              │
│ ☐ Studio Clay │                                                              │
│               │                                                              │
│ [ Clear all ] │                                                              │
└───────────────┴──────────────────────────────────────────────────────────────┘
```

**States**

| State | UI |
|---|---|
| Loading | Skeleton cards (image block + two text bars) |
| Empty | Illustration + “No crafts match these filters” + Clear filters |
| Error | Inline retry banner; SPA falls back to sample data |
| Out of stock | Greyscale image + “Out of stock” pill; card remains browsable |

**Responsive:** Filters collapse into a `Filters (3)` drawer button below 1024 px; grid becomes 2 columns on tablet and 1 column on phone.

---

## 5. Product Detail Page

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  NAVIGATION                                                                  │
├──────────────────────────────────────────────────────────────────────────────┤
│  Home / Shop / Ceramics / Hand-Thrown Stoneware Mug                          │
├───────────────────────────────┬──────────────────────────────────────────────┤
│                               │  Studio Clayworks  ✅ Verified               │
│  ┌─────────────────────────┐  │  Hand-Thrown Stoneware Mug                   │
│  │                         │  │  ★★★★★ 4.8 · 24 reviews                      │
│  │     [ main image ]      │  │  ₱1,250.00                                   │
│  │                         │  │  ● In stock — 8 available                    │
│  └─────────────────────────┘  │                                              │
│  ┌────┐ ┌────┐ ┌────┐ ┌────┐ │  Material: Stoneware                         │
│  │thumb│ │thumb│ │thumb│ │thumb│ Dimensions: 9 × 8 cm                       │
│  └────┘ └────┘ └────┘ └────┘ │                                              │
│                               │  Quantity  [ − ]  1  [ + ]                   │
│                               │  [ Add to Cart ]      [ ♡ Save ]             │
│                               │  🛡 Escrow-protected · Free shipping ₱1,500+ │
├───────────────────────────────┴──────────────────────────────────────────────┤
│  ABOUT THIS CRAFT            │  MEET THE ARTISAN                            │
│  Hand-thrown…                │  [logo] Studio Clayworks · Quezon City       │
│                              │  “Small-batch pottery…”                      │
├──────────────────────────────────────────────────────────────────────────────┤
│  REVIEWS (24)                                                                │
│  ★★★★★ 4.8 average      5 ████████████ 18                                   │
│                          4 ████ 4                                            │
│                          3 ██ 2                                              │
│  ┌────────────────────────────────────────────────────────────────────────┐  │
│  │ Maria C. · Verified purchase · ★★★★★                                   │  │
│  │ “Beautiful glaze and weight. Arrived well packed.”                     │  │
│  └────────────────────────────────────────────────────────────────────────┘  │
│  [ Write a review ]  ← only shown when the customer is eligible              │
├──────────────────────────────────────────────────────────────────────────────┤
│  YOU MAY ALSO LIKE  [ 4 craft cards ]                                        │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Rules shown in UI**
- 404 page for products from unapproved storefronts (direct links never leak).
- “Write a review” is disabled with helper text when the buyer has no delivered purchase.
- Quantity is capped at available stock; the server still re-validates.

---

## 6. Cart Page

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  NAVIGATION                                                                  │
├──────────────────────────────────────────────────────────────────────────────┤
│  Your Cart (3 items)                                                         │
│  ┌────────────────────────────────────────────────┬─────────────────────────┐│
│  │ ┌──────┐ Hand-Thrown Mug          ₱1,250       │ ORDER SUMMARY           ││
│  │ │ img  │ Studio Clayworks         [−] 1 [+]    │ Subtotal      ₱3,340    ││
│  │ └──────┘                          Remove       │ Shipping      ₱150      ││
│  │ ─────────────────────────────────────────────  │ ─────────────────────── ││
│  │ ┌──────┐ Abaca Tote              ₱1,890       │ Total         ₱3,490    ││
│  │ │ img  │ Cordillera Weaves       [−] 1 [+]    │                         ││
│  │ └──────┘                          Remove       │ [ Proceed to Checkout ] ││
│  │ ─────────────────────────────────────────────  │                         ││
│  │ ┌──────┐ Soy Candle              ₱200         │ 🛡 Escrow protection    ││
│  │ │ img  │ Home Glow               [−] 1 [+]    │ 🔒 Secure checkout      ││
│  │ └──────┘                          Remove       │                         ││
│  └────────────────────────────────────────────────┴─────────────────────────┘│
│  [ ← Continue shopping ]                                                     │
└──────────────────────────────────────────────────────────────────────────────┘
```

**States:** empty cart (illustration + “Browse the market”), stock conflict (inline warning with adjusted quantity), free-shipping progress bar (“Add ₱160 more for free shipping”).

---

## 7. Checkout Page

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  Checkout                                                                    │
│  ┌──────────────────────────────────────────────┬──────────────────────────┐ │
│  │ 1. SHIPPING DETAILS                          │ ORDER SUMMARY            │ │
│  │ Full name   [ Maria Clara Santos          ]  │ 3 items                  │ │
│  │ Phone       [ +63 917 123 4567            ]  │ Mug ×1        ₱1,250     │ │
│  │ Address     [ Unit 402 Sunshine Towers…   ]  │ Tote ×1       ₱1,890     │ │
│  │ City/Prov   [ Pasig City                  ]  │ Candle ×1     ₱200       │ │
│  │ Postal      [ 1600                        ]  │ ───────────────────────  │ │
│  │ Notes       [ Leave at the lobby…         ]  │ Subtotal      ₱3,340     │ │
│  │                                              │ Shipping      ₱0         │ │
│  │ 2. PAYMENT METHOD                            │ Total         ₱3,340     │ │
│  │ (•) PayMongo — GCash / Card                   │                          │ │
│  │ ( ) Cash on Delivery                         │ [ Place Order ]          │ │
│  │                                              │                          │ │
│  │ ☑ I agree to the marketplace terms           │ 🛡 Funds held in escrow  │ │
│  └──────────────────────────────────────────────┴──────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Validation UX:** inline field errors, disabled submit until required fields are valid, server errors mapped to fields, no card data collected on-site (redirect to PayMongo).

---

## 8. Customer Dashboard

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  NAVIGATION                                                                  │
├──────────────────────────────────────────────────────────────────────────────┤
│  My Account                                                                  │
│  ┌───────────────┬──────────────────────────────────────────────────────────┐│
│  │ ● Overview    │  OVERVIEW                                                ││
│  │ ○ Orders      │  ┌────────────┐ ┌────────────┐ ┌────────────┐            ││
│  │ ○ Wishlist    │  │ Orders     │ │ In transit │ │ Delivered  │            ││
│  │ ○ Profile     │  │ 12         │ │ 2          │ │ 9          │            ││
│  │ ○ Addresses   │  └────────────┘ └────────────┘ └────────────┘            ││
│  │               │                                                          ││
│  │               │  RECENT ORDERS                          View all →      ││
│  │               │  ┌────────────────────────────────────────────────────┐  ││
│  │               │  │ CM-20261108-0042 · ₱3,340 · ● Shipped             │  ││
│  │               │  │ Studio Clayworks + 2 more · Nov 8, 2026           │  ││
│  │               │  │ [ Track order ]  [ View details ]                 │  ││
│  │               │  └────────────────────────────────────────────────────┘  ││
│  └───────────────┴──────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────────────────────┘
```

**Orders tab:** filter pills (All, Pending, Shipped, Delivered, Cancelled), per-item status timeline, tracking number, “Write a review” on delivered items.  
**Wishlist tab:** grid of saved crafts with remove and add-to-cart.  
**Profile tab:** name, email, phone, address, password change.

---

## 9. Seller Dashboard

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  CRAFTMARKET SELLER                                     🔔  Avatar ▾        │
├───────────────┬──────────────────────────────────────────────────────────────┤
│ ● Overview    │  Studio Clayworks                    ● Verification: Approved│
│ ○ Products    │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│ ○ Orders      │  │Products  │ │Orders    │ │Pending   │ │Revenue   │          │
│ ○ Reviews     │  │ 14       │ │ 36       │ │ 4        │ │ ₱48,250  │          │
│ ○ Analytics   │  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│ ○ Payouts     │                                                              │
│ ○ Subscription│  ┌──────────────────────────────────────────────────────────┐│
│               │  │ REVENUE ANALYTICS          ▲ 12.4% vs last month         ││
│               │  │ This Month ₱8,420  Last Month ₱7,490  AOV ₱1,340  8%      ││
│               │  │                                                          ││
│               │  │ ₱                                                        ││
│               │  │ │        ▂      ▄      ▃      ▅      ▆      █             ││
│               │  │ └──────────────────────────────────────────────────────  ││
│               │  │   Jun    Jul    Aug    Sep    Oct    Nov                 ││
│               │  │                                                          ││
│               │  │ TOP-SELLING CRAFTS                                       ││
│               │  │ 1  Stoneware Mug        18 sold        ₱22,500          ││
│               │  │ 2  Speckled Bowl        11 sold        ₱9,350           ││
│               │  └──────────────────────────────────────────────────────────┘│
│               │                                                              │
│               │  RECENT ORDERS                                              │
│               │  [ table: item · buyer · status · total · action ]          │
└───────────────┴──────────────────────────────────────────────────────────────┘
```

### 9.1 Seller — Products tab

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  Products (14 / 10 Basic limit)                  [ + Add New Craft ]         │
│  ⚠ You reached the Basic listing limit. Upgrade to Artisan Pro for unlimited.│
│  ┌────────────────────────────────────────────────────────────────────────┐  │
│  │ [img] Stoneware Mug   Ceramics   ₱1,250   8 in stock   Active   ⋮       │  │
│  │ [img] Speckled Bowl   Ceramics   ₱850    0 in stock   Out      ⋮       │  │
│  └────────────────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────────────┘
```

### 9.2 Seller — Payouts tab

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  Payouts                                                                     │
│  ┌────────────────┐ ┌────────────────┐ ┌────────────────┐                    │
│  │ Available      │ │ Reserved       │ │ Paid out       │                    │
│  │ ₱18,450        │ │ ₱2,000         │ │ ₱27,800        │                    │
│  └────────────────┘ └────────────────┘ └────────────────┘                    │
│  [ Request Payout ]                                                          │
│  Amount [ ₱ 2,000 ]  Method [ GCash ▾ ]  Account name [ … ]  Number [ … ]    │
│  ┌────────────────────────────────────────────────────────────────────────┐  │
│  │ Date       Amount     Method   Status      Action                      │  │
│  │ Nov 8      ₱2,000     GCash    Pending     [ Cancel ]                  │  │
│  │ Oct 21     ₱5,000     Bank     Paid        —                           │  │
│  └────────────────────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────────────────┘
```

---

## 10. Admin Dashboard

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  CRAFTMARKET ADMIN                                     🔔  Admin ▾           │
├───────────────┬──────────────────────────────────────────────────────────────┤
│ ● Dashboard   │  Platform Overview                                           │
│ ○ Users       │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│ ○ Sellers     │  │Users     │ │Sellers   │ │Products  │ │Orders    │          │
│ ○ Products    │  │ 248      │ │ 32       │ │ 186      │ │ 421      │          │
│ ○ Categories  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘          │
│ ○ Orders      │  ┌──────────────────────────┐ ┌──────────────────────────┐    │
│ ○ Payouts     │  │ REVENUE                  │ │ RECENT ACTIVITY          │    │
│ ○ Reports     │  │ Realized  ₱482,900       │ │ • Order CM-…-0042 placed │    │
│ ○ CMS         │  │ Commission ₱38,632       │ │ • New seller joined      │    │
│               │  │ Pro MRR   ₱5,980         │ │ • Payout requested       │    │
│               │  └──────────────────────────┘ └──────────────────────────┘    │
└───────────────┴──────────────────────────────────────────────────────────────┘
```

### 10.1 Admin — Sellers verification queue

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  Sellers            Status [ Pending ▾ ]   🔍 Search shop or email           │
│  ┌────────────────────────────────────────────────────────────────────────┐  │
│  │ Shop            Owner          Products  Status     Actions             │  │
│  │ Studio Clayworks Maria Santos  14        Pending    [ Review ]          │  │
│  │ Cordillera …    Elena Cruz     22        Approved   [ Review ]          │  │
│  └────────────────────────────────────────────────────────────────────────┘  │
│                                                                              │
│  REVIEW STOREFRONT — Studio Clayworks                                        │
│  Shop name [ Studio Clayworks ]  Location [ Quezon City ]                    │
│  Bio [ Small-batch pottery… ]                                                │
│  Notes [ Valid ID checked. ]                                                 │
│  [ Approve ]   [ Reject ]                                                    │
└──────────────────────────────────────────────────────────────────────────────┘
```

### 10.2 Admin — User audit modal with role assignment

```text
┌───────────────────────────────────────────────────────┐
│  USER ACCOUNT AUDIT                             ✕     │
│  Maria Clara Santos                                   │
│  maria@example.com · +63 917 123 4567                 │
│  System Role:  [ customer ]                           │
│  ┌─────────────────────────────────────────────────┐  │
│  │ Assign System Role:                             │  │
│  │ [ Seller (Artisan) ▾ ]   [ Save Role ]          │  │
│  │ Role changes take effect immediately. The user  │  │
│  │ will be asked to sign in again.                 │  │
│  └─────────────────────────────────────────────────┘  │
│  Account Status: ● Active                             │
│  [ Deactivate Account ]                               │
└───────────────────────────────────────────────────────┘
```

### 10.3 Admin — Reports tab

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│  Reports                                                                     │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐                        │
│  │Realized  │ │Orders    │ │AOV       │ │Commission│                        │
│  │₱482,900  │ │421       │ │₱1,147    │ │₱38,632   │                        │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘                        │
│                                                                              │
│  PLATFORM COMMISSION REVENUE              SELLER PAYOUT SETTLEMENT          │
│  Total commission ₱38,632   Pro MRR ₱5,980  Pending  ₱12,400                │
│  ┌─────────────────────────────────────┐  Approved  ₱6,000                  │
│  │ Escrow        Orders  Fees  Payouts │  Paid      ₱27,800                 │
│  │ released_to…  312     ₱31,200 ₱358k │  Rejected  2                       │
│  │ held          64      ₱4,100 ₱47k   │                                    │
│  └─────────────────────────────────────┘                                    │
│                                                                              │
│  BEST-SELLING PRODUCTS            MOST ACTIVE SELLERS                       │
└──────────────────────────────────────────────────────────────────────────────┘
```

---

## 11. Authentication Screens

```text
┌──────────────────────────────────────────────────────────────┐
│                    ✦ CRAFTMARKET                             │
│                                                              │
│              Welcome back to the market                      │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ Email     [ maria@example.com                        ] │  │
│  │ Password  [ ••••••••                             👁 ] │  │
│  │ ☐ Remember me                    Forgot password?     │  │
│  │ [ Sign in ]                                            │  │
│  │ ─────────────── or ───────────────                     │  │
│  │ New here?  [ Create an account ]                       │  │
│  └────────────────────────────────────────────────────────┘  │
│                                                              │
│  [ error banner: "Too many attempts. Try again in 60s." ]    │
└──────────────────────────────────────────────────────────────┘
```

**States:** field validation, invalid credentials, deactivated account, rate-limited (`429`), loading spinner on submit.

---

## 12. Responsive Behaviour Matrix

| Screen | Phone (< 768) | Tablet (768–1023) | Desktop (≥ 1024) |
|---|---|---|---|
| Navigation | Hamburger drawer, icon-only utilities | Collapsed labels | Full horizontal nav |
| Hero | Stacked, image below CTA | Two columns, reduced type | Two columns, full type |
| Catalog | 1–2 cards, filter drawer | 3 cards, filter drawer | 4 cards + sidebar filters |
| Product | Single column, sticky add-to-cart bar | Two columns | Gallery + info + reviews |
| Cart | Cards stack, summary on top | Two columns | Two columns |
| Checkout | Single column, summary accordion | Two columns | Two columns |
| Customer dashboard | Horizontal tab pills | Sidebar | Sidebar |
| Seller dashboard | Tab pills + stacked cards | Sidebar + 2-col cards | Sidebar + 4-col cards |
| Admin dashboard | Tab pills + stacked cards | Sidebar + 2-col | Sidebar + 4-col + tables |
| Tables | Horizontal scroll with sticky first column | Horizontal scroll | Full table |

---

## 13. UX Heuristics Applied

| Heuristic | Implementation |
|---|---|
| Visibility of system status | Stock badges, order timelines, escrow labels, payout statuses, loading skeletons |
| Match to real world | Filipino artisan vocabulary (“workshop”, “craft”, “maker”), ₱ formatting, GCash payout option |
| User control and freedom | Cancel pending payout, remove cart items, clear filters, close modals, back navigation |
| Consistency | Shared design tokens, one JSON envelope, uniform status badges |
| Error prevention | Server-side validation, disabled invalid actions, confirmation dialogs for destructive admin actions, stock caps |
| Recognition over recall | Persistent filters, saved wishlist, visible cart count, role-aware navigation |
| Efficiency | Debounced search, pagination, one-click wishlist, seller analytics in one request |
| Aesthetic and minimalist design | Editorial layout, generous whitespace, limited palette |
| Help users recover from errors | Inline field messages, retry banners, sample-data fallback when the API is offline |
| Help and documentation | Empty-state guidance, verification explanations, payout balance tooltips, defense guide |
