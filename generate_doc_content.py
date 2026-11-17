"""
Comprehensive Content Generator for CraftMarket Documentation
Generates the complete SRS and SDD directly in Black and White format.
"""

import os
from docx.shared import Inches, Pt, RGBColor
from build_clean_bw_documentation import (
    create_document,
    add_styled_heading,
    add_p,
    add_bullet,
    add_callout,
    add_formatted_table,
    add_diagram,
    OUTPUT_FILE,
    DIAGRAM_DIR
)

def build_srs_section(doc):
    # START IMMEDIATELY - NO COVER PAGE, NO TOC, NO EXTRA FLUFF
    add_styled_heading(doc, "System Requirements Specification (SRS)", level=1)

    # ─────────────────────────────────────────────────────────────────────────
    # 1. INTRODUCTION (Expanded, rich, detailed, academic)
    # ─────────────────────────────────────────────────────────────────────────
    add_styled_heading(doc, "1. Introduction", level=2)

    add_styled_heading(doc, "1.1 Background and Socio-Economic Context", level=3)
    add_p(
        doc,
        "The Republic of the Philippines possesses one of the most culturally diverse and historically rich handicraft "
        "traditions in Southeast Asia. Across the archipelago's 7,641 islands, distinct indigenous communities and localized "
        "artisan clusters have preserved centuries-old creative traditions—including Cordillera backstrap weaving in the highlands, "
        "Paete woodcarving in Laguna, Bicolano abaca fiber braiding, Mindoro Mangyan nito basketry and organic calamansi agro-processing, "
        "Cebuano shellcraft and fashion jewelry, and Mindanao Yakan and Inabal sacred geometric tapestries. These handicraft micro-enterprises "
        "serve as the socio-economic backbone of rural and regional provincial economies, providing vital household income, sustaining cultural "
        "identity, and offering alternative livelihoods to agricultural families during off-peak seasons."
    )
    add_p(
        doc,
        "Despite their undeniable cultural value, exceptional craftsmanship, and the burgeoning global consumer appetite for authentic, "
        "sustainable, and handmade goods, grassroots Filipino artisans remain economically vulnerable and commercially marginalized. "
        "A primary driver of this disparity is the traditional multi-tiered supply chain. In conventional retail brick-and-mortar and "
        "consignment souvenir networks, commercial intermediaries, middlemen, and speculative traders systematically capture between 30% "
        "and 50% of the retail selling price. This leaves the actual craft creators with meager day wages that barely cover raw material "
        "procurement, directly disincentivizing younger generations from preserving their families' ancestral cultural heritage."
    )

    add_styled_heading(doc, "1.2 Structural Challenges in Modern Digital Commerce", level=3)
    add_p(
        doc,
        "With the rapid expansion of mobile telecommunications and social media penetration in the Philippines, many independent artisans "
        "have attempted to bypass predatory intermediaries by selling directly to consumers through informal social commerce channels, "
        "primarily Facebook Pages, Instagram profiles, and messaging groups (Messenger, Viber, and TikTok). However, informal social selling "
        "presents severe operational, financial, and logistical bottlenecks that inhibit enterprise sustainability and scale:"
    )
    add_bullet(
        doc,
        "Direct transactions via bank transfers or GCash personal QR codes force buyers to absorb 100% of the financial risk prior to dispatch, "
        "fostering deep consumer skepticism. Conversely, Cash-on-Delivery (COD) exposes micro-artisans to crippling cancellation losses from "
        "bogus buyers and 'joy reservers', who refuse parcel acceptance upon arrival, saddling creators with wasted labor and round-trip shipping fees.",
        "Absence of Escrow & Mutual Mistrust"
    )
    add_bullet(
        doc,
        "Grassroots artisans manage customer orders, inventory tallies, and custom requests through chaotic conversational chat threads. "
        "Critical order metadata, delivery addresses, and payment receipts routinely get lost, resulting in incorrect fulfillment, stockouts, "
        "and an absence of structured financial records required for formal credit or micro-finance evaluation.",
        "Operational Chaos & Conversational Fragmentation"
    )
    add_bullet(
        doc,
        "Mega-marketplaces such as Shopee and Lazada prioritize algorithmic volume, hyper-commoditized pricing, and algorithmic pay-to-play "
        "ad models that favor mass-produced, machine-molded plastic imports. Authentic handmade products, which naturally feature limited "
        "batch sizes and longer production cycles, are algorithmically buried, rendering them virtually invisible to discerning shoppers.",
        "Algorithmic Marginalization on Generic Platforms"
    )
    add_bullet(
        doc,
        "Archipelagic inter-island shipping across Luzon, Visayas, and Mindanao creates complex courier pricing structures. Independent "
        "artisans struggle to compute accurate logistics fees upfront, frequently leading to undercharged postage that erodes their profit margins "
        "or overcharged estimates that cause immediate cart abandonment.",
        "Inter-Island Logistics & Fragmented Shipping"
    )

    add_styled_heading(doc, "1.3 The Technological Landscape & Proposed Solution", level=3)
    add_p(
        doc,
        "The convergence of modern web engineering standards, cloud infrastructure, and regulated digital payment gateways provides an "
        "unprecedented opportunity to resolve these chronic structural inefficiencies. The CraftMarket platform is specifically engineered "
        "to serve as a decentralized, trust-gated, and specialized multi-vendor e-commerce marketplace dedicated exclusively to authentic "
        "Philippine handmade crafts, artisanal pantry goods, and cultural heritage products."
    )
    add_p(
        doc,
        "To decisively overcome the historical failure modes of grassroots social commerce, CraftMarket introduces a cohesive ecosystem "
        "governed by five foundational architectural innovations:"
    )
    add_bullet(
        doc,
        "All seller applicants must undergo formal administrative review. Proof of craft workshop authenticity, government identification, "
        "and production capability are audited before storefront activation. The public catalog is strictly gated, ensuring that unverified, "
        "counterfeit, or drop-shipped listings can never compromise marketplace integrity.",
        "1. Verified Artisan Storefronts"
    )
    add_bullet(
        doc,
        "CraftMarket incorporates a dedicated database-backed escrow state machine. Buyer payments are securely locked upon checkout and "
        "are released to the artisan's withdrawable ledger only when physical delivery is verified by the recipient or via automated courier "
        "settlement timelines, protecting both parties against transaction default.",
        "2. Automated Transactional Escrow Vault"
    )
    add_bullet(
        doc,
        "The platform natively integrates an automated zone matrix distinguishing intra-province (local), intra-island (same island group), "
        "and cross-island (Luzon to Visayas/Mindanao) shipments, ensuring transparent, equitable, and upfront shipping fee computation.",
        "3. Zone-Based National Logistics Engine"
    )
    add_bullet(
        doc,
        "Editorial storytelling, promotional announcements, and craft preservation blogs are managed via a decoupled headless WordPress instance. "
        "The WordPress CMS also functions as the exclusive administrative governance panel for critical lifecycle actions (artisan verification, "
        "account deactivation, category taxonomy CRUD), insulating core transactional databases from content editor exploits.",
        "4. Decoupled Headless CMS Governance"
    )
    add_bullet(
        doc,
        "The platform eliminates predatory middleman rates by establishing a fair 8% Basic commission structure, with an optional 3% Artisan "
        "Pro subscription tier (₱299/month), allowing thriving workshops to maximize their net earnings as their order volume scales.",
        "5. Sustainable Micro-Producer Monetization"
    )

    add_styled_heading(doc, "1.4 Purpose, Scope, and RFC Conventions of this Document", level=3)
    add_p(
        doc,
        "The purpose of this System Requirements Specification (SRS) is to establish a rigorous, formal, and verifiable contract defining "
        "the functional subsystems, behavioral interactions, operational workflows, and technical quality standards governing the CraftMarket "
        "application. This specification is designed to serve as the definitive evaluation benchmark for academic defense panels, software engineers, "
        "system evaluators, and test automation pipelines."
    )
    add_p(
        doc,
        "Requirement levels in this document adhere strictly to IETF RFC 2119 standards, wherein 'MUST' and 'SHALL' designate absolute mandatory "
        "compliance criteria, 'SHOULD' designates highly recommended architectural best practices, and 'MAY' identifies planned future enhancements."
    )

    add_styled_heading(doc, "1.5 Primary Stakeholder Roles & Responsibility Matrix", level=3)
    col_w = [Inches(1.8), Inches(2.2), Inches(2.0)]
    headers = ["Stakeholder Role", "System Authority & Governance", "Primary Technical Interfaces"]
    stakeholders_data = [
        [
            "Customer / Shopper",
            "Discovers authentic crafts, manages persistent cart/wishlist, executes escrow payments, confirms delivery, and posts verified reviews.",
            "Vue 3 Storefront SPA (`/products`, `/cart`, `/checkout`, `/dashboard`)."
        ],
        [
            "Artisan / Seller",
            "Manages workshop profile, publishes craft listings within subscription limits, fulfills orders, executes QR dispatch, and requests payouts.",
            "Seller Dashboard SPA (`/seller/dashboard`), Logistics Stepper, Payout Rail."
        ],
        [
            "System Administrator",
            "Conducts platform monitoring, reviews financial ledgers, inspects user audits, and administers onboarding and category taxonomy via CMS.",
            "Vue Admin Dashboard (`/admin`), WordPress CMS Bridge (`/wp-admin`)."
        ],
        [
            "Evaluation Panel",
            "Assesses decoupled architecture, relational 3NF schema, test pass rates, security compliance, and business model sustainability.",
            "Technical SRS/SDD Documentation, Automated PHPUnit Test Suite (47/47 passing)."
        ]
    ]
    add_formatted_table(doc, col_w, headers, stakeholders_data)

    # ─────────────────────────────────────────────────────────────────────────
    # 2. SYSTEM OBJECTIVES
    # ─────────────────────────────────────────────────────────────────────────
    add_styled_heading(doc, "2. Objectives", level=2)

    add_styled_heading(doc, "2.1 General Objective", level=3)
    add_p(
        doc,
        "To architect, implement, evaluate, and deploy a secure, responsive, and decoupled multi-vendor web marketplace tailored specifically "
        "to Philippine artisanal micro-enterprises, incorporating headless CMS content integration, automated financial escrow settlement, "
        "zone-based shipping computation, and role-based access control."
    )

    add_styled_heading(doc, "2.2 Specific Objectives", level=3)
    add_bullet(
        doc,
        "Design and implement a verified artisan onboarding and inspection pipeline where seller credentials and workshop backgrounds are audited "
        "via WordPress CMS before public listing permissions are granted.",
        "Specific Objective 1 (Trust & Verification)"
    )
    add_bullet(
        doc,
        "Develop an automated transactional escrow engine that securely holds buyer funds upon checkout and atomically releases net payouts to artisan "
        "balances upon physical delivery confirmation, mitigating both buyer and seller fraud.",
        "Specific Objective 2 (Financial Integrity)"
    )
    add_bullet(
        doc,
        "Implement a decoupled headless WordPress CMS architecture via REST API to power dynamic editorial announcements and artisan spotlight blogs, "
        "while enforcing administrative separation of concerns (SoC) for account governance and category taxonomy.",
        "Specific Objective 3 (CMS Integration & SoC)"
    )
    add_bullet(
        doc,
        "Engineer a zone-based shipping calculation matrix that programmatically evaluates origin and destination island groups (Local, Same Island, "
        "Cross-Island Luzon/Visayas/Mindanao) with an automatic free-shipping tier for qualifying baskets.",
        "Specific Objective 4 (Logistics Automation)"
    )
    add_bullet(
        doc,
        "Provide authenticated artisans with real-time operational analytics, including 6-month gross revenue visual trends, top-performing crafts, "
        "payout request ledgers, and printable PDF-grade invoice receipts.",
        "Specific Objective 5 (Artisan Analytics & Tooling)"
    )
    add_bullet(
        doc,
        "Establish an immutable audit trail using line-item financial snapshots (`commission_rate`, `platform_fee`, `seller_payout`) to ensure historical "
        "ledger accuracy regardless of future plan upgrades or platform pricing shifts.",
        "Specific Objective 6 (Auditability & Compliance)"
    )
    add_bullet(
        doc,
        "Achieve 100% test passing rate across automated unit and feature test suites verifying RBAC boundaries, escrow state transitions, role safety guards, "
        "and CMS bridge security.",
        "Specific Objective 7 (Automated Quality Assurance)"
    )

    # ─────────────────────────────────────────────────────────────────────────
    # 3. SYSTEM SCOPE & BOUNDARIES
    # ─────────────────────────────────────────────────────────────────────────
    add_styled_heading(doc, "3. Scope", level=2)

    add_styled_heading(doc, "3.1 In-Scope Capabilities", level=3)
    add_p(doc, "The CraftMarket system implementation strictly incorporates the following functional and architectural boundaries:")
    add_bullet(doc, "Bcrypt-hashed password authentication, session management via Laravel Sanctum bearer tokens, and route guards enforcing Customer, Seller, and Admin boundaries.", "Role-Based Access Control (RBAC)")
    add_bullet(doc, "Faceted product catalog with real-time debounced keyword searching, category taxonomy filtering, price range constraints, and verified storefront gating.", "Catalog Discovery")
    add_bullet(doc, "Pinia-managed reactive client shopping cart with server-side stock validation, price locking, and atomic stock decrements upon checkout.", "Cart & Escrow Checkout")
    add_bullet(doc, "PayMongo sandbox integration supporting simulated GCash, Maya, and Card payment authorizations, alongside Cash-on-Delivery (COD) support.", "Payment Gateway Simulation")
    add_bullet(doc, "5-stage order fulfillment tracker (Pending -> Processing -> Shipped -> In Transit -> Delivered) with 6-digit cryptographic verification codes and QR dispatch confirmation.", "Order Fulfillment & Tracking")
    add_bullet(doc, "Tiered seller monetization: Free tier (8% commission, 5 product limit) and Artisan Pro tier (3% commission, unlimited listings, ₱299/mo).", "Artisan Pro Subscriptions")
    add_bullet(doc, "Verified review engine permitting 1-to-5 star ratings and written feedback strictly for authenticated buyers with confirmed delivered orders.", "Customer Review Verification")
    add_bullet(doc, "Headless WordPress synchronization via REST API for marketing announcements and blogs, plus dedicated WordPress admin menus for seller onboarding, account deactivation, and taxonomy.", "WordPress CMS Bridge")

    add_styled_heading(doc, "3.2 Out-of-Scope Capabilities (Future Enhancements)", level=3)
    add_p(doc, "The following areas are explicitly documented as out-of-scope for the current production release and are reserved for future architectural iterations:")
    add_bullet(doc, "Direct real-time GPS telemetry from external courier fleets (e.g., J&T, Lalamove); fulfillment tracking relies on internal state transitions confirmed by artisans and buyers.", "Real-Time Courier Fleet GPS")
    add_bullet(doc, "Multi-currency foreign exchange conversions; all financial ledgers, listings, and payout calculations are denominated in Philippine Peso (PHP / ₱).", "Foreign Currency Exchange")
    add_bullet(doc, "Native mobile platform compilation (iOS Swift / Android Kotlin); the system is architected as an ultra-responsive progressive web application (PWA) optimized for mobile browsers.", "Native Compiled Mobile Apps")
    add_bullet(doc, "Live audio/video streaming consultations; custom order negotiation is handled via structured in-app inquiry modals.", "Real-Time Video Consultation")

    # ─────────────────────────────────────────────────────────────────────────
    # 4. FUNCTIONAL REQUIREMENTS
    # ─────────────────────────────────────────────────────────────────────────
    add_styled_heading(doc, "4. Functional Requirements", level=2)
    add_p(
        doc,
        "The functional behavior of CraftMarket is formally specified across thirteen (13) comprehensive subsystems. "
        "Each requirement outlines user stories, technical preconditions, step-by-step execution workflows, postconditions, and exception handling."
    )

    reqs = [
        {
            "id": "FR-01",
            "title": "Authentication, Session Security & Role-Based Access Control (RBAC)",
            "actor": "Guest, Customer, Seller, Admin",
            "story": "As a platform user, I want to securely register and log in so that I can access features authorized for my specific role.",
            "pre": "User possesses a valid email address and network connectivity.",
            "steps": [
                "User enters email, password, and selects intended role (customer or seller).",
                "Backend validates email uniqueness and enforces minimum 8-character password complexity.",
                "System hashes password using Bcrypt with work factor 12.",
                "Upon successful login, system issues a stateful Laravel Sanctum Bearer token with 7-day expiration.",
                "Frontend stores token in Pinia auth store and attaches Bearer header to subsequent Axios API requests.",
                "Vue Router navigation guards intercept navigation attempts, redirecting unauthorized roles to appropriate dashboards."
            ],
            "post": "Authenticated session established; role-specific dashboard views accessible.",
            "err": "Invalid credentials return 422 Unprocessable Entity or 401 Unauthorized; throttle middleware blocks brute force after 5 attempts/minute."
        },
        {
            "id": "FR-02",
            "title": "User Profile, Address Book & Security Management",
            "actor": "Customer, Seller, Admin",
            "story": "As an account holder, I want to update my profile details, default shipping address, and password to keep my account secure.",
            "pre": "User is authenticated with a valid Sanctum token.",
            "steps": [
                "User accesses `/profile` view.",
                "System fetches current profile information (name, email, phone, shipping address).",
                "User modifies details and submits form.",
                "Backend validates inputs and persists changes via Eloquent ORM.",
                "Password updates require verification of the existing password before saving new Bcrypt hash."
            ],
            "post": "Profile data updated in `users` table; session reflects updated identity.",
            "err": "Incorrect current password triggers 422 validation failure; duplicate email submissions are blocked."
        },
        {
            "id": "FR-03",
            "title": "Artisan Verification & Workshop Storefront Onboarding (WordPress CMS Authority)",
            "actor": "Seller, Administrator (WordPress CMS)",
            "story": "As an artisan, I want to submit my workshop credentials so that the administrator can audit and verify my storefront in WordPress CMS.",
            "pre": "User is registered with 'seller' role; `seller_profiles` record initialized with `pending` status.",
            "steps": [
                "Artisan provides shop name, biography, workshop address, contact phone, and craft specialty in Seller Dashboard.",
                "Artisan profile status is marked `verification_status = 'pending'`, and public listing is locked.",
                "Administrator logs into WordPress Admin Panel (`/wp-admin`) and navigates to CraftMarket CMS -> Artisan Verification.",
                "Administrator audits submitted workshop credentials, enters moderator notes, and selects 'Approve' or 'Reject'.",
                "On approval, `verification_status` updates to `'approved'`, `is_verified` is set to `true`, and artisan catalog publishing is unlocked.",
                "Artisan receives an automated email notification detailing the onboarding verdict and moderator feedback.",
                "Administrator can also toggle [Deactivate Account] / [Activate Account] directly from this WordPress page."
            ],
            "post": "Verified artisan storefront published; seller unlocked to list crafts; Vue Admin Dashboard displays read-only inspection.",
            "err": "Incomplete submissions remain pending; rejected applications require resolution of moderator notes before re-audit."
        },
        {
            "id": "FR-04",
            "title": "Product Catalog & Category Taxonomy Management (WordPress CMS Authority)",
            "actor": "Artisan / Seller, Administrator (WordPress CMS)",
            "story": "As an artisan, I want to publish craft listings, while administrators govern catalog taxonomies through WordPress CMS.",
            "pre": "Seller is authenticated and verified (`verification_status = 'approved'`).",
            "steps": [
                "Seller accesses Products tab in Seller Dashboard.",
                "Seller inputs title, description, category, price, stock, materials, dimensions, and origin province.",
                "Seller uploads high-resolution product photography (persisted in public disk storage).",
                "System validates that listing count does not exceed the seller's active plan quota (5 for Basic, unlimited for Pro).",
                "Listing appears immediately in public catalog with active status.",
                "Administrators manage category taxonomies (Add, Edit, Delete) exclusively in WordPress CMS (`wp-admin/admin.php?page=craftmarket-categories`).",
                "Category deletion is strictly blocked with HTTP 422 if existing products reference that category."
            ],
            "post": "Product saved in `products` table; category taxonomy maintained with foreign key integrity.",
            "err": "Validation failure on negative price, non-numeric inventory, or attempt to delete non-empty categories."
        },
        {
            "id": "FR-05",
            "title": "Product Discovery, Debounced Live Search & Faceted Filtering",
            "actor": "Customer, Guest",
            "story": "As a buyer, I want to search and filter crafts by category, price range, and origin province with instantaneous responsiveness.",
            "pre": "Products exist in database with `status = 'active'` from approved storefronts.",
            "steps": [
                "Customer accesses `/products` catalog view.",
                "Search input triggers a 300ms debounced API query (`/api/products?search=...`) to avoid server thrashing.",
                "Faceted sidebar allows multi-category selection, price range slider adjustments, and region filtering.",
                "Backend applies dynamic query scopes and returns paginated JSON results.",
                "UI renders responsive grid of `ProductCard.vue` components with lazy-loaded imagery."
            ],
            "post": "Filtered craft catalog presented accurately with matching item counts.",
            "err": "Zero matches render an empty state with suggestions to broaden filter criteria."
        },
        {
            "id": "FR-06",
            "title": "Shopping Cart & Multi-Vendor Checkout Engine",
            "actor": "Customer",
            "story": "As a customer, I want to add handmade crafts from multiple artisans into a unified cart and checkout with automated zone shipping.",
            "pre": "Customer is authenticated; selected items have available inventory.",
            "steps": [
                "Customer clicks 'Add to Cart' on product cards or detail pages.",
                "Pinia cart store validates stock and increments quantity.",
                "Administrator accounts are strictly blocked from adding items to cart to preserve transactional auditability.",
                "Cart view itemizes products, unit prices, seller names, subtotal, automated shipping fees, and grand total.",
                "At `/checkout`, customer provides shipping recipient, phone, and delivery address.",
                "System performs backend atomic stock lock (`lockForUpdate`) before initializing payment."
            ],
            "post": "Order created with status `'pending'` and payment status `'unpaid'`; inventory reserved.",
            "err": "If requested quantity exceeds available inventory, cart clamps quantity to available stock and notifies user."
        },
        {
            "id": "FR-07",
            "title": "Payment Authorization & PayMongo Sandbox Integration",
            "actor": "Customer, Payment Gateway",
            "story": "As a buyer, I want to pay for my craft order via GCash, Maya, or Credit/Debit card so that my purchase is immediately confirmed.",
            "pre": "Order created with status `'pending'`; PayMongo sandbox API keys configured.",
            "steps": [
                "Checkout redirects buyer to PayMongo Sandbox selector (`/payment/sandbox/:orderId`).",
                "Customer selects payment method (GCash, Maya, Card) and triggers payment simulation.",
                "System issues payment intent to PayMongo sandbox endpoint.",
                "On successful authorization, PayMongo returns transaction reference (`pm_txn_...`).",
                "System logs record in `payments` table with gateway reference, method, amount, and status `'paid'`.",
                "Order status transitions to `'processing'` and escrow lock is initiated."
            ],
            "post": "Order marked `'paid'`; funds locked in marketplace escrow vault.",
            "err": "Failed or canceled transactions leave order in `'pending'` state with option to re-attempt checkout."
        },
        {
            "id": "FR-08",
            "title": "Escrow Vault & Financial State Machine",
            "actor": "System, Customer, Seller, Administrator",
            "story": "As a marketplace operator, I want payments locked in escrow until physical delivery is verified so that buyers and artisans are protected.",
            "pre": "Order has confirmed payment in `payments` table.",
            "steps": [
                "Upon payment confirmation, escrow state is set to `'held'`.",
                "Platform commission is calculated per item based on seller tier: 8% Basic or 3% Artisan Pro.",
                "Artisan net earnings (`item_total - commission_fee`) are computed and snapshotted on `order_items`.",
                "Funds remain held while order traverses `'processing'`, `'shipped'`, and `'in_transit'`.",
                "When customer confirms delivery (or via 7-day auto-settlement), escrow transitions to `'released'`.",
                "Released earnings are credited to the artisan's verified withdrawable balance in `seller_profiles`."
            ],
            "post": "Artisan withdrawable balance incremented; transaction logged in immutable financial ledger.",
            "err": "Disputed orders lock escrow indefinitely pending administrative arbitration."
        },
        {
            "id": "FR-09",
            "title": "Order Fulfillment, Zone Shipping & QR Logistics Tracking",
            "actor": "Seller, Customer, Courier",
            "story": "As a buyer and seller, I want to track order fulfillment across a 5-stage logistics stepper with cryptographic QR confirmation.",
            "pre": "Order is confirmed and paid.",
            "steps": [
                "Order appears in Seller Dashboard with status `'processing'`.",
                "Seller packages items and clicks 'Dispatch Order', triggering transition to `'shipped'`.",
                "System generates cryptographic 6-digit verification code and scannable QR payload.",
                "Courier marks parcel `'in_transit'` upon pickup.",
                "Customer verifies delivery by entering the 6-digit code or clicking 'Confirm Delivery Received'.",
                "Order status transitions to `'delivered'`; customer can generate printable PDF-grade tax invoice receipt."
            ],
            "post": "Order completed; escrow released; buyer unlocked to submit product review.",
            "err": "Invalid verification codes reject delivery confirmation attempt."
        },
        {
            "id": "FR-10",
            "title": "Artisan Payout Request, Validation & Disbursal Audit",
            "actor": "Seller, Administrator",
            "story": "As an artisan, I want to withdraw my cleared escrow earnings to my bank or e-wallet so that I receive my workshop income.",
            "pre": "Seller has cleared available balance >= minimum threshold (₱500.00).",
            "steps": [
                "Seller accesses Payouts tab in Seller Dashboard.",
                "System displays cleared available balance, locked escrow balance, and lifetime earnings.",
                "Seller submits payout request specifying amount, channel (GCash, Maya, BDO, BPI), account name, and number.",
                "System validates that `requested_amount <= available_balance` and deducts amount into pending state.",
                "Payout record inserted into `payout_requests` table with status `'pending'`.",
                "Administrator audits payout queue in Admin Dashboard (`/admin#payouts`).",
                "Administrator transfers funds via banking rail, inputs bank reference, and marks request `'approved'`."
            ],
            "post": "Payout marked `'approved'` or `'completed'`; seller notified of cash-out.",
            "err": "Requests exceeding cleared balance are rejected with HTTP 422 validation failure."
        },
        {
            "id": "FR-11",
            "title": "Artisan Pro Subscription & Monetization Model",
            "actor": "Seller, System",
            "story": "As an active seller, I want to upgrade to Artisan Pro to lower my transaction commission from 8% to 3% and publish unlimited listings.",
            "pre": "Seller is authenticated and verified.",
            "steps": [
                "Seller accesses Subscription tab in Seller Dashboard.",
                "System presents plan comparison: Basic Tier (8% fee, 5 products) vs Artisan Pro (3% fee, unlimited products, ₱299/mo).",
                "Seller selects plan and completes subscription payment simulation.",
                "System creates or updates record in `subscriptions` table with active validity dates.",
                "`seller_profiles.subscription_plan` is updated to `'pro'`, and commission rate is set to 3.00%.",
                "All subsequent order line items automatically snapshot the 3% Pro rate."
            ],
            "post": "Artisan Pro benefits active; reduced fees applied immediately to subsequent sales.",
            "err": "Expired subscriptions automatically revert to Basic Tier (8% commission) upon cron evaluation."
        },
        {
            "id": "FR-12",
            "title": "Verified Customer Reviews & Rating Feedback",
            "actor": "Customer, Public",
            "story": "As a buyer who received an order, I want to submit a 1-to-5 star review and comment so that other shoppers can trust the artisan.",
            "pre": "Customer purchased product and order status is strictly `'delivered'`.",
            "steps": [
                "Customer views order history or product details page.",
                "System verifies purchase authenticity via SQL join (`orders` + `order_items` + `payments`).",
                "If verified, 'Write a Verified Review' action is unlocked.",
                "Customer selects rating (1 to 5 stars) and enters review text.",
                "System saves record in `product_reviews` with `is_verified_purchase = true`.",
                "Product average rating and review count recalculate automatically in catalog cards."
            ],
            "post": "Review published publicly; artisan workshop rating updated.",
            "err": "Unverified buyers or users without completed delivery cannot submit reviews."
        },
        {
            "id": "FR-13",
            "title": "Decoupled Headless WordPress CMS Integration & Administrative Governance",
            "actor": "Content Editor, Administrator (WordPress CMS), Public",
            "story": "As a platform visitor, I want to read official craft announcements and blogs, while administrators manage governance through WordPress CMS.",
            "pre": "WordPress instance running with CraftMarket CMS Bridge plugin active and configured.",
            "steps": [
                "Storefront homepage initiates asynchronous HTTP fetch to WordPress REST API (`/wp-json/wp/v2/posts`).",
                "System parses JSON response, extracting titles, excerpts, featured media, and published dates.",
                "Announcements banner renders above hero section; educational craft stories render in blog section.",
                "If WordPress server is offline, Vue 3 catches network error and displays curated local fallback content with zero crash.",
                "In WordPress Admin (`wp-admin`), administrator manages: (1) Product status moderation, (2) Category taxonomy CRUD, (3) Artisan storefront verification, (4) User and Artisan account deactivation/activation.",
                "Vue Admin Dashboard operates strictly as a read-only monitoring surface for users and sellers, enforcing Separation of Concerns."
            ],
            "post": "Editorial content displayed cleanly; administrative governance centralized in WordPress CMS; Vue dashboard is read-only.",
            "err": "Network timeouts trigger graceful fallback banner with no console errors or transactional disruption."
        }
    ]

    for req in reqs:
        add_styled_heading(doc, f"{req['id']}: {req['title']}", level=3)
        add_p(doc, req['story'], bold_prefix="User Story:")
        add_p(doc, req['actor'], bold_prefix="Primary Actor:")
        add_p(doc, req['pre'], bold_prefix="Prerequisites:")
        add_p(doc, "Step-by-Step Execution Workflow:", bold_prefix="Execution Flow:")
        for idx, step in enumerate(req['steps'], 1):
            add_bullet(doc, step, bold_prefix=f"Step {idx}")
        add_p(doc, req['post'], bold_prefix="Post-conditions:")
        add_p(doc, req['err'], bold_prefix="Exception Handling:")
        doc.add_paragraph().paragraph_format.space_after = Pt(2)

    # ─────────────────────────────────────────────────────────────────────────
    # 5. NON-FUNCTIONAL REQUIREMENTS
    # ─────────────────────────────────────────────────────────────────────────
    add_styled_heading(doc, "5. Non-Functional Requirements", level=2)
    add_p(
        doc,
        "The CraftMarket system adheres to stringent quality, reliability, performance, and security engineering standards "
        "structured across six (6) fundamental non-functional requirement pillars:"
    )

    col_w = [Inches(1.2), Inches(1.5), Inches(2.1), Inches(1.2)]
    headers = ["NFR Identifier", "Quality Attribute", "Specification & Technical Threshold", "Verification Method"]
    nfr_data = [
        [
            "NFR-PERF-01",
            "API Latency & Response",
            "All core REST API endpoints must respond in <= 300ms under standard 50-user concurrent load. Vite SPA bundles load in <= 1.5s.",
            "Automated Benchmark Tests & Lighthouse Audit"
        ],
        [
            "NFR-SEC-01",
            "Authentication & Hashing",
            "Stateless token management using Laravel Sanctum. Passwords encrypted using Bcrypt with work factor 12. HTTPS enforced.",
            "PHPUnit Feature Tests & Security Code Audits"
        ],
        [
            "NFR-SEC-02",
            "Role & Data Segregation",
            "Strict server-side policy authorization (`Authorize` middleware). Admins barred from cart/checkout; sellers restricted to own inventory.",
            "403 Forbidden automated unit and integration tests"
        ],
        [
            "NFR-SEC-03",
            "CMS Bridge Security",
            "Endpoints under `/api/cms-bridge/*` protected by `X-CMS-Bridge-Key` with timing-safe `hash_equals` and fail-closed 503 behavior.",
            "PHPUnit CmsBridgeTest Suite (13 dedicated tests)"
        ],
        [
            "NFR-REL-01",
            "Fault Tolerance & Fallback",
            "Graceful degradation when external WordPress REST API or PayMongo sandbox is unreachable; fallback content displayed with zero crash.",
            "Simulated network partition & API mock failure tests"
        ],
        [
            "NFR-REL-02",
            "Transactional Integrity",
            "All financial mutations (orders, escrow, payouts) wrapped in ACID database transactions with automatic rollback on error.",
            "PHPUnit DatabaseTransactions test suite (47/47 passing)"
        ],
        [
            "NFR-USE-01",
            "Accessibility & Aesthetics",
            "Compliance with WCAG 2.1 Level AA color contrast. Warm editorial artisan palette. Fully responsive across 360px, 768px, 1440px.",
            "Lighthouse Accessibility Audit & Responsive DevTools"
        ],
        [
            "NFR-MNT-01",
            "Separation of Concerns",
            "Clean decoupling between Vue 3 presentation, Laravel 12 transactional API, and WordPress CMS governance. Vue Admin is read-only for users/sellers.",
            "Architecture Review & Component Audit"
        ],
        [
            "NFR-SCL-01",
            "Database Indexing",
            "Foreign keys on all relational tables with cascade constraints. Composite indexes on `products(category_id, price)` and `orders(user_id, status)`.",
            "MySQL EXPLAIN query optimization analysis"
        ]
    ]
    add_formatted_table(doc, col_w, headers, nfr_data)

    doc.add_page_break()

