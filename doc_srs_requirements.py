"""
CraftMarket Documentation - Part 2: Functional & Non-Functional Requirements (SRS)
"""

import docx
from docx.shared import Inches, Pt, RGBColor
from build_comprehensive_docx import (
    add_styled_heading, add_p, add_bullet, add_callout, add_formatted_table
)

def build_functional_requirements(doc):
    add_styled_heading(doc, "4. Functional Requirements (Subsystems FR-01 to FR-13)", level=2)
    
    add_p(
        doc,
        "The CraftMarket system comprises thirteen (13) distinct functional subsystems. Each requirement is "
        "formally specified below with user stories, technical prerequisites, functional workflows, postconditions, "
        "and exception handling."
    )

    reqs = [
        {
            "id": "FR-01",
            "title": "Authentication, Authorization & Role-Based Access Control (RBAC)",
            "actor": "All Users (Guest, Customer, Seller, Admin)",
            "story": "As a platform user, I want to securely register and log in so that I can access features specific to my designated role.",
            "pre": "User has valid email address and network connectivity.",
            "steps": [
                "User enters email, password, and selects intended account role (customer or seller).",
                "Backend validates password strength (min 8 characters, confirmation match) and uniqueness of email.",
                "System hashes password using Bcrypt with work factor 12.",
                "Upon login, system issues a state-preserving Laravel Sanctum Bearer token with appropriate token abilities.",
                "Frontend stores token in reactive Pinia auth store and attaches Authorization header to all Axios API requests.",
                "Vue Router navigation guards intercept navigation attempts and redirect unauthorized roles to designated dashboards."
            ],
            "post": "Authenticated session established; role-specific dashboard views accessible.",
            "err": "Invalid credentials return 422 Unprocessable Entity or 401 Unauthorized with descriptive error text."
        },
        {
            "id": "FR-02",
            "title": "User Account, Security & Profile Management",
            "actor": "Customer, Seller, Admin",
            "story": "As an account holder, I want to update my profile details, shipping address, and password so that my account remains current and secure.",
            "pre": "User is authenticated with a valid Sanctum Bearer token.",
            "steps": [
                "User navigates to `/profile` view.",
                "System displays current profile information (name, email, phone, default shipping address).",
                "User updates fields and submits update form.",
                "Backend executes FormRequest validation and persists changes via Eloquent ORM.",
                "Password change workflow requires verification of current password before updating to new Bcrypt hash."
            ],
            "post": "Profile data updated in `users` table; session maintains updated identity.",
            "err": "Incorrect current password triggers validation exception; duplicate email checks prevent collisions."
        },
        {
            "id": "FR-03",
            "title": "Artisan Verification & Workshop Storefront Onboarding",
            "actor": "Seller, Administrator",
            "story": "As an artisan, I want to submit my workshop credentials so that the platform administrator can verify my workshop and grant me active seller privileges.",
            "pre": "User registered with 'seller' role; `seller_profiles` record initialized.",
            "steps": [
                "Artisan enters shop name, artisan biography, workshop address, contact phone, and craft specialty.",
                "Artisan uploads workshop proof / craft identification.",
                "Seller profile status is set to `verification_status = 'pending'`.",
                "Administrator inspects pending applications in Admin Dashboard (`/admin#sellers`).",
                "Administrator audits submitted workshop credentials and clicks 'Approve' or 'Reject'.",
                "Upon approval, `verification_status` updates to `'approved'`, and artisan product publishing is unlocked."
            ],
            "post": "Verified artisan storefront published; seller can list handmade products in catalog.",
            "err": "Incomplete submissions remain pending; rejected applications display admin feedback for resubmission."
        },
        {
            "id": "FR-04",
            "title": "Product & Craft Catalog Taxonomy Management",
            "actor": "Artisan / Seller, Administrator",
            "story": "As a seller, I want to create, update, and manage product listings with handmade attributes (materials, origin, stock) so that customers can discover them.",
            "pre": "Seller is authenticated and verified (`verification_status = 'approved'`).",
            "steps": [
                "Seller navigates to Products tab in Seller Dashboard.",
                "Seller clicks 'Add New Craft' and enters title, description, category, price, stock, materials, and origin province.",
                "Seller uploads high-resolution craft images (stored securely in Laravel public storage).",
                "System assigns SEO slug and verifies foreign key category constraint.",
                "Listing appears immediately in the public marketplace catalog.",
                "Administrators hold emergency moderation privileges to delete or deactivate inappropriate listings."
            ],
            "post": "Product record inserted into `products` table with active status.",
            "err": "Validation errors on negative price, non-numeric stock, or unsupported image MIME types."
        },
        {
            "id": "FR-05",
            "title": "Product Discovery, Debounced Search & Faceted Filtering",
            "actor": "Customer, Guest",
            "story": "As a buyer, I want to search and filter products by craft category, price range, region, and keyword with instantaneous responsiveness.",
            "pre": "Products exist in database with `is_active = true` and `stock > 0`.",
            "steps": [
                "Customer visits `/products` catalog view.",
                "Search input triggers a 300ms debounced API query (`/api/products?search=...`) to prevent query thrashing.",
                "Faceted sidebar filters permit multi-select category taxonomies (Ceramics, Textiles, Woodwork, Jewelry).",
                "Price slider constrains query within min and max price boundaries.",
                "Backend applies dynamic Eloquent query scopes and returns paginated JSON payload.",
                "UI renders responsive CSS grid of `ProductCard.vue` components with lazy-loaded images."
            ],
            "post": "Filtered results displayed accurately with matching item counts.",
            "err": "Zero matches render an elegant empty state with recommendations to broaden filter criteria."
        },
        {
            "id": "FR-06",
            "title": "Shopping Cart & Buyer Checkout Engine",
            "actor": "Customer",
            "story": "As a customer, I want to add items to my persistent cart, adjust quantities, review itemized order totals, and proceed to checkout.",
            "pre": "Customer is authenticated; items have available inventory.",
            "steps": [
                "Customer clicks 'Add to Cart' on product details or catalog card.",
                "Pinia cart store validates stock and adds item or increments quantity.",
                "Administrator accounts are strictly blocked from adding items to cart to preserve transactional auditability.",
                "Cart view displays line items, unit prices, subtotal, estimated shipping fee, and grand total.",
                "At checkout (`/checkout`), customer specifies recipient name, contact number, and delivery address.",
                "System performs backend atomic stock lock before issuing payment gateway intent."
            ],
            "post": "Order record created with status `'pending'` and payment status `'unpaid'`.",
            "err": "If requested quantity exceeds available stock, user is warned and quantity is clamped to maximum available."
        },
        {
            "id": "FR-07",
            "title": "Payment Processing & PayMongo Sandbox Gateway Integration",
            "actor": "Customer, Payment Gateway",
            "story": "As a buyer, I want to pay for my craft order via GCash, Maya, or Credit Card so that my order is immediately confirmed.",
            "pre": "Order created with status `'pending'`; PayMongo sandbox API keys configured.",
            "steps": [
                "Checkout directs buyer to PayMongo Sandbox payment selector (`/payment/sandbox/:orderId`).",
                "Customer selects payment method (GCash, Maya, Card) and triggers payment simulation.",
                "System issues payment intent API request to PayMongo sandbox endpoint.",
                "On successful authorization, PayMongo returns success transaction token (`pm_txn_...`).",
                "System writes record to `payments` table with payment method, amount, reference number, and status `'paid'`.",
                "Order status transitions from `'pending'` to `'processing'` and escrow lock is initiated."
            ],
            "post": "Order payment status marked `'paid'`; funds locked in marketplace escrow vault.",
            "err": "Declined or canceled transactions leave order in `'pending'` state with option to re-attempt checkout."
        },
        {
            "id": "FR-08",
            "title": "Escrow Vault & Financial State Machine",
            "actor": "System, Customer, Seller, Administrator",
            "story": "As a marketplace operator, I want payments held in escrow until delivery is verified so that both buyer and artisan are protected from fraud.",
            "pre": "Order has confirmed payment in `payments` table.",
            "steps": [
                "Upon payment confirmation, escrow state is set to `'held'`.",
                "Platform commission is calculated based on seller tier: 8% for Free tier, 3% for Artisan Pro tier.",
                "Artisan net earnings (`order_total - commission_fee`) are computed and logged in escrow ledger.",
                "Funds remain locked while order status traverses `'processing'`, `'shipped'`, and `'in_transit'`.",
                "When customer confirms delivery (or upon 7-day auto-settlement), escrow transitions to `'released_to_seller'`.",
                "Released net earnings are added to the seller's verified withdrawable balance in `seller_profiles`."
            ],
            "post": "Artisan available balance incremented; transaction permanently logged for financial audit.",
            "err": "Disputed orders lock escrow indefinitely until administrative dispute arbitration."
        },
        {
            "id": "FR-09",
            "title": "Order Fulfillment & 5-Step Logistics Tracking Stepper",
            "actor": "Seller, Customer, Courier",
            "story": "As a buyer and seller, I want to track order fulfillment across a 5-stage timeline with printable tax receipts and packing slips.",
            "pre": "Order is confirmed and paid.",
            "steps": [
                "Order appears in Seller Dashboard with status `'processing'`.",
                "Seller packages items and clicks 'Dispatch Order', triggering transition to `'shipped'`.",
                "System generates cryptographic 6-digit verification code and scannable QR payload.",
                "Courier marks parcel `'in_transit'` upon pickup.",
                "Customer verifies delivery by entering the 6-digit dispatch code or clicking 'Confirm Delivery Received'.",
                "Order status transitions to `'delivered'`; customer can generate printable PDF-grade tax invoice receipt."
            ],
            "post": "Order completed; escrow released; buyer unlocked to submit product review.",
            "err": "Invalid dispatch codes prevent delivery confirmation."
        },
        {
            "id": "FR-10",
            "title": "Artisan Payout Request, Validation & Disbursal Audit",
            "actor": "Seller, Administrator",
            "story": "As an artisan, I want to withdraw my cleared escrow earnings to my bank or e-wallet so that I receive my workshop income.",
            "pre": "Seller has cleared escrow balance >= minimum withdrawal threshold (₱500.00).",
            "steps": [
                "Seller navigates to Payouts tab in Seller Dashboard.",
                "System displays cleared available balance, locked escrow balance, and lifetime earnings.",
                "Seller submits payout request specifying amount, payout channel (GCash, Maya, BDO, BPI), account name, and number.",
                "System verifies `requested_amount <= available_balance` and deducts amount from available balance into pending state.",
                "Payout record inserted into `payout_requests` table with status `'pending'`.",
                "Administrator audits payout queue in Admin Dashboard (`/admin#payouts`).",
                "Administrator transfers funds via banking rail, uploads transaction reference, and marks request `'approved'`."
            ],
            "post": "Payout marked `'approved'` or `'completed'`; seller receives payment notification.",
            "err": "Requests exceeding cleared balance are rejected with HTTP 422 validation failure."
        },
        {
            "id": "FR-11",
            "title": "Artisan Pro Subscription & Monetization Model",
            "actor": "Seller, System",
            "story": "As an active seller, I want to upgrade to Artisan Pro to lower my transaction commission from 8% to 3% and gain featured marketplace visibility.",
            "pre": "Seller is authenticated and verified.",
            "steps": [
                "Seller accesses Subscription tab in Seller Dashboard.",
                "System presents plan comparison: Free Tier (8% fee) vs Artisan Pro (3% fee, ₱299/mo or ₱2,990/yr).",
                "Seller selects plan and completes subscription payment simulation via PayMongo.",
                "System creates or updates record in `subscriptions` table with active dates.",
                "`seller_profiles.is_pro` flag is updated to `true`.",
                "All subsequent order commission calculations automatically apply the 3% Pro fee formula."
            ],
            "post": "Artisan Pro benefits active; lower fees applied immediately to subsequent sales.",
            "err": "Expired subscriptions automatically revert `is_pro` to `false` and commission formula to 8%."
        },
        {
            "id": "FR-12",
            "title": "Verified Customer Reviews & Rating Feedback",
            "actor": "Customer, Public",
            "story": "As a buyer who received an order, I want to submit a 1-5 star review and comment so that other shoppers can trust the artisan.",
            "pre": "Customer purchased product and order status is strictly `'delivered'`.",
            "steps": [
                "Customer views order history or product details page.",
                "System verifies purchase authenticity via SQL join (`orders` + `order_items` + `payments`).",
                "If verified, 'Write a Verified Review' button is enabled.",
                "Customer selects rating (1 to 5 stars) and enters review text.",
                "System saves record in `product_reviews` with `is_verified_purchase = true`.",
                "Product average rating and review count recalculate automatically in catalog and product cards."
            ],
            "post": "Review published publicly; artisan workshop rating score updated.",
            "err": "Unverified buyers or users without completed delivery cannot submit reviews."
        },
        {
            "id": "FR-13",
            "title": "Decoupled Headless WordPress CMS Integration & Fallback",
            "actor": "Content Editor, Public, System",
            "story": "As a platform visitor, I want to read official artisan announcements and craft guides seamlessly embedded into the Vue 3 storefront.",
            "pre": "WordPress instance running with REST API enabled (`/wp-json/wp/v2/posts`).",
            "steps": [
                "Storefront homepage (`HomeView.vue`) initiates asynchronous HTTP fetch to WordPress REST API.",
                "System parses JSON response, extracting post titles, excerpts, featured media, and published dates.",
                "Announcements banner renders prominently above hero section.",
                "Educational craft stories render in the dedicated blog section (`BlogSection.vue`).",
                "If WordPress server is offline or unreachable, Vue 3 catches network error and displays curated local fallback content.",
                "Under zero circumstances does CMS failure disrupt product catalog, shopping cart, or checkout pipelines."
            ],
            "post": "Editorial content rendered cleanly with zero coupling to transactional database.",
            "err": "Network timeouts trigger graceful fallback banner with no console errors or page breakdown."
        }
    ]

    for req in reqs:
        add_styled_heading(doc, f"{req['id']}: {req['title']}", level=3)
        add_p(doc, req['story'], bold_prefix="User Story:")
        add_p(doc, req['actor'], bold_prefix="Primary Actor:")
        add_p(doc, req['pre'], bold_prefix="Prerequisites:")
        
        doc.add_paragraph().paragraph_format.space_after = Pt(2)
        add_p(doc, "Step-by-Step Functional Workflow:", bold_prefix="Execution Flow:")
        for idx, step in enumerate(req['steps'], 1):
            add_bullet(doc, step, bold_prefix=f"Step {idx}")
            
        add_p(doc, req['post'], bold_prefix="Post-conditions:")
        add_p(doc, req['err'], bold_prefix="Exception Handling:")
        doc.add_paragraph().paragraph_format.space_after = Pt(4)

    doc.add_page_break()

