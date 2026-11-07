import { reactive, computed } from 'vue';
import { authService } from '../services/authService.js';

function normalizeUser(u) {
  if (!u) return null;
  const rawRole = (u.role || 'customer').toLowerCase();
  const displayRole = rawRole === 'admin' 
    ? 'Administrator' 
    : (rawRole.charAt(0).toUpperCase() + rawRole.slice(1));

  return {
    ...u,
    id: u.id,
    fullName: u.name || u.fullName || 'CraftMarket Member',
    name: u.name || u.fullName || 'CraftMarket Member',
    email: u.email,
    username: u.username || u.email?.split('@')[0] || 'artisan_member',
    role: displayRole,
    rawRole: rawRole,
    phone: u.phone || u.contact_number || '+63 917 123 4567',
    address: u.address || 'Metro Manila, Philippines',
    bio: u.bio || (rawRole === 'seller' ? 'Independent craftmaker and local handmade artisan.' : 'Conscious supporter of local handmade crafts.'),
    shopName: u.seller_profile?.shop_name || u.sellerProfile?.shop_name || u.shopName || (u.name + "'s Workshop")
  };
}

const state = reactive({
  user: normalizeUser(authService.getUser())
});

export const auth = {
  user: computed(() => state.user),
  isAuthenticated: computed(() => !!state.user && !!authService.getToken()),

  setUser(userData) {
    state.user = normalizeUser(userData);
  },

  login(userData) {
    state.user = normalizeUser(userData);
  },

  async updateProfile(updatedData) {
    try {
      const res = await authService.updateProfile({
        name: updatedData.fullName || updatedData.name,
        phone: updatedData.phone,
        address: updatedData.address,
        avatar: updatedData.avatar,
        shop_name: updatedData.shopName
      });
      state.user = normalizeUser(res);
      return state.user;
    } catch (err) {
      throw err;
    }
  },

  async logout() {
    try {
      await authService.logout();
    } finally {
      state.user = null;
    }
  }
};
