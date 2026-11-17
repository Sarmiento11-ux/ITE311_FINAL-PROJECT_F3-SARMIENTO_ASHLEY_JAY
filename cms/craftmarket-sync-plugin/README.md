# CraftMarket WordPress CMS Bridge Plugin

This plugin connects your WordPress installation (`craftmarket-cms`) directly with CraftMarket's Laravel REST API (`http://127.0.0.1:8000/api`) to satisfy **Section C (CMS-Based Admin Panel)** of the project requirements.

## Handled CMS Functions

1. **Website Content:** Managed in standard WordPress Pages (Hero Banner, About Us, Policies).
2. **Announcements:** Managed in standard WordPress Posts (Notice Bar & Promos).
3. **Products & Services:** Live synchronized catalog from `/api/cms-bridge/products`, with status moderation (`active`, `inactive`, `out_of_stock`, `archived`).
4. **Category Taxonomy:** Add, edit, and delete product categories through `/api/cms-bridge/categories`; deletion is blocked while products still reference the category.
5. **Artisan Verification:** Review storefront applications from `/api/cms-bridge/sellers`, then approve or reject with moderator notes through `/api/cms-bridge/sellers/{id}/verification`; the artisan receives an email.
6. **User Data Management:** Live synchronized view of Shoppers, Sellers, and Admins from `/api/cms-bridge/users`, with activate/deactivate controls.
7. **Sales & Reports:** GMV, commission, escrow ledger, payout summary, MRR, order breakdown, and best sellers from `/api/cms-bridge/reports`.

## Management Actions

| Action | Guard |
|---|---|
| Change product status | WordPress nonce + `manage_options`; Laravel enum validation |
| Add / edit / delete category | WordPress nonce + `manage_options`; Laravel validates unique slug; delete blocked while products exist |
| Approve / reject storefront | WordPress nonce + `manage_options`; Laravel validates status enum and emails the artisan |
| Activate / deactivate account | WordPress nonce + `manage_options`; deactivation revokes Sanctum tokens; admins are protected |

The bridge cannot modify orders, payments, escrow, payouts, or product pricing.

## Installation into WordPress

1. Copy the `craftmarket-sync-plugin` folder into your local WordPress directory:
   `C:\xampp\htdocs\craftmarket-cms\wp-content\plugins\craftmarket-sync-plugin`
2. Log in to WordPress Admin (`http://localhost/craftmarket-cms/wp-admin`).
3. Navigate to **Plugins** &rarr; **Installed Plugins**.
4. Click **Activate** under **CraftMarket CMS Bridge & Sync**.
5. You will see the new **CraftMarket CMS** menu with Products, Categories, Artisan Verification, Users, and Reports in your WordPress sidebar.

## Configuration

Add these to `wp-config.php`:

```php
define('CRAFTMARKET_CMS_BRIDGE_KEY', 'dev-cms-bridge-key'); // must match backend CMS_BRIDGE_KEY
define('CRAFTMARKET_API_BASE', 'http://127.0.0.1:8000/api'); // optional
```

Without a matching key the Laravel API returns `401`. If `CMS_BRIDGE_KEY` is empty on the backend, it returns `503` (fail closed).
