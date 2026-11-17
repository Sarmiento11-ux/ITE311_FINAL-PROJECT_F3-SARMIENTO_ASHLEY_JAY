import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

OUTPUT_DIR = r"c:\CraftMarket\diagram_images"
os.makedirs(OUTPUT_DIR, exist_ok=True)

FONT_FAMILY = 'sans-serif'
C_PRIMARY = '#2F4533'     # Forest Green
C_SECONDARY = '#C25E3E'   # Terracotta
C_ACCENT = '#3B82F6'      # Blue
C_PURPLE = '#8B5CF6'      # Purple
C_GOLD = '#D97706'        # Amber/Gold
C_BG = '#FAF8F5'          # Warm Off-White
C_BOX_BG = '#FFFFFF'      # Pure White
C_BORDER = '#D9CFC4'      # Neutral Border
C_DARK = '#2C221E'        # Charcoal/Brown Text
C_MUTED = '#6B7280'       # Muted text

def save_fig(fig, filename):
    filepath = os.path.join(OUTPUT_DIR, filename)
    fig.savefig(filepath, dpi=300, bbox_inches='tight', facecolor='white')
    plt.close(fig)
    print(f"Saved: {filepath}")

# ==============================================================================
# 1. CONTEXT DIAGRAM (DFD Level 0)
# ==============================================================================
def draw_context_diagram():
    fig, ax = plt.subplots(figsize=(10, 6.5), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 100)
    ax.axis('off')

    # Central System Circle
    center_circle = patches.Circle((50, 50), 16, facecolor=C_PRIMARY, edgecolor='#1E2E21', linewidth=2.5, zorder=3)
    ax.add_patch(center_circle)
    ax.text(50, 52, "CRAFTMARKET\nPLATFORM", ha='center', va='center', color='white', fontsize=12, fontweight='bold', fontfamily=FONT_FAMILY, zorder=4)
    ax.text(50, 44, "(Vue 3 SPA + Laravel 12 API)", ha='center', va='center', color='#C8E6C9', fontsize=8.5, fontfamily=FONT_FAMILY, zorder=4)

    # External Entities
    entities = [
        {"name": "CUSTOMER\n(Shopper)", "pos": (12, 78), "color": C_SECONDARY},
        {"name": "SELLER\n(Artisan Maker)", "pos": (12, 22), "color": C_GOLD},
        {"name": "ADMINISTRATOR\n(Marketplace Admin)", "pos": (50, 90), "color": C_PURPLE},
        {"name": "HEADLESS CMS\n(WordPress REST API)", "pos": (88, 78), "color": '#0D9488'},
        {"name": "PAYMENT GATEWAY\n(PayMongo API)", "pos": (88, 22), "color": C_ACCENT},
    ]

    for ent in entities:
        x, y = ent["pos"]
        w, h = 20, 11
        box = patches.FancyBboxPatch((x - w/2, y - h/2), w, h, boxstyle="round,pad=1.2,rounding_size=2",
                                     facecolor=C_BOX_BG, edgecolor=ent["color"], linewidth=2, zorder=3)
        ax.add_patch(box)
        # Header banner inside box
        header = patches.FancyBboxPatch((x - w/2, y + h/2 - 3.5), w, 3.5, boxstyle="round,pad=0.2,rounding_size=1",
                                       facecolor=ent["color"], edgecolor=ent["color"], zorder=3)
        ax.add_patch(header)
        ax.text(x, y + 2.2, ent["name"].split("\n")[0], ha='center', va='center', color='white', fontsize=8.5, fontweight='bold', zorder=4)
        ax.text(x, y - 2, ent["name"].split("\n")[1], ha='center', va='center', color=C_DARK, fontsize=7.5, zorder=4)

    # Arrows & Data Flows
    # Customer <-> System
    ax.annotate("", xy=(36, 56), xytext=(22, 73), arrowprops=dict(arrowstyle="->", color=C_SECONDARY, lw=1.5, ls='-'))
    ax.text(23, 67, "1. Search, Cart, Checkout, Inquiries", color=C_DARK, fontsize=7.5, fontweight='bold', rotation=32)
    ax.annotate("", xy=(22, 70), xytext=(35, 52), arrowprops=dict(arrowstyle="->", color=C_PRIMARY, lw=1.5, ls='--'))
    ax.text(32, 59, "2. Catalog, Order Status, Receipts", color=C_PRIMARY, fontsize=7, rotation=32)

    # Seller <-> System
    ax.annotate("", xy=(36, 44), xytext=(22, 27), arrowprops=dict(arrowstyle="->", color=C_GOLD, lw=1.5))
    ax.text(23, 31, "3. Craft Listings, Dispatch, Payouts", color=C_DARK, fontsize=7.5, fontweight='bold', rotation=-32)
    ax.annotate("", xy=(22, 24), xytext=(36, 40), arrowprops=dict(arrowstyle="->", color=C_PRIMARY, lw=1.5, ls='--'))
    ax.text(32, 38, "4. Sales Ledgers, Escrow Releases", color=C_PRIMARY, fontsize=7, rotation=-32)

    # Admin <-> System
    ax.annotate("", xy=(48, 66), xytext=(48, 83), arrowprops=dict(arrowstyle="->", color=C_PURPLE, lw=1.5))
    ax.text(35, 75, "Storefront Approvals,\nUser Moderation", color=C_DARK, fontsize=7, ha='right')
    ax.annotate("", xy=(52, 83), xytext=(52, 66), arrowprops=dict(arrowstyle="->", color=C_PRIMARY, lw=1.5, ls='--'))
    ax.text(53, 75, "Financial Audit\n& Platform GMV", color=C_PRIMARY, fontsize=7, ha='left')

    # Headless WP <-> System
    ax.annotate("", xy=(64, 56), xytext=(78, 73), arrowprops=dict(arrowstyle="->", color='#0D9488', lw=1.5))
    ax.text(67, 67, "Announcements, Heritage Stories", color=C_DARK, fontsize=7, fontweight='bold', rotation=-32)
    ax.annotate("", xy=(78, 70), xytext=(65, 52), arrowprops=dict(arrowstyle="->", color=C_PRIMARY, lw=1.5, ls='--'))
    ax.text(67, 57, "Synced Products & Reports (CMS Bridge)", color=C_PRIMARY, fontsize=6.8, rotation=-32)

    # PayMongo <-> System
    ax.annotate("", xy=(78, 27), xytext=(64, 44), arrowprops=dict(arrowstyle="->", color=C_ACCENT, lw=1.5))
    ax.text(68, 38, "Create Checkout Session", color=C_DARK, fontsize=7, rotation=32)
    ax.annotate("", xy=(64, 40), xytext=(78, 24), arrowprops=dict(arrowstyle="->", color=C_PRIMARY, lw=1.5, ls='--'))
    ax.text(68, 29, "Payment Webhook Confirmation", color=C_PRIMARY, fontsize=7, fontweight='bold', rotation=32)

    # Diagram Title
    ax.text(50, 98, "FIGURE 1: CRAFTMARKET SYSTEM CONTEXT DIAGRAM (DFD LEVEL 0)", ha='center', va='center', fontsize=11, fontweight='bold', color=C_PRIMARY)

    save_fig(fig, "1_context_diagram.png")

