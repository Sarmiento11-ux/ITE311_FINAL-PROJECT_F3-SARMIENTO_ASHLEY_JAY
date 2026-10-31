import { sampleProducts } from '../data/products.js';

export const craftProducts = sampleProducts;

export const productService = {
  async getAll() {
    return Promise.resolve(sampleProducts);
  },
  async getById(id) {
    const numId = parseInt(id, 10);
    const product = sampleProducts.find(p => p.id === numId);
    return Promise.resolve(product || null);
  },
  async getFeatured() {
    return Promise.resolve(sampleProducts.slice(0, 4));
  },
  async getRelated(category, currentId) {
    return Promise.resolve(
      sampleProducts.filter(p => p.id !== currentId && p.category === category).slice(0, 4)
    );
  }
};
