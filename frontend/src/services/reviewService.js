import api from './api.js';

/**
 * CraftMarket Review Service
 * Handles all product review API calls.
 */

/**
 * Fetch all reviews for a product (public).
 */
export async function getProductReviews(productId) {
  const res = await api.get(`/products/${productId}/reviews`);
  return res.data;
}

/**
 * Check if the authenticated user is eligible to review a product.
 */
export async function checkReviewEligibility(productId) {
  try {
    const res = await api.get(`/products/${productId}/reviews/eligibility`);
    return res.data;
  } catch {
    return { eligible: false, reason: 'Not authenticated.' };
  }
}

/**
 * Get the authenticated user's existing review for a product.
 */
export async function getMyReview(productId) {
  try {
    const res = await api.get(`/products/${productId}/reviews/my`);
    return res.data.data;
  } catch {
    return null;
  }
}

/**
 * Submit a new review.
 * @param {number} productId
 * @param {{ rating: number, comment: string }} data
 */
export async function submitReview(productId, data) {
  const res = await api.post(`/products/${productId}/reviews`, data);
  return res.data;
}

/**
 * Update an existing review.
 * @param {number} productId
 * @param {number} reviewId
 * @param {{ rating: number, comment: string }} data
 */
export async function updateReview(productId, reviewId, data) {
  const res = await api.put(`/products/${productId}/reviews/${reviewId}`, data);
  return res.data;
}

/**
 * Delete a review (own review or admin).
 * @param {number} productId
 * @param {number} reviewId
 */
export async function deleteReview(productId, reviewId) {
  const res = await api.delete(`/products/${productId}/reviews/${reviewId}`);
  return res.data;
}

/**
 * Fetch all reviews for the authenticated seller's products.
 */
export async function getSellerReviews() {
  const res = await api.get('/seller/reviews');
  return res.data;
}

/**
 * Fetch all reviews for admin overview.
 */
export async function getAdminReviews() {
  const res = await api.get('/admin/reviews');
  return res.data;
}