# ==============================================================================
# 2. SYSTEM ARCHITECTURE DIAGRAM (Decoupled Multi-Tier)
# ==============================================================================
def draw_architecture_diagram():
    fig, ax = plt.subplots(figsize=(11, 7.5), dpi=300)
    ax.set_xlim(0, 110)
    ax.set_ylim(0, 100)
    ax.axis('off')

    # Layer 1: Presentation Layer
    l1_box = patches.FancyBboxPatch((5, 75), 100, 20, boxstyle="round,pad=1,rounding_size=2",
                                    facecolor='#F0FDF4', edgecolor='#86EFAC', linewidth=2)
    ax.add_patch(l1_box)
    ax.text(8, 92, "1. PRESENTATION LAYER (Client Single Page Application — Vue.js 3 + Vite 8.3)", fontsize=9.5, fontweight='bold', color='#166534')

    comps_l1 = [
        ("Views & Components", "HomeView, ProductDetailsView\nSellerDashboardView, AdminDashboard\nOrderTrackingStepper, InvoiceModal"),
        ("State Management", "Pinia Reactive Stores\nCart, Wishlist, User Session\nComposables (useAuth)"),
        ("Client Security & Services", "DOMPurify (XSS Sanitizer)\nproductService, orderService\ncmsService, adminService"),
        ("Routing & Navigation", "Vue Router 4\nRole-Based Navigation Guards\nStateless JWT Bearer Header")
    ]
    for idx, (title, desc) in enumerate(comps_l1):
        x = 8 + idx * 24.5
        box = patches.FancyBboxPatch((x, 77), 23, 13, boxstyle="round,pad=0.5,rounding_size=1.5", facecolor='white', edgecolor='#BBF7D0', lw=1.2)
        ax.add_patch(box)
        ax.text(x + 11.5, 87, title, ha='center', va='center', fontsize=8, fontweight='bold', color='#14532D')
        ax.text(x + 11.5, 82, desc, ha='center', va='center', fontsize=6.5, color=C_DARK)

    # Connectivity Arrow L1 -> L2 / L3
    ax.annotate("", xy=(38, 66), xytext=(38, 75), arrowprops=dict(arrowstyle="<->", color=C_PRIMARY, lw=2))
    ax.text(39, 70.5, "HTTPS / REST API (Bearer Token)", fontsize=7.5, fontweight='bold', color=C_PRIMARY)

    ax.annotate("", xy=(85, 66), xytext=(85, 75), arrowprops=dict(arrowstyle="<->", color='#0D9488', lw=2))
    ax.text(86, 70.5, "Direct Content Fetch (/wp-json/wp/v2)", fontsize=7.5, fontweight='bold', color='#0D9488')

    # Layer 2: Application API Engine (Laravel 12)
    l2_box = patches.FancyBboxPatch((5, 36), 65, 30, boxstyle="round,pad=1,rounding_size=2",
                                    facecolor='#FFF7ED', edgecolor='#FDBA74', linewidth=2)
    ax.add_patch(l2_box)
    ax.text(8, 63, "2. APPLICATION REST API ENGINE (Laravel 12 + PHP 8.2)", fontsize=9.5, fontweight='bold', color='#9A3412')

    comps_l2 = [
        ("Sanctum Auth & Middleware", "role:admin,seller,customer\nVerifyCmsBridgeKey (Shared Secret)\nRateLimiter (API Throttling)"),
        ("Resource Controllers", "ProductController (Catalogs & Pro Toggle)\nOrderController (Escrow Lifecycle)\nSellerController, AdminController\nPaymentController (Sandbox Engine)"),
        ("Domain Services & Logistics", "EscrowEngine (Commission 8% vs 3%)\nNotificationService (Mail Alerts)\nQRDispatchService (Courier Codes)")
    ]
    for idx, (title, desc) in enumerate(comps_l2):
        x = 8 + idx * 20.5
        box = patches.FancyBboxPatch((x, 39), 19.5, 21, boxstyle="round,pad=0.5,rounding_size=1.5", facecolor='white', edgecolor='#FED7AA', lw=1.2)
        ax.add_patch(box)
        ax.text(x + 9.75, 56.5, title, ha='center', va='center', fontsize=7.5, fontweight='bold', color='#7C2D12')
        ax.text(x + 9.75, 46.5, desc, ha='center', va='center', fontsize=6.5, color=C_DARK)

    # Layer 3: Headless Content Management (WordPress)
    l3_box = patches.FancyBboxPatch((74, 36), 31, 30, boxstyle="round,pad=1,rounding_size=2",
                                    facecolor='#F0FDFA', edgecolor='#99F6E4', linewidth=2)
    ax.add_patch(l3_box)
    ax.text(76, 63, "3. HEADLESS CMS TIER (WordPress)", fontsize=9, fontweight='bold', color='#115E59')
    wp_inner = patches.FancyBboxPatch((76, 39), 27, 21, boxstyle="round,pad=0.5,rounding_size=1.5", facecolor='white', edgecolor='#5EEAD4', lw=1.2)
    ax.add_patch(wp_inner)
    ax.text(89.5, 56.5, "WordPress 6.x CMS Engine", ha='center', va='center', fontsize=8, fontweight='bold', color='#134E4A')
    ax.text(89.5, 47, "• /wp-admin Editorial Dashboard\n• Gutenberg Rich Text & Media\n• Announcements (/wp/v2/posts)\n• CraftMarket CMS Bridge Plugin\n• Fallback Offline Cache", ha='center', va='center', fontsize=6.8, color=C_DARK)

    # Laravel <-> WP Bridge Arrow
    ax.annotate("", xy=(74, 49), xytext=(70, 49), arrowprops=dict(arrowstyle="<->", color='#0D9488', lw=1.5, ls='--'))
    ax.text(72, 51, "X-CMS-Key Bridge", ha='center', fontsize=6.5, color='#0D9488', fontweight='bold')

    # Layer 4: Persistence Layer (MySQL 8) & Layer 5: External Services
    l4_box = patches.FancyBboxPatch((5, 5), 58, 23, boxstyle="round,pad=1,rounding_size=2",
                                    facecolor='#EFF6FF', edgecolor='#93C5FD', linewidth=2)
    ax.add_patch(l4_box)
    ax.text(8, 25, "4. PERSISTENCE LAYER (MySQL 8.x Relational Database)", fontsize=9, fontweight='bold', color='#1E40AF')
    ax.text(37, 14, "InnoDB Storage Engine • ACID Transactions • UTF-8mb4 Collation\n11 Application Tables: users, seller_profiles, categories, products, orders,\norder_items, payments, payout_requests, artisan_subscriptions, reviews, wishlists", ha='center', va='center', fontsize=7.2, color=C_DARK)

    l5_box = patches.FancyBboxPatch((67, 5), 38, 23, boxstyle="round,pad=1,rounding_size=2",
                                    facecolor='#FAF5FF', edgecolor='#D8B4FE', linewidth=2)
    ax.add_patch(l5_box)
    ax.text(70, 25, "5. EXTERNAL GATEWAYS & HARDWARE", fontsize=9, fontweight='bold', color='#6B21A8')
    ax.text(86, 14, "• PayMongo Sandbox API (GCash, Maya, Cards)\n• Webhook Listener (/api/webhooks/paymongo)\n• Logistics QR Scanner & Courier Code Bridge\n• Client-Side Window.Print Hardware Spooler", ha='center', va='center', fontsize=7.2, color=C_DARK)

    # Arrow L2 -> L4
    ax.annotate("", xy=(34, 28), xytext=(34, 36), arrowprops=dict(arrowstyle="<->", color='#1E40AF', lw=1.8))
    ax.text(35, 32, "Eloquent ORM / PDO (Parameterized)", fontsize=7, color='#1E40AF')

    # Arrow L2 -> L5
    ax.annotate("", xy=(72, 28), xytext=(60, 36), arrowprops=dict(arrowstyle="<->", color='#6B21A8', lw=1.8))
    ax.text(67, 33, "Webhooks & APIs", fontsize=7, color='#6B21A8')

    ax.text(55, 98, "FIGURE 2: CRAFTMARKET DECOUPLED MULTI-TIER SYSTEM ARCHITECTURE", ha='center', va='center', fontsize=11, fontweight='bold', color=C_PRIMARY)

    save_fig(fig, "2_architecture_diagram.png")

