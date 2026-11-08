# CraftMarket WordPress CMS Bridge Plugin

This plugin connects your WordPress installation (`craftmarket-cms`) directly with CraftMarket's Laravel REST API (`http://127.0.0.1:8000/api`) to satisfy **Section C (CMS-Based Admin Panel)** of the project requirements:

## Handled CMS Functions
1. **Website Content:** Managed in standard WordPress Pages (Hero Banner, About Us, Policies).
2. **Announcements:** Managed in standard WordPress Posts (Notice Bar & Promos).
3. **Products & Services:** Live synchronized view from `/api/products`.
4. **User Data Management:** Live synchronized view of Shoppers, Sellers, and Admins from `/api/users`.
5. **Sales & Reports:** Live platform GMV, realized revenue, and order metrics from `/api/admin/reports`.

## Installation into WordPress
1. Copy the `craftmarket-sync-plugin` folder into your local WordPress directory:
   `C:\xampp\htdocs\craftmarket-cms\wp-content\plugins\craftmarket-sync-plugin`
2. Log in to WordPress Admin (`http://localhost/craftmarket-cms/wp-admin`).
3. Navigate to **Plugins** &rarr; **Installed Plugins**.
4. Click **Activate** under **CraftMarket CMS Bridge & Sync**.
5. You will see the new **CraftMarket CMS** menu with Products, Users, and Reports in your WordPress sidebar!