def build_non_functional_requirements(doc):
    add_styled_heading(doc, "5. Non-Functional Requirements (The 6 Architectural Pillars)", level=2)
    
    add_p(
        doc,
        "The CraftMarket system adheres to stringent quality, reliability, and security standards across six (6) "
        "fundamental non-functional engineering pillars, summarized in the technical matrix below:"
    )

    col_w = [Inches(1.2), Inches(1.5), Inches(2.1), Inches(1.2)]
    headers = ["NFR Identifier", "Quality Attribute", "Specification & Technical Threshold", "Verification Method"]
    nfr_data = [
        [
            "NFR-PERF-01",
            "API Latency & Throughput",
            "All REST API endpoints must respond in <= 300ms under standard 50-user concurrent load. Static assets served via Vite in <= 1.5s.",
            "Postman Runner / Automated Benchmark Benchmarks"
        ],
        [
            "NFR-SEC-01",
            "Authentication & Hashing",
            "Stateless token management using Laravel Sanctum. Passwords encrypted using Bcrypt with cost factor 12. HTTPS enforced.",
            "PHPUnit Feature Tests & Security Code Audits"
        ],
        [
            "NFR-SEC-02",
            "Role & Data Segregation",
            "Strict server-side policy authorization (`Authorize` middleware). Admins barred from cart/orders; sellers restricted to own inventory.",
            "403 Forbidden automated unit and integration tests"
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
            "All financial mutations (orders, escrow, payouts) wrapped in ACID database transactions with rollback on failure.",
            "PHPUnit DatabaseTransactions test trait (29/29 tests)"
        ],
        [
            "NFR-USE-01",
            "Accessibility & Aesthetics",
            "Compliance with WCAG 2.1 Level AA color contrast. Warm artisan palette (forest green, terracotta, warm cream). Fully responsive.",
            "Lighthouse Accessibility Audit & Responsive DevTools"
        ],
        [
            "NFR-MNT-01",
            "Modularity & Clean Code",
            "Clean Service-Repository separation in backend; component-driven Vue 3 architecture with Pinia state management.",
            "Automated PHP CS Fixer & Vue ESLint verification"
        ],
        [
            "NFR-SCL-01",
            "Database Indexing",
            "Foreign keys on all relational tables with cascade constraints. Composite indexes on `products(category_id, price)` and `orders(user_id, status)`.",
            "MySQL EXPLAIN query optimization analysis"
        ]
    ]
    add_formatted_table(doc, col_w, headers, nfr_data, header_bg="1E3A2F")

    doc.add_page_break()

print("Functional & Non-Functional Requirements module loaded.")
