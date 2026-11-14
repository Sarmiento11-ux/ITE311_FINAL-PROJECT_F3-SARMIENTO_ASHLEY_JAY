import axios from 'axios';

// WordPress Headless REST API Base URL
const WP_API_BASE = import.meta.env.VITE_WP_API_BASE_URL || 'http://localhost/craftmarket-cms/wp-json/wp/v2';

const wpClient = axios.create({
  baseURL: WP_API_BASE,
  timeout: 4000,
  headers: {
    'Accept': 'application/json',
  },
});

/**
 * Robust HTML entity decoding and tag stripping using the browser DOM parser.
 * Protects against XSS injection from external CMS or user-supplied content.
 */
function sanitizeHtmlText(raw) {
  if (!raw || typeof raw !== 'string') return '';
  try {
    const doc = new DOMParser().parseFromString(raw, 'text/html');
    return (doc.body.textContent || '').trim();
  } catch {
    return raw.replace(/<[^>]+>/g, '').trim();
  }
}

export const cmsService = {
  /**
   * 1. Retrieve the latest community announcement.
   * Fetches from WordPress posts or pages with fallback.
   */
  async getLatestAnnouncement() {
    try {
      // 1. Try querying posts in the 'announcements' category (ID 2 by default)
      let response = await wpClient.get('/posts', {
        params: { categories: 2, per_page: 1 }
      }).catch(() => null);

      // 2. If not found, dynamically resolve the category ID by slug 'announcements'
      if (!response || !Array.isArray(response.data) || response.data.length === 0) {
        try {
          const catRes = await wpClient.get('/categories', {
            params: { slug: 'announcements', per_page: 1 }
          });
          if (Array.isArray(catRes.data) && catRes.data.length > 0) {
            response = await wpClient.get('/posts', {
              params: { categories: catRes.data[0].id, per_page: 1 }
            }).catch(() => null);
          }
        } catch {
          // fallback continues
        }
      }

      // 3. Fallback: text search for 'announcement' in titles/content
      if (!response || !Array.isArray(response.data) || response.data.length === 0) {
        response = await wpClient.get('/posts', {
          params: { search: 'announcement', per_page: 1 }
        }).catch(() => null);
      }

      if (response && Array.isArray(response.data) && response.data.length > 0) {
        const item = response.data[0];
        // Strip HTML tags from rendered content; prioritize message body over headline
        const bodyText = sanitizeHtmlText(item.content?.rendered || item.excerpt?.rendered);
        const titleText = sanitizeHtmlText(item.title?.rendered);
        const text = bodyText || titleText;
        if (text) {
          return {
            text,
            isFromCms: true,
            date: item.date
          };
        }
      }
    } catch {
      // Graceful fallback to default banner
    }

    return {
      text: 'Free nationwide shipping on handcrafted orders over ₱1,500 • Direct Maker Support',
      isFromCms: false,
      date: new Date().toISOString()
    };
  },

  /**
   * 2. Retrieve homepage hero banner content.
   */
  async getHeroContent() {
    try {
      const response = await wpClient.get('/pages', {
        params: { slug: 'homepage' }
      });
      if (Array.isArray(response.data) && response.data.length > 0) {
        const page = response.data[0];
        return {
          eyebrow: 'SUPPORT LOCAL ARTISANS & CULTURAL HERITAGE',
          title: sanitizeHtmlText(page.title?.rendered) || 'Discover Unique Filipino Craftsmanship',
          description: sanitizeHtmlText(page.excerpt?.rendered) || 
            sanitizeHtmlText(page.content?.rendered) || 
            'Connect directly with independent local weavers, potters, woodworkers, and jewelers across the Philippine archipelago.',
          ctaText: 'Explore Handmade Products',
          ctaLink: '/products',
          isFromCms: true
        };
      }
    } catch {
      // Graceful fallback
    }

    return {
      eyebrow: 'SUPPORT LOCAL ARTISANS & CULTURAL HERITAGE',
      title: 'Discover Unique Filipino Craftsmanship',
      description: 'Connect directly with independent local weavers, potters, woodworkers, and jewelers across the Philippine archipelago. Every piece tells an authentic story of regional tradition and sustainable livelihood.',
      ctaText: 'Explore Handmade Products',
      ctaLink: '/products',
      isFromCms: false
    };
  },

  /**
   * 3. Retrieve About Us section content.
   */
  async getAboutContent() {
    try {
      const response = await wpClient.get('/pages', {
        params: { slug: 'about-us' }
      });
      if (Array.isArray(response.data) && response.data.length > 0) {
        const page = response.data[0];
        let paragraphs = [];
        if (page.content?.rendered) {
          try {
            const doc = new DOMParser().parseFromString(page.content.rendered, 'text/html');
            const pTags = Array.from(doc.querySelectorAll('p'))
              .map(p => (p.textContent || '').trim())
              .filter(t => t.length > 0);
            if (pTags.length > 0) {
              paragraphs = pTags;
            }
          } catch {
            const text = sanitizeHtmlText(page.content.rendered);
            if (text) paragraphs = [text];
          }
        }
        if (paragraphs.length === 0) {
          paragraphs = [
            'Across Luzon, Visayas, and Mindanao, indigenous weaving communities, family-run potteries, and generational woodcarvers have sustained their cultural craft for centuries.',
            'CraftMarket eliminates unfair middleman markups by enabling makers to list directly, manage their stalls, and receive fair, dignified earnings for every piece sold.'
          ];
        }

        return {
          badge: 'OUR ROOTS & PURPOSE',
          title: sanitizeHtmlText(page.title?.rendered) || 'Preserving Philippine Craftsmanship & Empowering Artisan Communities',
          leadText: sanitizeHtmlText(page.excerpt?.rendered) || paragraphs[0] || 'CraftMarket was established to bridge the gap between talented provincial artisans and conscious buyers seeking authentic, sustainable, and ethically handcrafted goods.',
          paragraphs,
          isFromCms: true
        };
      }
    } catch {
      // Graceful fallback
    }

    return {
      badge: 'OUR ROOTS & PURPOSE',
      title: 'Preserving Philippine Craftsmanship & Empowering Artisan Communities',
      leadText: 'CraftMarket was established to bridge the gap between talented provincial artisans and conscious buyers seeking authentic, sustainable, and ethically handcrafted goods.',
      paragraphs: [
        'Across Luzon, Visayas, and Mindanao, indigenous weaving communities, family-run potteries, and generational woodcarvers have sustained their cultural craft for centuries. However, modern commercial retail often disconnects these creators from fair compensation.',
        'CraftMarket eliminates unfair middleman markups by enabling makers to list directly, manage their stalls, and receive fair, dignified earnings for every piece sold.'
      ],
      isFromCms: false
    };
  },

  /**
   * 4. Retrieve latest blog posts & artisan stories.
   */
  async getBlogPosts() {
    try {
      const response = await wpClient.get('/posts', {
        params: { _embed: 1, per_page: 3 }
      });
      if (Array.isArray(response.data) && response.data.length > 0) {
        return response.data.map(p => {
          const featuredMedia = p._embedded?.['wp:featuredmedia']?.[0]?.source_url;
          return {
            id: p.id,
            title: p.title?.rendered || 'Artisan Story',
            excerpt: sanitizeHtmlText(p.excerpt?.rendered) || '',
            content: sanitizeHtmlText(p.content?.rendered) || sanitizeHtmlText(p.excerpt?.rendered) || '',
            date: new Date(p.date).toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'short',
              day: 'numeric'
            }),
            category: 'Artisan Heritage',
            image: featuredMedia || 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=80',
            link: p.link || '#',
            isFromCms: true
          };
        });
      }
    } catch {
      // Graceful fallback
    }

    return [
      {
        id: 101,
        title: 'The Sacred Art of Cordillera Weaving: Behind the Backstrap Loom',
        excerpt: 'Meet Elena and the indigenous Kalinga women weavers keeping geometric ancestral patterns alive through every thread.',
        date: 'Oct 28, 2026',
        category: 'Weaving Heritage',
        image: 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=80',
        author: 'Elena Bautista',
        readTime: '4 min read',
        isFromCms: false
      },
      {
        id: 102,
        title: "From Clay to Kiln: Laguna's Centuries-Old Pottery Traditions",
        excerpt: 'How native clay harvesting and mineral glazing create durable everyday kitchen stoneware that honors the earth.',
        date: 'Oct 24, 2026',
        category: 'Ceramic Arts',
        image: 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80',
        author: 'Marco Gomez',
        readTime: '5 min read',
        isFromCms: false
      },
      {
        id: 103,
        title: 'Sustainable Living: How Abaca & Bamboo Are Transforming Modern Homes',
        excerpt: 'Why interior designers and conscious homeowners are choosing biodegradable natural fibers over synthetic plastic decor.',
        date: 'Oct 20, 2026',
        category: 'Sustainable Living',
        image: 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=80',
        author: 'CraftMarket Editorial',
        readTime: '3 min read',
        isFromCms: false
      }
    ];
  },

  /**
   * 5. Get current configured endpoint.
   */
  getEndpoint() {
    return WP_API_BASE;
  },

  /**
   * 6. Live health check / ping to WordPress REST API.
   */
  async checkHealth() {
    try {
      const response = await wpClient.get('/posts', { params: { per_page: 1 } });
      return {
        online: response.status >= 200 && response.status < 300,
        endpoint: WP_API_BASE,
      };
    } catch (err) {
      return {
        online: false,
        endpoint: WP_API_BASE,
        error: err.message || 'Connection offline',
      };
    }
  }
};