# ==============================================================================
# 3. USE CASE DIAGRAM
# ==============================================================================
def draw_usecase_diagram():
    fig, ax = plt.subplots(figsize=(11, 7.5), dpi=300)
    ax.set_xlim(0, 110)
    ax.set_ylim(0, 100)
    ax.axis('off')

    # System Boundary Box
    sys_box = patches.FancyBboxPatch((28, 5), 78, 88, boxstyle="round,pad=1,rounding_size=2",
                                     facecolor='#FAF8F5', edgecolor=C_PRIMARY, linewidth=2)
    ax.add_patch(sys_box)
    ax.text(67, 90, "CRAFTMARKET SYSTEM BOUNDARY", ha='center', va='center', fontsize=11, fontweight='bold', color=C_PRIMARY)

    # Actors
    actors = [
        {"name": "CUSTOMER\n(Shopper)", "pos": (12, 75), "color": C_SECONDARY},
        {"name": "SELLER\n(Artisan Maker)", "pos": (12, 45), "color": C_GOLD},
        {"name": "ADMINISTRATOR", "pos": (12, 18), "color": C_PURPLE}
    ]

    for act in actors:
        x, y = act["pos"]
        # Stick figure head
        head = patches.Circle((x, y + 4), 2.2, facecolor='white', edgecolor=act["color"], lw=2)
        ax.add_patch(head)
        # Body
        ax.plot([x, x], [y + 1.8, y - 2], color=act["color"], lw=2)
        # Arms
        ax.plot([x - 3, x + 3], [y + 0.5, y + 0.5], color=act["color"], lw=2)
        # Legs
        ax.plot([x, x - 2.5], [y - 2, y - 5.5], color=act["color"], lw=2)
        ax.plot([x, x + 2.5], [y - 2, y - 5.5], color=act["color"], lw=2)
        # Label
        ax.text(x, y - 8, act["name"], ha='center', va='top', fontsize=8, fontweight='bold', color=act["color"])

    # Use Cases (Ovals)
    usecases = [
        # Customer
        {"id": "UC1", "text": "Browse & Filter Catalog", "pos": (45, 82), "actors": [0]},
        {"id": "UC2", "text": "Cart & PayMongo Checkout", "pos": (70, 82), "actors": [0]},
        {"id": "UC3", "text": "Request Custom Craft", "pos": (92, 82), "actors": [0]},
        {"id": "UC4", "text": "5-Step Delivery Stepper", "pos": (45, 68), "actors": [0]},
        {"id": "UC5", "text": "Print Official Receipt", "pos": (70, 68), "actors": [0, 1]},
        {"id": "UC6", "text": "Submit Verified Review", "pos": (92, 68), "actors": [0]},
        
        # Seller
        {"id": "UC7", "text": "Manage Craft Listings & Stock", "pos": (45, 52), "actors": [1]},
        {"id": "UC8", "text": "Toggle ⭐ Pro Featured Craft", "pos": (70, 52), "actors": [1]},
        {"id": "UC9", "text": "Generate QR Dispatch Code", "pos": (92, 52), "actors": [1]},
        {"id": "UC10", "text": "Request Escrow Payout", "pos": (45, 38), "actors": [1]},
        {"id": "UC11", "text": "Export 13-Col Sales CSV", "pos": (70, 38), "actors": [1]},

        # Admin
        {"id": "UC12", "text": "Approve Artisan Storefronts", "pos": (45, 22), "actors": [2]},
        {"id": "UC13", "text": "Manage User Accounts & Roles", "pos": (70, 22), "actors": [2]},
        {"id": "UC14", "text": "Oversee WordPress CMS Hub", "pos": (92, 22), "actors": [2]},
        {"id": "UC15", "text": "Audit Platform Escrow Pool", "pos": (45, 11), "actors": [2]},
        {"id": "UC16", "text": "Export Financial Audit CSV", "pos": (70, 11), "actors": [2]}
    ]

    for uc in usecases:
        x, y = uc["pos"]
        ellipse = patches.Ellipse((x, y), 20, 7.5, facecolor='white', edgecolor='#4B5563', lw=1.4, zorder=3)
        ax.add_patch(ellipse)
        ax.text(x, y, uc["text"], ha='center', va='center', fontsize=7, color=C_DARK, fontweight='bold', zorder=4)

        # Draw lines to actors
        for act_idx in uc["actors"]:
            act_x, act_y = actors[act_idx]["pos"]
            ax.plot([act_x + 5, x - 10], [act_y, y], color=actors[act_idx]["color"], lw=1.2, ls='-', alpha=0.7, zorder=2)

    ax.text(55, 98, "FIGURE 3: CRAFTMARKET ROLE-BASED USE CASE DIAGRAM", ha='center', va='center', fontsize=11, fontweight='bold', color=C_PRIMARY)

    save_fig(fig, "3_usecase_diagram.png")

