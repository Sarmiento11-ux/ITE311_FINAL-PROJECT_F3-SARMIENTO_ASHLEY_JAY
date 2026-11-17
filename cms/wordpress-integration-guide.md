# WordPress Headless CMS Integration Guide
## CraftMarket System Integration & Architecture

### 1. Architectural Strategy & Compliance
CraftMarket complies with **Section C (CMS-Based Admin Panel)** of the Web Systems & Technologies 2 / System Integration & Architecture 1 requirements using a **Modern Decoupled / Headless CMS Pattern**.

```mermaid
flowchart LR
    A["Public Vue.js Storefront<br/>(Client UI)"] <-->|REST API| B["Laravel 12 API<br/>(Transactional Engine & MySQL)"]
    A <-->|REST API (JSON)| C["Headless WordPress CMS<br/>(Content, Announcements)"]
    C <-->|Internal Bridge Plugin<br/>X-CMS-Bridge-Key| B
```

---

### 2. The 5 Required CMS Functions Matrix

| Required Function | Managed Where | Endpoint / Interface |
| :--- | :--- | :--- |
| **1. Website Content** | WordPress Pages | `GET /wp-json/wp/v2/pages?slug=homepage`, `GET /wp-json/wp/v2/pages?slug=about-us` |
| **2. Announcements** | WordPress Posts | `GET /wp-json/wp/v2/posts?search=announcement` |
| **3. Products / Services** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-products` · `GET /api/cms-bridge/products` · `PATCH /api/cms-bridge/products/{id}/status` |
| **4. User Data** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-users` · `GET /api/cms-bridge/users` · `PATCH /api/cms-bridge/users/{id}/toggle-active` |
| **5. Reports** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-reports` · `GET /api/cms-bridge/reports` |
| **Extension: Category Taxonomy** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-categories` · `GET/POST /api/cms-bridge/categories` · `PUT/DELETE /api/cms-bridge/categories/{id}` |
| **Extension: Artisan Verification** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-verification` · `GET /api/cms-bridge/sellers` · `PATCH /api/cms-bridge/sellers/{id}/verification` |

### 2.1 Management actions exposed to the CMS

| CMS action | WordPress surface | Laravel effect |
| :--- | :--- | :--- |
| Change a listing's status | Per-row select + **Update** (Products page) | Sets `products.status` to `active`, `inactive`, `out_of_stock`, or `archived` |
| Add / edit / delete a category | Category form and per-row **Edit** / **Delete** (Categories page) | Creates, updates, or deletes `categories`; delete is blocked with `422` while products reference the category |
| Approve / reject a storefront | Status select + moderator notes + **Save Decision** (Artisan Verification page) | Updates `verification_status`, `is_verified`, `verified_at`, `verification_reviewed_at`, and emails the artisan |
| Activate / deactivate an account | Per-row **Activate** / **Deactivate** (Users page) | Sets `users.is_active`; deactivation revokes all Sanctum tokens |
| View financial and operational reports | Escrow Reports page + dashboard widget | Reads aggregate sales, commission, escrow, payouts, MRR, best sellers |

Every write action is protected twice:

1. **WordPress side** — `check_admin_referer()` (nonce) and `current_user_can('manage_options')`.
2. **Laravel side** — `cms.bridge` shared secret plus strict enum/boolean validation.

Administrator accounts cannot be deactivated from the CMS bridge, and the bridge can never modify orders, payments, escrow, payouts, or product pricing. Category deletion is additionally blocked while products still reference the category, so the CMS cannot orphan live listings.

---

### 3. CMS Bridge Authentication & Security

Internal bridge synchronization endpoints (`/api/cms-bridge/*`) are protected from unauthenticated access using the `VerifyCmsBridgeKey` middleware (`cms.bridge`).

1. **Backend Configuration (`backend/.env`):**
   ```env
   CMS_BRIDGE_KEY=dev-cms-bridge-key
   ```
2. **WordPress Plugin Configuration (`wp-config.php` or filter):**
   ```php
   define('CRAFTMARKET_CMS_BRIDGE_KEY', 'dev-cms-bridge-key');
   define('CRAFTMARKET_API_BASE', 'http://127.0.0.1:8000/api'); // optional override
   ```
   The API base can also be changed with the `craftmarket_api_base` filter, and the request timeout with `craftmarket_cms_timeout`.
3. **Authentication Header:**
   The `craftmarket-cms-sync.php` plugin automatically includes the header with every server-to-server request:
   ```http
   X-CMS-Bridge-Key: dev-cms-bridge-key
   ```

---

### 4. Cross-Origin Resource Sharing (CORS) Configuration

When hosting WordPress under Apache/WAMP/XAMPP and serving the Vue storefront from `http://localhost:5173`, WordPress REST API responses must allow CORS:

1. **Option A: Apache `.htaccess` (WordPress Root)**
   ```apache
   <IfModule mod_headers.c>
       Header set Access-Control-Allow-Origin "*"
       Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
       Header set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
   </IfModule>
   ```

2. **Option B: WordPress Theme `functions.php` or mu-plugin**
   ```php
   add_action('rest_api_init', function () {
       remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
       add_filter('rest_pre_serve_request', function ($value) {
           header('Access-Control-Allow-Origin: *');
           header('Access-Control-Allow-Methods: GET, OPTIONS');
           header('Access-Control-Allow-Headers: Authorization, Content-Type');
           return $value;
       });
   }, 15);
   ```

3. **Frontend Client Fallback:**
   `frontend/src/services/cmsService.js` incorporates a 4000ms timeout and graceful fallback to static defaults if the WordPress CMS is offline or unreachable during cold starts.

---

### 5. Verification & Live Demo Instructions for Defense
1. **To demo Content & Announcements:**
   - Log into WordPress Admin: `http://localhost/craftmarket-cms/wp-admin`
   - Create or edit a post tagged/titled `announcement`.
   - Reload CraftMarket Vue storefront (`http://localhost:5173`) &rarr; top banner immediately reflects the updated announcement text.
2. **To demo Products, Categories, Artisan Verification, Users, and Reports in WordPress:**
   - In WordPress Admin sidebar, click **CraftMarket CMS**.
   - Show the **Products & Crafts** table (live crafts, stock, artisan, status).
   - Change one listing to **Archived** and click **Update**; reload the storefront to show it disappeared from the public catalog.
   - Open **Categories**, create a new taxonomy entry, edit it, then try deleting a category that still owns products — the API returns the guarded `422` message.
   - Open **Artisan Verification**, filter to **Pending review**, approve one storefront, then show the seller notification email (Mailpit/SMTP). Reject another with moderator notes.
   - Show the **Marketplace Users** table (shoppers, artisans, admins).
   - Deactivate a demo customer; the notice confirms the account and its sessions were revoked.
   - Reactivate the same customer to restore access.
   - Show the **Escrow Reports** page: GMV, orders, commission, MRR, escrow ledger, payout summary, order breakdown, best sellers.
3. **To demo the bridge security:**
   - Open `http://127.0.0.1:8000/api/cms-bridge/products` without the header → `401`.
   - Remove `CMS_BRIDGE_KEY` from `backend/.env` and reload → `503` (fail closed).
4. **To run the automated proof:**
   ```powershell
   cd backend
   php artisan test --filter=CmsBridgeTest
   ```
   Expected: **13 passed** — bridge secret, fail-closed guard, catalog, product moderation, category CRUD + delete guard, storefront listing, approve/reject with email, token revocation, and admin protection.
