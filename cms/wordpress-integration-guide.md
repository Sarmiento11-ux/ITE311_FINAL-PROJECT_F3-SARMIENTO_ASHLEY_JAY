# WordPress Headless CMS Integration Guide
## CraftMarket System Integration & Architecture

### 1. Architectural Strategy & Compliance
CraftMarket complies with **Section C (CMS-Based Admin Panel)** of the Web Systems & Technologies 2 / System Integration & Architecture 1 requirements using a **Modern Decoupled / Headless CMS Pattern**.

```mermaid
flowchart LR
    A["Public Vue.js Storefront<br/>(Client UI)"] <-->|REST API| B["Laravel 12 API<br/>(Transactional Engine & MySQL)"]
    A <-->|REST API (JSON)| C["Headless WordPress CMS<br/>(Content, Announcements)"]
    C <-->|Internal Bridge Plugin| B
```

---

### 2. The 5 Required CMS Functions Matrix

| Required Function | Managed Where | Endpoint / Interface |
| :--- | :--- | :--- |
| **1. Website Content** | WordPress Pages | `GET /wp-json/wp/v2/pages?slug=homepage` |
| **2. Announcements** | WordPress Posts | `GET /wp-json/wp/v2/posts?search=announcement` |
| **3. Products / Services** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-products` & `/api/products` |
| **4. User Data** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-users` & `/api/users` |
| **5. Reports** | WordPress Admin + Laravel | `wp-admin/admin.php?page=craftmarket-reports` & `/api/admin/reports` |

---

### 3. Verification & Live Demo Instructions for Defense
1. **To demo Content & Announcements:**
   - Log into WordPress Admin: `http://localhost/craftmarket-cms/wp-admin`
   - Create or edit a post tagged/titled `announcement`.
   - Reload CraftMarket Vue storefront (`http://localhost:5173`) &rarr; top banner immediately reflects the updated announcement text.
2. **To demo Products, Users, and Reports in WordPress:**
   - In WordPress Admin sidebar, click **CraftMarket CMS**.
   - Show the **Products & Crafts** table (displays live crafts and stock from Laravel).
   - Show the **Marketplace Users** table (displays active shopper and seller accounts).
   - Show the **Escrow Reports** card (displays total realized sales and orders).