# ==============================================================================
# 4. ENTITY-RELATIONSHIP (ER) DIAGRAM
# ==============================================================================
def draw_er_diagram():
    fig, ax = plt.subplots(figsize=(11, 8.5), dpi=300)
    ax.set_xlim(0, 110)
    ax.set_ylim(0, 100)
    ax.axis('off')

    tables = [
        {"name": "USERS", "pos": (10, 68), "color": C_PRIMARY,
         "cols": ["id (PK) : BIGINT", "name : VARCHAR(120)", "email (UK) : VARCHAR(150)", "password : VARCHAR(255)", "role : ENUM('admin','seller','cust')", "subscription_plan : VARCHAR(50)", "commission_rate : DECIMAL(5,2)", "is_active : BOOLEAN"]},
        
        {"name": "SELLER_PROFILES", "pos": (44, 68), "color": C_GOLD,
         "cols": ["id (PK) : BIGINT", "user_id (FK) : BIGINT", "shop_name : VARCHAR(150)", "bio : TEXT", "location : VARCHAR(100)", "verification_status : VARCHAR(30)", "badge_details : TEXT"]},

        {"name": "CATEGORIES", "pos": (78, 68), "color": '#0D9488',
         "cols": ["id (PK) : BIGINT", "name : VARCHAR(100)", "slug (UK) : VARCHAR(120)", "description : TEXT", "created_at : TIMESTAMP"]},

        {"name": "PRODUCTS", "pos": (78, 30), "color": C_SECONDARY,
         "cols": ["id (PK) : BIGINT", "seller_id (FK) : BIGINT", "category_id (FK) : BIGINT", "name : VARCHAR(200)", "price : DECIMAL(10,2)", "stock : INT", "is_featured : BOOLEAN (Pro)", "status : VARCHAR(50)"]},

        {"name": "ORDERS", "pos": (10, 30), "color": C_PURPLE,
         "cols": ["id (PK) : BIGINT", "customer_id (FK) : BIGINT", "order_number (UK) : VARCHAR(40)", "total_amount : DECIMAL(10,2)", "payment_status : VARCHAR(50)", "escrow_status : VARCHAR(50)", "shipping_address : TEXT"]},

        {"name": "ORDER_ITEMS", "pos": (44, 30), "color": C_ACCENT,
         "cols": ["id (PK) : BIGINT", "order_id (FK) : BIGINT", "product_id (FK) : BIGINT", "seller_id (FK) : BIGINT", "quantity : INT", "unit_price : DECIMAL(10,2)", "platform_commission : DECIMAL", "seller_payout : DECIMAL", "dispatch_code : VARCHAR(10)"]},

        {"name": "PAYMENTS", "pos": (10, 2), "color": '#10B981',
         "cols": ["id (PK) : BIGINT", "order_id (FK) : BIGINT", "payment_method : VARCHAR(50)", "amount : DECIMAL(10,2)", "status : VARCHAR(50)", "checkout_session_id : VARCHAR(100)"]},

        {"name": "PAYOUT_REQUESTS", "pos": (44, 2), "color": '#F59E0B',
         "cols": ["id (PK) : BIGINT", "user_id (FK) : BIGINT", "amount : DECIMAL(10,2)", "payout_channel : VARCHAR(50)", "status : VARCHAR(50)", "reference_number : VARCHAR(100)"]},

        {"name": "PRODUCT_REVIEWS", "pos": (78, 2), "color": '#EC4899',
         "cols": ["id (PK) : BIGINT", "product_id (FK) : BIGINT", "user_id (FK) : BIGINT", "rating : INT (1-5)", "review_text : TEXT", "created_at : TIMESTAMP"]}
    ]

    for tbl in tables:
        x, y = tbl["pos"]
        w, h = 25, 23
        # Box
        box = patches.FancyBboxPatch((x, y), w, h, boxstyle="round,pad=0.3,rounding_size=1", facecolor='white', edgecolor=tbl["color"], lw=1.8, zorder=3)
        ax.add_patch(box)
        # Header
        hdr = patches.FancyBboxPatch((x, y + h - 3.5), w, 3.5, boxstyle="round,pad=0.1,rounding_size=0.5", facecolor=tbl["color"], edgecolor=tbl["color"], zorder=3)
        ax.add_patch(hdr)
        ax.text(x + w/2, y + h - 1.8, tbl["name"], ha='center', va='center', color='white', fontsize=8.5, fontweight='bold', zorder=4)

        # Columns
        for c_idx, col in enumerate(tbl["cols"]):
            col_y = y + h - 5.5 - (c_idx * 2.1)
            is_pk = "PK" in col
            is_fk = "FK" in col
            txt_color = '#B91C1C' if is_pk else ('#1D4ED8' if is_fk else C_DARK)
            weight = 'bold' if (is_pk or is_fk) else 'normal'
            ax.text(x + 1.2, col_y, col, ha='left', va='center', fontsize=6.2, color=txt_color, fontweight=weight, zorder=4)

    # Relationships (Connectors)
    # USERS -> SELLER_PROFILES (1:1)
    ax.annotate("", xy=(44, 79), xytext=(35, 79), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.5))
    ax.text(39.5, 80.5, "1:1", ha='center', fontsize=7.5, fontweight='bold', color='#374151')

    # USERS -> ORDERS (1:N)
    ax.annotate("", xy=(22, 53), xytext=(22, 68), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.5))
    ax.text(23.5, 60.5, "1:N", ha='left', fontsize=7.5, fontweight='bold', color='#374151')

    # ORDERS -> ORDER_ITEMS (1:N)
    ax.annotate("", xy=(44, 41), xytext=(35, 41), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.5))
    ax.text(39.5, 42.5, "1:N", ha='center', fontsize=7.5, fontweight='bold', color='#374151')

    # PRODUCTS -> ORDER_ITEMS (1:N)
    ax.annotate("", xy=(69, 41), xytext=(78, 41), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.5))
    ax.text(73.5, 42.5, "1:N", ha='center', fontsize=7.5, fontweight='bold', color='#374151')

    # CATEGORIES -> PRODUCTS (1:N)
    ax.annotate("", xy=(90, 53), xytext=(90, 68), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.5))
    ax.text(91.5, 60.5, "1:N", ha='left', fontsize=7.5, fontweight='bold', color='#374151')

    # ORDERS -> PAYMENTS (1:1)
    ax.annotate("", xy=(22, 25), xytext=(22, 30), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.5))
    ax.text(23.5, 27.5, "1:1", ha='left', fontsize=7.5, fontweight='bold', color='#374151')

    # USERS -> PAYOUT_REQUESTS (1:N)
    ax.annotate("", xy=(56, 25), xytext=(30, 68), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.2, ls=':'))
    ax.text(45, 27.5, "1:N", ha='center', fontsize=7.5, color='#374151')

    # PRODUCTS -> PRODUCT_REVIEWS (1:N)
    ax.annotate("", xy=(90, 25), xytext=(90, 30), arrowprops=dict(arrowstyle="->", color='#374151', lw=1.5))
    ax.text(91.5, 27.5, "1:N", ha='left', fontsize=7.5, fontweight='bold', color='#374151')

    ax.text(55, 98, "FIGURE 4: CRAFTMARKET RELATIONAL DATABASE ENTITY-RELATIONSHIP SCHEMA", ha='center', va='center', fontsize=11, fontweight='bold', color=C_PRIMARY)

    save_fig(fig, "4_er_diagram.png")

