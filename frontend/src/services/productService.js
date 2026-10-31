/**
 * CraftMarket - Product Service Layer
 * Currently serves realistic artisan mock data; designed for seamless switch to Laravel REST API endpoints (/api/v1/products).
 */

export const craftProducts = [
  {
    id: 1,
    title: "Earthy Terracotta Coffee Mug",
    category: "Ceramics & Pottery",
    price: 420.0,
    seller: "Studio Clayworks",
    location: "Laguna, Philippines",
    rating: 4.9,
    reviewsCount: 24,
    badge: "Featured",
    stock: 8,
    image: "https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Hand-thrown stoneware mug made with natural local clay and food-safe matte mineral glaze.",
    description: "Hand-thrown on a traditional potter's wheel using locally sourced Laguna stoneware clay. This mug features a natural unglazed earthy exterior combined with a food-safe glazed interior. Fired at over 1,200°C for exceptional strength, heat retention, and daily kitchen durability.",
    materials: "100% Native Clay, Non-Toxic Mineral Glaze",
    dimensions: "350ml capacity • 9.0cm height x 8.5cm diameter",
    origin: "Pila, Laguna, Philippines"
  },
  {
    id: 2,
    title: "Handwoven Abaca Market Tote",
    category: "Woven Goods & Bags",
    price: 780.0,
    seller: "Cordillera Weaves",
    location: "Baguio City, Philippines",
    rating: 5.0,
    reviewsCount: 42,
    badge: "Best Seller",
    stock: 12,
    image: "https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Tightly handwoven from natural abaca fibers with sturdy vegetable-tanned leather straps.",
    description: "Tightly handwoven from sun-dried natural abaca fibers by women artisan weavers. Features durable vegetable-tanned genuine leather shoulder straps reinforced with antique brass rivets. Lightweight, breathable, and designed to carry groceries, books, or beach essentials.",
    materials: "100% Natural Abaca Fiber, Veg-Tanned Leather, Brass Rivets",
    dimensions: "38cm width x 32cm height x 12cm base gusset",
    origin: "Baguio, Benguet, Philippines"
  },
  {
    id: 3,
    title: "Palawan Freshwater Pearl Bracelet",
    category: "Artisan Jewelry",
    price: 350.0,
    seller: "Tala Artisan Co.",
    location: "Puerto Princesa, Palawan",
    rating: 4.8,
    reviewsCount: 18,
    badge: "Handcrafted",
    stock: 15,
    image: "https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Natural baroque freshwater pearls strung on waterproof cord with recycled brass beads.",
    description: "Individually handcrafted with authentic Palawan baroque freshwater pearls known for their organic shapes and luminous luster. Complemented with recycled brass spacer beads and durable waterproof macrame cord with an adjustable sliding knot.",
    materials: "Natural Baroque Pearls, Recycled Brass, Waterproof Cord",
    dimensions: "Adjustable sliding clasp (fits 16cm to 21cm wrist)",
    origin: "Puerto Princesa, Palawan, Philippines"
  },
  {
    id: 4,
    title: "Botanical Soy Candle in Amber Jar",
    category: "Home Decor & Candles",
    price: 295.0,
    seller: "Habilin Apothecary",
    location: "Tagaytay, Cavite",
    rating: 4.9,
    reviewsCount: 31,
    badge: "Handcrafted",
    stock: 20,
    image: "https://images.unsplash.com/photo-1602874801007-bd458bb1b8b8?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Hand-poured 100% natural soy wax scented with native wild sampaguita and lemongrass.",
    description: "Hand-poured in small batches using 100% natural, biodegradable soy wax. Infused with pure essential oils of native wild sampaguita flower and fresh mountain lemongrass. Features a lead-free organic cotton wick for a clean, soot-free burn of over 45 hours.",
    materials: "Golden Soy Wax, Pure Botanical Essential Oils, Cotton Wick",
    dimensions: "200g net weight • 8.5cm height x 7cm diameter",
    origin: "Tagaytay, Cavite, Philippines"
  },
  {
    id: 5,
    title: "Carved Acacia Wood Serving Tray",
    category: "Wooden Crafts",
    price: 650.0,
    seller: "Paete Woodcraft Guild",
    location: "Paete, Laguna",
    rating: 4.9,
    reviewsCount: 15,
    badge: "Featured",
    stock: 6,
    image: "https://images.unsplash.com/photo-1546554137-f86b9593a222?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Sculpted from a single slab of sustainable acacia wood and finished with organic food-grade beeswax.",
    description: "Carved by third-generation woodcrafters from a single piece of reclaimed fallen acacia wood. Showcases rich organic grain patterns, beveled comfort edges, and a silky smooth finish protected with food-grade virgin coconut oil and organic beeswax.",
    materials: "Reclaimed Solid Acacia Hardwood, Organic Beeswax Finish",
    dimensions: "40cm length x 22cm width x 3cm depth",
    origin: "Paete, Laguna, Philippines"
  },
  {
    id: 6,
    title: "Handwoven Rattan Planter Basket",
    category: "Woven Goods & Bags",
    price: 480.0,
    seller: "Bicol Craft Collective",
    location: "Albay, Bicol",
    rating: 4.7,
    reviewsCount: 29,
    badge: "Best Seller",
    stock: 10,
    image: "https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Traditional wicker pattern hand-plaited using seasoned natural forest rattan vines.",
    description: "Traditional wicker pattern hand-plaited using seasoned natural forest rattan vines. Designed with a flexible internal weave that accommodates standard 7 to 9 inch plastic nursery pots, elevating your indoor botanical garden.",
    materials: "100% Natural Forest Rattan Vines, Cotton Thread Binding",
    dimensions: "25cm diameter x 24cm height",
    origin: "Tabaco, Albay, Philippines"
  },
  {
    id: 7,
    title: "Macrame Plant Hanger with Wood Ring",
    category: "Home Decor & Candles",
    price: 320.0,
    seller: "Knot & Thread Studio",
    location: "Iloilo City, Philippines",
    rating: 4.8,
    reviewsCount: 22,
    badge: "Handcrafted",
    stock: 14,
    image: "https://images.unsplash.com/photo-1490312278390-ab64016e0aa9?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Intricately knotted with 100% unbleached cotton cord and suspended from an acacia wood ring.",
    description: "Intricately knotted using soft 100% natural unbleached Philippine cotton cord. Suspended from a hand-turned and sanded acacia wooden ring. Sturdy design safely supports ceramic pots or glass bowls up to 22cm in diameter.",
    materials: "4mm Natural Cotton Rope, Acacia Wood Hanging Ring",
    dimensions: "105cm total length (from top ring to tassel base)",
    origin: "Iloilo City, Philippines"
  },
  {
    id: 8,
    title: "Ceramic Minimalist Pour-Over Dripper",
    category: "Ceramics & Pottery",
    price: 520.0,
    seller: "Studio Clayworks",
    location: "Laguna, Philippines",
    rating: 5.0,
    reviewsCount: 37,
    badge: "Featured",
    stock: 9,
    image: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80",
    shortDesc: "Custom ribbed interior designed for balanced extraction. High-fired durable stoneware pottery.",
    description: "A functional piece of ceramic art engineered specifically for pour-over coffee enthusiasts. Internal spiral channels maximize brew aeration and uniform flavor extraction. Compatible with cone-shaped 01 and 02 paper coffee filters.",
    materials: "High-Fire Stoneware, Matte Mineral Glaze",
    dimensions: "11cm top diameter x 9cm height x 10cm base plate",
    origin: "Pila, Laguna, Philippines"
  }
];

export const productService = {
  async getAll() {
    return Promise.resolve(craftProducts);
  },
  async getById(id) {
    const numId = parseInt(id, 10);
    const product = craftProducts.find(p => p.id === numId);
    return Promise.resolve(product || null);
  },
  async getFeatured() {
    return Promise.resolve(craftProducts.slice(0, 4));
  },
  async getRelated(categoryId, currentId) {
    return Promise.resolve(
      craftProducts.filter(p => p.id !== currentId && p.category === categoryId).slice(0, 4)
    );
  }
};
