import { reactive, computed } from 'vue';

const getStoredUser = () => {
  try {
    const raw = sessionStorage.getItem('craftMarketActiveUser');
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    return null;
  }
};

const state = reactive({
  user: getStoredUser()
});

export const auth = {
  user: computed(() => state.user),
  isAuthenticated: computed(() => !!state.user),
  
  login(userData) {
    state.user = userData;
    sessionStorage.setItem('craftMarketActiveUser', JSON.stringify(userData));
  },

  updateProfile(updatedData) {
    state.user = { ...state.user, ...updatedData };
    sessionStorage.setItem('craftMarketActiveUser', JSON.stringify(state.user));
  },

  logout() {
    state.user = null;
    sessionStorage.removeItem('craftMarketActiveUser');
  }
};
