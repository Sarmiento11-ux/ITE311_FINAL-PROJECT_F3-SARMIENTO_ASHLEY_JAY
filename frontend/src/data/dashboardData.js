/**
 * CraftMarket - Dashboard Sample Datasets
 * Provides structured sample data for Customer order histories and Seller merchant operations.
 */

export const customerOrdersData = [
  {
    id: "CM-2026-1042",
    productName: "Earthy Terracotta Coffee Mug",
    artisan: "Studio Clayworks",
    date: "Oct 28, 2026",
    totalAmount: 420.00,
    quantity: 1,
    status: "Delivered",
    paymentMethod: "GCash Escrow",
    trackingNumber: "CM-TRK-98112",
    deliveryAddress: "Unit 402, Katipunan Ave, Quezon City"
  },
  {
    id: "CM-2026-1035",
    productName: "Handwoven Abaca Market Tote",
    artisan: "Cordillera Weaves",
    date: "Oct 24, 2026",
    totalAmount: 780.00,
    quantity: 1,
    status: "In Transit",
    paymentMethod: "Maya Wallet",
    trackingNumber: "CM-TRK-98044",
    deliveryAddress: "Unit 402, Katipunan Ave, Quezon City"
  },
  {
    id: "CM-2026-0988",
    productName: "Botanical Soy Candle in Amber Jar",
    artisan: "Habilin Apothecary",
    date: "Oct 15, 2026",
    totalAmount: 590.00,
    quantity: 2,
    status: "Delivered",
    paymentMethod: "Credit Card (Escrow)",
    trackingNumber: "CM-TRK-97621",
    deliveryAddress: "Unit 402, Katipunan Ave, Quezon City"
  },
  {
    id: "CM-2026-0912",
    productName: "Carved Acacia Wood Serving Tray",
    artisan: "Paete Woodcraft Guild",
    date: "Sep 30, 2026",
    totalAmount: 650.00,
    quantity: 1,
    status: "Pending Escrow",
    paymentMethod: "Cash on Delivery",
    trackingNumber: "CM-TRK-97103",
    deliveryAddress: "Unit 402, Katipunan Ave, Quezon City"
  }
];

export const sellerProductsData = [
  {
    id: 101,
    name: "Earthy Terracotta Coffee Mug",
    category: "Ceramics & Pottery",
    price: 420.00,
    stock: 8,
    status: "Published",
    salesCount: 24,
    image: "https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=400&q=80"
  },
  {
    id: 102,
    name: "Ceramic Minimalist Pour-Over Dripper",
    category: "Ceramics & Pottery",
    price: 520.00,
    stock: 9,
    status: "Published",
    salesCount: 37,
    image: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=400&q=80"
  },
  {
    id: 103,
    name: "Glazed Speckled Clay Planter Bowl",
    category: "Ceramics & Pottery",
    price: 680.00,
    stock: 3,
    status: "Low Stock",
    salesCount: 14,
    image: "https://images.unsplash.com/photo-1490312278390-ab64016e0aa9?auto=format&fit=crop&w=400&q=80"
  },
  {
    id: 104,
    name: "Raku Fired Ceramic Tea Set",
    category: "Ceramics & Pottery",
    price: 1250.00,
    stock: 0,
    status: "Draft",
    salesCount: 0,
    image: "https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=400&q=80"
  }
];

export const sellerOrdersData = [
  {
    id: "CM-ORD-5501",
    customerName: "Maria Clara Santos",
    product: "Earthy Terracotta Coffee Mug",
    quantity: 2,
    totalAmount: 840.00,
    orderDate: "Oct 30, 2026",
    status: "Pending Dispatch",
    shippingAddress: "Quezon City, Metro Manila",
    payoutStatus: "Held in Escrow"
  },
  {
    id: "CM-ORD-5489",
    customerName: "Juan Dela Cruz",
    product: "Ceramic Minimalist Pour-Over Dripper",
    quantity: 1,
    totalAmount: 520.00,
    orderDate: "Oct 29, 2026",
    status: "In Transit",
    shippingAddress: "BGC, Taguig City",
    payoutStatus: "Held in Escrow"
  },
  {
    id: "CM-ORD-5442",
    customerName: "Elena Ramos",
    product: "Glazed Speckled Clay Planter Bowl",
    quantity: 1,
    totalAmount: 680.00,
    orderDate: "Oct 25, 2026",
    status: "Delivered",
    shippingAddress: "Cebu City, Cebu",
    payoutStatus: "Released to Seller"
  },
  {
    id: "CM-ORD-5390",
    customerName: "Carlos Miguel",
    product: "Earthy Terracotta Coffee Mug",
    quantity: 1,
    totalAmount: 420.00,
    orderDate: "Oct 20, 2026",
    status: "Delivered",
    shippingAddress: "Davao City, Davao",
    payoutStatus: "Released to Seller"
  }
];