# ==============================================================================
# 5. WIREFRAME: HOMEPAGE (Visual UI Mockup)
# ==============================================================================
def draw_wireframe_homepage():
    fig, ax = plt.subplots(figsize=(10, 6.5), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 100)
    ax.axis('off')

    # Browser Frame
    browser_frame = patches.FancyBboxPatch((2, 2), 96, 94, boxstyle="round,pad=0.5,rounding_size=1", facecolor='white', edgecolor='#D1D5DB', lw=1.5)
    ax.add_patch(browser_frame)
    # Browser Bar
    browser_bar = patches.FancyBboxPatch((2, 91), 96, 5, boxstyle="round,pad=0.2,rounding_size=0.5", facecolor='#F3F4F6', edgecolor='#D1D5DB', lw=1)
    ax.add_patch(browser_bar)
    for i, c in enumerate(['#EF4444', '#F59E0B', '#10B981']):
        ax.add_patch(patches.Circle((5 + i*2.5, 93.5), 0.9, facecolor=c, edgecolor=None))
    ax.add_patch(patches.Rectangle((15, 92), 70, 3, facecolor='white', edgecolor='#E5E7EB'))
    ax.text(18, 93.5, "https://craftmarket.ph (Local Philippine Handmade Goods)", va='center', fontsize=7, color='#6B7280')

    # Announcement Bar (Top)
    ann_bar = patches.Rectangle((3, 86), 94, 4.5, facecolor=C_PRIMARY)
    ax.add_patch(ann_bar)
    ann_pill = patches.FancyBboxPatch((5, 87), 13, 2.5, boxstyle="round,pad=0.2", facecolor=C_SECONDARY, edgecolor=None)
    ax.add_patch(ann_pill)
    ax.text(11.5, 88.25, "CMS NOTICE", ha='center', va='center', color='white', fontsize=6.5, fontweight='bold')
    ax.text(20, 88.25, "Free nationwide shipping on orders over ₱1,500 • Support 120+ Verified Local Makers", va='center', color='white', fontsize=7)
    ax.text(93, 88.25, "Sell With Us ↗", ha='right', va='center', color='#C8E6C9', fontsize=7, fontweight='bold')

    # Navbar
    nav = patches.Rectangle((3, 79), 94, 7, facecolor='white', edgecolor='#E5E7EB')
    ax.add_patch(nav)
    ax.text(6, 82.5, "🌿 CraftMarket", va='center', fontsize=12, fontweight='bold', color=C_PRIMARY)
    search = patches.FancyBboxPatch((32, 80.5), 38, 4, boxstyle="round,pad=0.2", facecolor='#FAF8F5', edgecolor='#D9CFC4')
    ax.add_patch(search)
    ax.text(35, 82.5, "🔍 Search handcrafted pottery, textiles, woodwork...", va='center', fontsize=6.8, color='#7A6F68')
    ax.text(78, 82.5, "Explore  •  Artisans  •  Wishlist", va='center', fontsize=7.5, color=C_DARK)
    cart_btn = patches.FancyBboxPatch((90, 80.5), 5.5, 4, boxstyle="round,pad=0.2", facecolor=C_PRIMARY, edgecolor=None)
    ax.add_patch(cart_btn)
    ax.text(92.75, 82.5, "🛒 2", ha='center', va='center', color='white', fontsize=7.5, fontweight='bold')

    # Hero Banner
    hero = patches.Rectangle((5, 48), 90, 29, facecolor='#FAF8F5', edgecolor='#E8DFD5')
    ax.add_patch(hero)
    ax.text(8, 71, "HERITAGE MEETS CONTEMPORARY LIVING", fontsize=7.5, fontweight='bold', color=C_SECONDARY)
    ax.text(8, 64, "Handcrafted Traditions\nof the Philippines", fontsize=15, fontweight='bold', color=C_PRIMARY)
    ax.text(8, 55, "Empowering grassroots artisans, weavers, and woodcarvers nationwide\nwith fair compensation, transparent escrow, and national reach.", fontsize=7.2, color='#6B7280')
    btn_shop = patches.FancyBboxPatch((8, 50), 16, 3.8, boxstyle="round,pad=0.2", facecolor=C_PRIMARY, edgecolor=None)
    ax.add_patch(btn_shop)
    ax.text(16, 51.9, "Browse Catalog ↗", ha='center', va='center', color='white', fontsize=7, fontweight='bold')
    
    # Hero Image Box (Placeholder graphic)
    img_box = patches.Rectangle((62, 50), 31, 24, facecolor='#E5E7EB', edgecolor='#D1D5DB')
    ax.add_patch(img_box)
    ax.text(77.5, 62, "🎨 Verified Artisan\nWorkshop Photo", ha='center', va='center', fontsize=9, color='#4B5563', fontweight='bold')

    # Featured Products Section
    ax.text(5, 43, "⭐ FEATURED CRAFTS (Artisan Pro Spotlight)", fontsize=9.5, fontweight='bold', color=C_PRIMARY)
    crafts = [
        ("Inabel Handwoven Throw", "Ilocos Region", "₱1,250.00"),
        ("Terracotta Clay Vases", "Pampanga", "₱850.00"),
        ("Carved Narra Storage Tray", "Paete, Laguna", "₱1,400.00"),
        ("Brass Kulintang Gong", "Mindanao", "₱3,500.00")
    ]
    for idx, (title, loc, price) in enumerate(crafts):
        x = 5 + idx * 22.8
        c_box = patches.FancyBboxPatch((x, 6), 21.5, 34, boxstyle="round,pad=0.3", facecolor='white', edgecolor='#E5E7EB', lw=1.2)
        ax.add_patch(c_box)
        c_img = patches.Rectangle((x+0.5, 20), 20.5, 19, facecolor='#F3F4F6', edgecolor='#E5E7EB')
        ax.add_patch(c_img)
        ax.text(x + 10.75, 29.5, "[ Craft Photo ]", ha='center', va='center', fontsize=7.5, color='#9CA3AF')
        pill = patches.FancyBboxPatch((x + 1.5, 34.5), 8, 3, boxstyle="round,pad=0.2", facecolor='#FEF3C7', edgecolor='#FDE68A')
        ax.add_patch(pill)
        ax.text(x + 5.5, 36, "⭐ Featured", ha='center', va='center', fontsize=6, fontweight='bold', color='#D97706')

        ax.text(x + 1.5, 17, title, fontsize=7.5, fontweight='bold', color=C_DARK)
        ax.text(x + 1.5, 13.5, f"📍 {loc}", fontsize=6.8, color='#6B7280')
        ax.text(x + 1.5, 9.5, price, fontsize=8.5, fontweight='bold', color=C_PRIMARY)

    save_fig(fig, "5_wireframe_homepage.png")

