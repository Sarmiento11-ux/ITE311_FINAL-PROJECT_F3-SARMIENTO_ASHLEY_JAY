# WordPress Headless CMS Integration Guide
## CraftMarket Architecture

### 1. Architectural Strategy
To avoid bloated dependencies, CraftMarket utilizes **WordPress as a Headless Content Management System (CMS)**. 

- **What WordPress Handles:**
  - Marketplace announcements (Notice bar and popups)
  - Artisan spotlight articles & storytelling blogs
  - Static legal policies (Privacy Policy, Terms of Service, Seller Guidelines)
  
- **What WordPress DOES NOT Handle:**
  - User accounts and password hashing (handled by Laravel)
  - Shopping cart, checkouts, and escrow payments (handled by Laravel)
  - Product inventory and order management (handled by Laravel)

---

### 2. WordPress REST API Endpoints
CraftMarket's Vue.js frontend communicates with WordPress via standard HTTP GET requests:

| Purpose | WordPress REST API Endpoint |
| :--- | :--- |
| Active Community Announcements | `GET /wp-json/wp/v2/announcements?per_page=1` |
| Artisan Story Blog Posts | `GET /wp-json/wp/v2/posts?categories=artisan-stories` |
| Privacy Policy Content | `GET /wp-json/wp/v2/pages?slug=privacy-policy` |
| Terms & Conditions | `GET /wp-json/wp/v2/pages?slug=terms-of-service` |

---

### 3. Frontend Integration Service Example (`wpService.js`)
In `frontend/src/services/wpService.js`:

```javascript
const WP_API_BASE = 'http://localhost/craftmarket-cms/wp-json/wp/v2';

export const wpService = {
  async getLatestAnnouncement() {
    try {
      const res = await fetch(`${WP_API_BASE}/posts?tags=announcement&per_page=1`);
      const data = await res.json();
      return data[0]?.title?.rendered || 'Support 120+ Verified Local Makers';
    } catch (e) {
      // Graceful fallback to static message if WP server is offline
      return 'Free shipping on local artisan orders over ₱1,500';
    }
  }
};
```
