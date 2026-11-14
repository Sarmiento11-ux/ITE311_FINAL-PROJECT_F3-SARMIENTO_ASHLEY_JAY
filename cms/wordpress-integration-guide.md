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
| **3. Products / Services** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-products` & `GET /api/products` |
| **4. User Data** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-users` & `GET /api/cms-bridge/users` |
| **5. Reports** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-reports` & `GET /api/cms-bridge/reports` |

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
   ```
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
2. **To demo Products, Users, and Reports in WordPress:**
   - In WordPress Admin sidebar, click **CraftMarket CMS**.
   - Show the **Products & Crafts** table (displays live crafts and stock from Laravel).
   - Show the **Marketplace Users** table (displays active shopper and seller accounts).
   - Show the **Escrow Reports** card (displays total realized sales and orders).