# ==============================================================================
# 6. WIREFRAME: PRODUCT DETAILS & CUSTOM INQUIRY MODAL
# ==============================================================================
def draw_wireframe_product_inquiry():
    fig, ax = plt.subplots(figsize=(10, 6.5), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 100)
    ax.axis('off')

    # Base Product Details Page (Background)
    bg = patches.Rectangle((2, 2), 96, 96, facecolor='#FAF8F5', edgecolor='#D9CFC4', lw=1.5)
    ax.add_patch(bg)

    # Product Gallery
    p_img = patches.Rectangle((6, 45), 36, 46, facecolor='white', edgecolor='#E5E7EB', lw=1.5)
    ax.add_patch(p_img)
    ax.text(24, 68, "High-Resolution\nHandcrafted\nItem Showcase", ha='center', va='center', fontsize=11, color='#9CA3AF', fontweight='bold')

    # Product Info
    ax.text(46, 88, "Carved Narra Heritage Storage Chest", fontsize=14, fontweight='bold', color=C_PRIMARY)
    ax.text(46, 83, "⭐⭐⭐⭐⭐ (14 verified reviews) •paete-woodcraft", fontsize=7.5, color=C_SECONDARY)
    ax.text(46, 76, "₱4,500.00", fontsize=16, fontweight='bold', color=C_PRIMARY)
    ax.text(62, 76.5, "(In Stock: 5 available)", fontsize=7.5, color='#15803D')
    ax.text(46, 68, "Maker: Mang Ben's Workshop • Paete, Laguna [✓ Verified Artisan]", fontsize=8, color=C_DARK, fontweight='bold')
    ax.text(46, 61, "Authentic solid Narra timber box crafted using centuries-old Filipino relief-carving\ntraditions with brass latch hardware and natural beeswax finish.", fontsize=7.2, color='#4B5563')

    # Add to Cart & Wishlist Buttons
    btn_cart = patches.FancyBboxPatch((46, 48), 24, 5.5, boxstyle="round,pad=0.2", facecolor=C_PRIMARY, edgecolor=None)
    ax.add_patch(btn_cart)
    ax.text(58, 50.7, "🛒 Add to Cart (1)", ha='center', va='center', color='white', fontsize=8, fontweight='bold')

    # Dimming Overlay (for Modal effect)
    dim_overlay = patches.Rectangle((2, 2), 96, 96, facecolor='black', alpha=0.45, zorder=5)
    ax.add_patch(dim_overlay)

    # Custom Craft Inquiry Modal Box
    m_box = patches.FancyBboxPatch((20, 12), 60, 76, boxstyle="round,pad=1,rounding_size=2", facecolor='white', edgecolor=C_PRIMARY, lw=2, zorder=6)
    ax.add_patch(m_box)

    # Modal Header
    ax.text(23, 83, "🎨 BESPOKE CRAFT INQUIRY", fontsize=7.5, fontweight='bold', color=C_SECONDARY, zorder=7)
    ax.text(23, 78, "Request Custom Made-to-Order Craft", fontsize=12, fontweight='bold', color=C_PRIMARY, zorder=7)
    ax.text(23, 74, "Direct negotiation with Mang Ben's Workshop for custom dimensions & engravings.", fontsize=7, color='#6B7280', zorder=7)
    ax.text(77, 81, "✕", fontsize=12, color='#9CA3AF', fontweight='bold', zorder=7)

    # Form Fields inside modal
    fields = [
        ("Customer Full Name *", "Juan Dela Cruz", (23, 64), 25),
        ("Email Address *", "juan@example.ph", (51, 64), 25),
        ("Phone / Mobile (Optional)", "0917-123-4567", (23, 53), 25),
        ("Preferred Contact Method", "Email / Viber / SMS", (51, 53), 25),
        ("Desired Quantity *", "2 units", (23, 42), 15),
        ("Preferred Dimensions / Specs", "30 x 18 x 15 inches, Walnut Stain", (41, 42), 35),
    ]
    for lbl, val, (fx, fy), fw in fields:
        ax.text(fx, fy + 4.5, lbl, fontsize=6.8, fontweight='bold', color=C_DARK, zorder=7)
        f_box = patches.FancyBboxPatch((fx, fy), fw, 3.8, boxstyle="round,pad=0.2", facecolor='#FAF8F5', edgecolor='#D9CFC4', zorder=7)
        ax.add_patch(f_box)
        ax.text(fx + 1.5, fy + 1.9, val, va='center', fontsize=7, color='#4B5563', zorder=7)

    # Textarea (Custom Specs)
    ax.text(23, 37.5, "Customization Specifications & Detailed Notes *", fontsize=6.8, fontweight='bold', color=C_DARK, zorder=7)
    ta_box = patches.FancyBboxPatch((23, 23), 53, 12, boxstyle="round,pad=0.2", facecolor='#FAF8F5', edgecolor='#D9CFC4', zorder=7)
    ax.add_patch(ta_box)
    ax.text(24.5, 31, "We would like personalized floral relief engravings on the top lid featuring\nour family initials 'D&C 2026' with antique brass chest corner braces...", fontsize=6.8, color='#374151', zorder=7)

    # Actions
    btn_cancel = patches.FancyBboxPatch((48, 15), 12, 4.2, boxstyle="round,pad=0.2", facecolor='#E5E7EB', edgecolor=None, zorder=7)
    ax.add_patch(btn_cancel)
    ax.text(54, 17.1, "Cancel", ha='center', va='center', color='#374151', fontsize=7.5, fontweight='bold', zorder=7)

    btn_submit = patches.FancyBboxPatch((62, 15), 14, 4.2, boxstyle="round,pad=0.2", facecolor=C_PRIMARY, edgecolor=None, zorder=7)
    ax.add_patch(btn_submit)
    ax.text(69, 17.1, "Send Inquiry ↗", ha='center', va='center', color='white', fontsize=7.5, fontweight='bold', zorder=7)

    save_fig(fig, "6_wireframe_product_inquiry.png")