def build_sdd_section(doc):
    # ─────────────────────────────────────────────────────────────────────────
    # SYSTEM DESIGN DOCUMENTS
    # Exact requested order:
    # 1. Use Case Diagram
    # 2. ER Diagram
    # 3. Context Diagram
    # 4. Architecture Diagram
    # 5. Database Schema
    # 6. Wireframes
    # ─────────────────────────────────────────────────────────────────────────
    add_styled_heading(doc, "System Design Documents", level=1)

    # ─── 1. USE CASE DIAGRAM ─────────────────────────────────────────────────
    add_styled_heading(doc, "Use Case Diagram", level=2)
    add_p(
        doc,
        "The Use Case Diagram defines the interactions between external human actors (Guest, Customer, Seller, Admin) "
        "and automated external software systems (PayMongo Payment Gateway, WordPress CMS) across the twenty-two (22) "
        "formal use cases of the CraftMarket platform.",
        bold_prefix="Behavioral Scope:"
    )

    uc_img = os.path.join(DIAGRAM_DIR, "3_usecase_diagram.png")
    add_diagram(doc, uc_img, "Figure 1: CraftMarket Role-Based Use Case Diagram", width=Inches(6.2))

    col_w = [Inches(1.0), Inches(1.8), Inches(1.4), Inches(1.8)]
    headers = ["UC ID", "Use Case Title", "Primary Actor", "Technical Subsystem"]
    uc_data = [
        ["UC-01", "Browse Public Craft Catalog", "Guest, Customer", "Product Catalog Subsystem"],
        ["UC-02", "Search & Filter Crafts", "Guest, Customer", "Catalog Faceted Search"],
        ["UC-03", "View Product Details & Reviews", "Guest, Customer", "Catalog Details & Ratings"],
        ["UC-04", "User Registration & Authentication", "All Users", "Sanctum Auth & RBAC"],
        ["UC-05", "Manage Shopping Cart", "Customer", "Pinia Cart State Subsystem"],
        ["UC-06", "Manage Wishlist", "Customer", "Wishlist Subsystem"],
        ["UC-07", "Place Escrow-Protected Order", "Customer", "Checkout & Order Engine"],
        ["UC-08", "Execute Payment (PayMongo / COD)", "Customer, PayMongo", "Payment Gateway Subsystem"],
        ["UC-09", "Track Order Fulfillment Status", "Customer, Seller", "Logistics Stepper & QR"],
        ["UC-10", "Submit Verified Product Review", "Customer", "Review Verification Engine"],
        ["UC-11", "Manage Artisan Product Listings", "Artisan / Seller", "Seller Product CRUD"],
        ["UC-12", "Fulfill Order & Confirm QR Dispatch", "Artisan, Courier", "QR Logistics Subsystem"],
        ["UC-13", "View Revenue & Sales Analytics", "Artisan / Seller", "Analytics Engine (Chart.js)"],
        ["UC-14", "Request Escrow Earnings Payout", "Artisan / Seller", "Payout Validation Rail"],
        ["UC-15", "Audit Storefront Verification", "Admin (WordPress CMS)", "CMS Artisan Verification"],
        ["UC-16", "Moderate Products & Categories", "Admin (WordPress CMS)", "CMS Taxonomy & Moderation"],
        ["UC-17", "User Oversight & Account Toggle", "Admin (WordPress CMS)", "CMS User Lifecycle Bridge"],
        ["UC-18", "Audit Escrow & Settle Payouts", "System Administrator", "Admin Financial Ledgers"],
        ["UC-19", "View Platform Operational Reports", "System Administrator", "Admin Reporting Engine"],
        ["UC-20", "Subscribe to Artisan Pro Tier", "Artisan / Seller", "Subscription Subsystem"],
        ["UC-21", "Synchronize CMS Blogs & Notices", "WordPress CMS, Public", "Headless CMS REST API"],
        ["UC-22", "Process Payment Gateway Webhooks", "PayMongo, System", "Webhook Controller"]
    ]
    add_formatted_table(doc, col_w, headers, uc_data)

    # ─── 2. ER DIAGRAM ───────────────────────────────────────────────────────
    add_styled_heading(doc, "ER Diagram", level=2)
    add_p(
        doc,
        "The Entity-Relationship (ER) Diagram specifies the relational database schema of the CraftMarket system, "
        "enforcing strict Third Normal Form (3NF) compliance, foreign key relational constraints, cascading rules, "
        "and controlled denormalization snapshots on line-item financials.",
        bold_prefix="Relational Topology:"
    )

    erd_img = os.path.join(DIAGRAM_DIR, "4_er_diagram.png")
    add_diagram(doc, erd_img, "Figure 2: CraftMarket Entity-Relationship Diagram (ERD)", width=Inches(6.2))

    col_w = [Inches(1.8), Inches(1.5), Inches(2.7)]
    headers = ["Relational Entity", "Cardinality & Target", "Business Rule & Integrity Constraint"]
    erd_data = [
        ["users", "1 : 1 seller_profiles", "Every seller user MUST possess exactly one workshop profile."],
        ["users", "1 : M orders", "A customer user may place zero or multiple marketplace orders."],
        ["users", "1 : M product_reviews", "A customer may author multiple reviews; restricted to delivered products."],
        ["categories", "1 : M products", "Each category groups multiple products; deletion blocked if products exist."],
        ["seller_profiles", "1 : M products", "An artisan workshop owns multiple catalog craft listings."],
        ["products", "1 : M product_images", "A product listing holds up to five gallery images."],
        ["products", "1 : M order_items", "A product may be ordered across multiple separate customer orders."],
        ["orders", "1 : M order_items", "An order contains one or more line items from one or multiple sellers."],
        ["orders", "1 : 1 payments", "An order links to exactly one payment transaction record."],
        ["seller_profiles", "1 : M payout_requests", "An artisan may submit multiple payout disbursement requests."]
    ]
    add_formatted_table(doc, col_w, headers, erd_data)

    # ─── 3. CONTEXT DIAGRAM ──────────────────────────────────────────────────
    add_styled_heading(doc, "Context Diagram", level=2)
    add_p(
        doc,
        "The System Context Diagram (Level-0 Data Flow Diagram) establishes the formal boundary of the CraftMarket "
        "application, illustrating how human actors and external cloud software services exchange data with the "
        "central system hub.",
        bold_prefix="Architectural Boundary:"
    )

    ctx_img = os.path.join(DIAGRAM_DIR, "1_context_diagram.png")
    add_diagram(doc, ctx_img, "Figure 3: CraftMarket System Context Diagram (DFD Level 0)", width=Inches(6.2))

    col_w = [Inches(1.5), Inches(1.5), Inches(3.0)]
    headers = ["External Entity", "Entity Classification", "Primary Inbound / Outbound Data Exchanges"]
    ctx_data = [
        [
            "Customer / Shopper",
            "Human User",
            "Inbound: Search queries, cart items, checkout credentials, delivery confirmations, reviews.\nOutbound: Product listings, order tracking status, printable receipts, shop info."
        ],
        [
            "Artisan / Seller",
            "Human User",
            "Inbound: Workshop credentials, craft listings, stock adjustments, dispatch codes, payout requests.\nOutbound: Sales metrics, order alerts, escrow status, payout disbursement confirmations."
        ],
        [
            "System Administrator",
            "Human User",
            "Inbound: Storefront audits, category CRUD, account activation/deactivation via WordPress CMS.\nOutbound: Platform-wide telemetry, financial reports, system audit logs."
        ],
        [
            "PayMongo Sandbox",
            "External API",
            "Inbound: Payment intents, webhook event notifications (payment.paid, payment.failed).\nOutbound: Payment source tokens, simulated gateway transaction IDs (`pm_txn_...`)."
        ],
        [
            "WordPress REST CMS",
            "External Headless Service",
            "Inbound: HTTP GET requests to `/wp-json/wp/v2/posts` and `/pages`.\nOutbound: Promotional announcements, artisan spotlights, craft educational articles (JSON format)."
        ]
    ]
    add_formatted_table(doc, col_w, headers, ctx_data)

    # ─── 4. ARCHITECTURE DIAGRAM ─────────────────────────────────────────────
    add_styled_heading(doc, "Architecture Diagram", level=2)
    add_p(
        doc,
        "The System Architecture Diagram illustrates the decoupled multi-tier topology of CraftMarket, separating the "
        "presentation tier (Vue 3 Single Page Application), the application tier (Laravel 12 REST API), the relational "
        "data tier (MySQL 8 / InnoDB), and the external headless content and governance sidecar (WordPress CMS).",
        bold_prefix="System Topology:"
    )

    arch_img = os.path.join(DIAGRAM_DIR, "2_architecture_diagram.png")
    add_diagram(doc, arch_img, "Figure 4: CraftMarket Decoupled Multi-Tier System Architecture", width=Inches(6.2))

    col_w = [Inches(1.5), Inches(1.8), Inches(2.7)]
    headers = ["Architectural Tier", "Technologies & Frameworks", "Primary Operational Responsibilities"]
    arch_data = [
        [
            "Presentation Tier",
            "Vue.js 3, Vite, Vue Router 4, Pinia, Axios, DOMPurify",
            "Client-side reactive rendering, client routing, optimistic cart state, form validation, and responsive mobile-first UI."
        ],
        [
            "Application Tier",
            "PHP 8.2+, Laravel 12, Laravel Sanctum, Eloquent ORM",
            "Stateless REST API routing, Sanctum token authentication, RBAC authorization, escrow state transitions, and business logic."
        ],
        [
            "Relational Data Tier",
            "MySQL 8.x / InnoDB (or SQLite for local dev)",
            "ACID transactional persistence, 3NF schema, foreign key cascade constraints, indexing, and financial line-item snapshots."
        ],
        [
            "Content & Governance",
            "WordPress Headless CMS, CraftMarket Sync Plugin",
            "Editorial announcements and blogs via WP REST API; authoritative administrative governance for storefront verification, category CRUD, and account toggles."
        ],
        [
            "External Services",
            "PayMongo Sandbox, SMTP / Mailpit",
            "Hosted checkout simulation, signed webhook event ingestion, transactional email notifications."
        ]
    ]
    add_formatted_table(doc, col_w, headers, arch_data)

    # ─── 5. DATABASE SCHEMA ──────────────────────────────────────────────────
    add_styled_heading(doc, "Database Schema", level=2)
    add_p(
        doc,
        "The Database Schema Data Dictionary provides a comprehensive, field-level specification of all relational tables "
        "in the CraftMarket database, detailing column names, SQL data types, keys, nullability, and functional descriptions.",
        bold_prefix="Data Dictionary:"
    )

    tables = [
        {
            "name": "users",
            "desc": "Stores primary user credentials, global roles, contact details, and subscription tiers.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique auto-incrementing user identifier."],
                ["name", "VARCHAR(255)", "-", "NO", "Full legal name or display name of the user."],
                ["email", "VARCHAR(255)", "UNI", "NO", "Unique email address utilized for login authentication."],
                ["password", "VARCHAR(255)", "-", "NO", "Bcrypt-encrypted password hash (work factor 12)."],
                ["role", "ENUM", "-", "NO", "Global RBAC role: 'customer', 'seller', or 'admin'."],
                ["phone", "VARCHAR(20)", "-", "YES", "Contact telephone number."],
                ["address", "TEXT", "-", "YES", "Default delivery or workshop street address."],
                ["subscription_plan", "ENUM", "-", "NO", "Tier: 'free', 'basic', or 'pro' (default: 'free')."],
                ["is_active", "BOOLEAN", "-", "NO", "Account activation flag (1 = Active, 0 = Deactivated)."],
                ["created_at", "TIMESTAMP", "-", "YES", "Timestamp of initial account registration."]
            ]
        },
        {
            "name": "seller_profiles",
            "desc": "Contains artisan workshop metadata, verification records, location, and cleared escrow balance.",
            "cols": [Inches(1.5), Inches(1.1), Inches(0.8), Inches(0.6), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique seller profile record identifier."],
                ["user_id", "BIGINT UNSIGNED", "FK, UNI", "NO", "Foreign key referencing `users.id` (1:1 constraint)."],
                ["shop_name", "VARCHAR(255)", "-", "NO", "Public trading name of the artisan workshop."],
                ["shop_slug", "VARCHAR(255)", "UNI", "NO", "SEO-optimized URL slug for storefront routing."],
                ["shop_description", "TEXT", "-", "YES", "Detailed biography and workshop craft background."],
                ["location", "VARCHAR(255)", "-", "YES", "Workshop location (Municipality, Province, Region)."],
                ["is_verified", "BOOLEAN", "-", "NO", "Storefront approval flag (1 = Approved, 0 = Pending/Rejected)."],
                ["verification_status", "ENUM", "-", "NO", "Workflow state: 'pending', 'approved', or 'rejected'."],
                ["available_balance", "DECIMAL(10,2)", "-", "NO", "Cleared, withdrawable net earnings from delivered orders."],
                ["total_earnings", "DECIMAL(10,2)", "-", "NO", "Cumulative lifetime gross earnings processed."]
            ]
        },
        {
            "name": "categories",
            "desc": "Catalog classification taxonomy governed through WordPress CMS.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique category taxonomy identifier."],
                ["name", "VARCHAR(255)", "-", "NO", "Human-readable category label (e.g., Textiles, Ceramics)."],
                ["slug", "VARCHAR(255)", "UNI", "NO", "URL slug for faceted filtering."],
                ["description", "TEXT", "-", "YES", "Editorial description of the craft taxonomy."]
            ]
        },
        {
            "name": "products",
            "desc": "Catalog craft listings with inventory stock, pricing, and origin province.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique craft listing identifier."],
                ["seller_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing `users.id` (workshop owner)."],
                ["category_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing `categories.id`."],
                ["name", "VARCHAR(255)", "-", "NO", "Title of the handmade craft product."],
                ["slug", "VARCHAR(255)", "UNI", "NO", "URL slug for product detail routing."],
                ["price", "DECIMAL(10,2)", "-", "NO", "Unit retail selling price denominated in Philippine Peso (₱)."],
                ["stock", "INT", "-", "NO", "Current available physical inventory quantity."],
                ["status", "ENUM", "-", "NO", "Catalog status: 'active', 'inactive', 'out_of_stock', 'archived'."],
                ["materials", "VARCHAR(255)", "-", "YES", "Primary artisanal raw materials (e.g., Abaca, Clay)."],
                ["origin_province", "VARCHAR(255)", "-", "YES", "Philippine province where the craft was fabricated."]
            ]
        },
        {
            "name": "orders",
            "desc": "Master transactional order records tracking buyer details, logistics status, and totals.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Internal relational order identifier."],
                ["order_number", "VARCHAR(50)", "UNI", "NO", "Formatted reference code: `CM-YYYYMMDD-XXXX`."],
                ["user_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing purchasing `users.id`."],
                ["total_amount", "DECIMAL(10,2)", "-", "NO", "Grand total payable including items and shipping fee."],
                ["shipping_fee", "DECIMAL(10,2)", "-", "NO", "Zone-based shipping fee calculated at checkout."],
                ["status", "ENUM", "-", "NO", "Lifecycle: 'pending', 'processing', 'shipped', 'delivered', 'cancelled'."],
                ["payment_status", "ENUM", "-", "NO", "Financial status: 'unpaid', 'paid', 'refunded'."],
                ["payment_method", "ENUM", "-", "NO", "Channel: 'paymongo', 'cod', 'wallet'."],
                ["dispatch_code", "VARCHAR(6)", "UNI", "YES", "Cryptographic 6-digit confirmation code for delivery."],
                ["shipping_address", "TEXT", "-", "NO", "Full recipient delivery address and contact phone."]
            ]
        },
        {
            "name": "order_items",
            "desc": "Individual line items with immutable financial commission and payout snapshots.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique order line item identifier."],
                ["order_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing parent `orders.id`."],
                ["product_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing purchased `products.id`."],
                ["seller_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing artisan `users.id`."],
                ["quantity", "INT", "-", "NO", "Purchased item count."],
                ["unit_price", "DECIMAL(10,2)", "-", "NO", "Historical unit price snapshot at time of checkout."],
                ["subtotal", "DECIMAL(10,2)", "-", "NO", "Computed line total (`quantity * unit_price`)."],
                ["commission_rate", "DECIMAL(5,2)", "-", "NO", "Commission percentage snapshot (8.00% or 3.00%)."],
                ["platform_fee", "DECIMAL(10,2)", "-", "NO", "Deducted marketplace platform fee."],
                ["seller_payout", "DECIMAL(10,2)", "-", "NO", "Net payout owed to artisan (`subtotal - platform_fee`)."],
                ["escrow_status", "ENUM", "-", "NO", "State: 'held', 'released', 'refunded', 'disputed'."]
            ]
        },
        {
            "name": "payments",
            "desc": "Payment gateway audit transactions and gateway transaction references.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique payment record identifier."],
                ["order_id", "BIGINT UNSIGNED", "FK, UNI", "NO", "Foreign key referencing `orders.id` (1:1 constraint)."],
                ["amount", "DECIMAL(10,2)", "-", "NO", "Authorized monetary amount in Philippine Peso."],
                ["payment_method", "VARCHAR(50)", "-", "NO", "Method utilized: 'gcash', 'maya', 'card', 'cod'."],
                ["gateway_ref", "VARCHAR(255)", "-", "YES", "External gateway transaction identifier (`pm_txn_...`)."],
                ["status", "ENUM", "-", "NO", "Transaction outcome: 'paid', 'pending', 'failed', 'refunded'."]
            ]
        },
        {
            "name": "payout_requests",
            "desc": "Artisan cash-out withdrawal requests against cleared available escrow balances.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique payout request identifier."],
                ["seller_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing artisan `users.id`."],
                ["amount", "DECIMAL(10,2)", "-", "NO", "Requested withdrawal amount (min: ₱500.00)."],
                ["payment_method", "VARCHAR(50)", "-", "NO", "Selected payout channel (e.g., GCash, Maya, BPI, BDO)."],
                ["account_name", "VARCHAR(255)", "-", "NO", "Registered legal name on recipient banking account."],
                ["account_number", "VARCHAR(100)", "-", "NO", "Banking or e-wallet account number."],
                ["status", "ENUM", "-", "NO", "Disbursal state: 'pending', 'approved', 'rejected', 'completed'."],
                ["reference_no", "VARCHAR(255)", "-", "YES", "Bank transfer reference number provided by admin."]
            ]
        },
        {
            "name": "product_reviews",
            "desc": "Verified customer feedback and 1-to-5 star ratings for delivered orders.",
            "cols": [Inches(1.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(2.0)],
            "headers": ["Field Name", "Data Type", "Key", "Null", "Field Description"],
            "rows": [
                ["id", "BIGINT UNSIGNED", "PK", "NO", "Unique review identifier."],
                ["product_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing evaluated `products.id`."],
                ["user_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing reviewing `users.id`."],
                ["order_id", "BIGINT UNSIGNED", "FK", "NO", "Foreign key referencing delivered `orders.id`."],
                ["rating", "TINYINT UNSIGNED", "-", "NO", "Integer rating scale from 1 (lowest) to 5 (highest)."],
                ["comment", "TEXT", "-", "YES", "Qualitative review comment submitted by customer."],
                ["is_verified", "BOOLEAN", "-", "NO", "Audit flag confirming delivered purchase authenticity (1 = True)."]
            ]
        }
    ]

    for t in tables:
        add_styled_heading(doc, f"Table: {t['name']}", level=3)
        add_p(doc, t['desc'], italic=True)
        add_formatted_table(doc, t['cols'], t['headers'], t['rows'])

    # ─── 6. WIREFRAMES ───────────────────────────────────────────────────────
    add_styled_heading(doc, "Wireframes", level=2)
    add_p(
        doc,
        "The System Wireframes illustrate the structural layout, information architecture, navigation controls, "
        "and responsive UI component hierarchy across the primary views of the CraftMarket web application.",
        bold_prefix="User Interface Blueprints:"
    )

    wireframes = [
        ("5_wireframe_homepage.png", "Figure 5: Wireframe Blueprint — Public Marketplace Homepage & Discovery"),
        ("6_wireframe_product_inquiry.png", "Figure 6: Wireframe Blueprint — Custom Product Negotiation & Inquiry Modal"),
        ("6_wireframe_seller_dashboard.png", "Figure 7: Wireframe Blueprint — Artisan Workshop Management & Analytics Dashboard"),
        ("7_wireframe_order_tracking.png", "Figure 8: Wireframe Blueprint — Customer 5-Step Order Logistics Tracking View")
    ]

    for img_name, caption in wireframes:
        img_path = os.path.join(DIAGRAM_DIR, img_name)
        add_diagram(doc, img_path, caption, width=Inches(6.0))

def main():
    print("=" * 60)
    print("CraftMarket: Generating Clean Black & White Documentation (.docx)")
    print("=" * 60)

    doc = create_document()

    print("[1/2] Building System Requirements Specification (SRS)...")
    build_srs_section(doc)

    print("[2/2] Building System Design Documents (SDD)...")
    build_sdd_section(doc)

    print(f"Saving finalized Word document to: {OUTPUT_FILE}...")
    try:
        doc.save(OUTPUT_FILE)
        target_saved = OUTPUT_FILE
    except PermissionError:
        fallback = r"c:\CraftMarket\CraftMarket_Documentation_SRS_and_SDD_v2.docx"
        print(f"Warning: {OUTPUT_FILE} is open in Word. Saving to fallback: {fallback}")
        doc.save(fallback)
        target_saved = fallback

    file_size_mb = os.path.getsize(target_saved) / (1024 * 1024)
    print(f"SUCCESS: Document generated. Saved as: {target_saved} (Size: {file_size_mb:.2f} MB)")
    print("=" * 60)

if __name__ == "__main__":
    main()