# ==============================================================================
# 7. WIREFRAME: ORDER TRACKING & PRINTABLE INVOICE MODAL
# ==============================================================================
def draw_wireframe_order_tracking():
    fig, ax = plt.subplots(figsize=(10, 6.5), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 100)
    ax.axis('off')

    # Modal Box (Order Details)
    m_box = patches.FancyBboxPatch((5, 5), 90, 90, boxstyle="round,pad=1,rounding_size=2", facecolor='white', edgecolor=C_PRIMARY, lw=2)
    ax.add_patch(m_box)

    # Header
    ax.text(8, 89, "ORDER SHIPMENT DETAILS & DELIVERY TRACKING", fontsize=11, fontweight='bold', color=C_PRIMARY)
    ax.text(8, 85, "Order Number: #CM-20261115-0842 • Placed: Nov 15, 2026 • Payment: PayMongo (GCash Paid)", fontsize=7.5, color='#6B7280')
    ax.text(91, 89, "✕", fontsize=12, color='#9CA3AF', fontweight='bold')

    # Visual Tracking Stepper (5-Steps)
    stepper_bg = patches.FancyBboxPatch((8, 64), 84, 18, boxstyle="round,pad=0.5", facecolor='#FAF8F5', edgecolor='#E8DFD5')
    ax.add_patch(stepper_bg)
    ax.text(10, 78.5, "Live Fulfillment Tracking Journey", fontsize=8, fontweight='bold', color=C_PRIMARY)

    steps = [
        ("1. Placed", True, 18),
        ("2. Confirmed", True, 34),
        ("3. In Workshop", True, 50),
        ("4. Dispatched", True, 66),
        ("5. Delivered", False, 82)
    ]
    # Stepper line
    ax.plot([18, 66], [71, 71], color='#16A34A', lw=3, zorder=2)
    ax.plot([66, 82], [71, 71], color='#D1D5DB', lw=3, ls='--', zorder=2)

    for name, is_done, cx in steps:
        c_fill = '#16A34A' if is_done else 'white'
        c_edge = '#16A34A' if is_done else '#9CA3AF'
        c_txt = 'white' if is_done else '#6B7280'
        sym = '✓' if is_done else '○'
        circle = patches.Circle((cx, 71), 2.8, facecolor=c_fill, edgecolor=c_edge, lw=2, zorder=3)
        ax.add_patch(circle)
        ax.text(cx, 71, sym, ha='center', va='center', color=c_txt, fontsize=8, fontweight='bold', zorder=4)
        ax.text(cx, 66, name, ha='center', va='center', fontsize=7.2, fontweight='bold' if is_done else 'normal', color=C_DARK)

    # Tracking Badge pill
    trk_pill = patches.FancyBboxPatch((60, 60), 28, 3.2, boxstyle="round,pad=0.2", facecolor='#ECFDF5', edgecolor='#A7F3D0')
    ax.add_patch(trk_pill)
    ax.text(74, 61.6, "Courier: J&T Express • Trk: JT-99482103", ha='center', va='center', fontsize=6.8, color='#047857', fontweight='bold')

    # Item List Table
    ax.text(8, 52, "Purchased Handcrafted Items", fontsize=9, fontweight='bold', color=C_PRIMARY)
    tbl_hdr = patches.Rectangle((8, 45), 84, 4, facecolor=C_PRIMARY)
    ax.add_patch(tbl_hdr)
    ax.text(10, 47, "Craft Product & Artisan", va='center', color='white', fontsize=7.2, fontweight='bold')
    ax.text(55, 47, "Quantity", va='center', color='white', fontsize=7.2, fontweight='bold')
    ax.text(70, 47, "Unit Price", va='center', color='white', fontsize=7.2, fontweight='bold')
    ax.text(85, 47, "Subtotal", va='center', color='white', fontsize=7.2, fontweight='bold')

    # Row 1
    ax.text(10, 40, "Carved Narra Heritage Storage Chest\nMang Ben's Workshop (Paete, Laguna)", va='center', fontsize=7, color=C_DARK)
    ax.text(58, 40, "1", va='center', fontsize=7, color=C_DARK)
    ax.text(72, 40, "₱4,500.00", va='center', fontsize=7, color=C_DARK)
    ax.text(87, 40, "₱4,500.00", va='center', fontsize=7.5, fontweight='bold', color=C_DARK)

    # Row 2
    ax.text(10, 31, "Inabel Handwoven Throw Blanket (Blue/White)\nIlocos Heritage Weavers", va='center', fontsize=7, color=C_DARK)
    ax.text(58, 31, "1", va='center', fontsize=7, color=C_DARK)
    ax.text(72, 31, "₱1,250.00", va='center', fontsize=7, color=C_DARK)
    ax.text(87, 31, "₱1,250.00", va='center', fontsize=7.5, fontweight='bold', color=C_DARK)

    # Divider & Totals
    ax.plot([8, 92], [24, 24], color='#E5E7EB', lw=1)
    ax.text(65, 20, "Subtotal:\nShipping Fee (Luzon):\nTotal Paid (GCash):", fontsize=7.5, color='#4B5563')
    ax.text(90, 20, "₱5,750.00\n₱  150.00\n₱5,900.00", ha='right', fontsize=7.5, fontweight='bold', color=C_PRIMARY)

    # Footer Actions
    btn_print = patches.FancyBboxPatch((8, 8), 24, 5, boxstyle="round,pad=0.2", facecolor='#F3F4F6', edgecolor='#D1D5DB')
    ax.add_patch(btn_print)
    ax.text(20, 10.5, "🧾 Print Official Receipt", ha='center', va='center', color=C_PRIMARY, fontsize=7.5, fontweight='bold')

    btn_close = patches.FancyBboxPatch((76, 8), 16, 5, boxstyle="round,pad=0.2", facecolor=C_PRIMARY, edgecolor=None)
    ax.add_patch(btn_close)
    ax.text(84, 10.5, "Close Window", ha='center', va='center', color='white', fontsize=7.5, fontweight='bold')

    save_fig(fig, "7_wireframe_order_tracking.png")

# Run all generator functions
if __name__ == "__main__":
    draw_context_diagram()
    draw_architecture_diagram()
    draw_usecase_diagram()
    draw_er_diagram()
    draw_wireframe_homepage()
    draw_wireframe_product_inquiry()
    draw_wireframe_order_tracking()
    print("ALL DIAGRAMS GENERATED SUCCESSFULLY AS HIGH-RES PNG IMAGES!")
