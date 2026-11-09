<template>
  <div class="stat-card-box">
    <div class="stat-card-icon" :class="bgClass">
      <!-- Orders / Received -->
      <svg v-if="iconKey === 'orders'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
      </svg>

      <!-- Pending / Clock -->
      <svg v-else-if="iconKey === 'pending'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <polyline points="12 6 12 12 16 14"></polyline>
      </svg>

      <!-- Completed / Check circle -->
      <svg v-else-if="iconKey === 'completed'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>

      <!-- Money / Spent / Revenue -->
      <svg v-else-if="iconKey === 'money'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
        <line x1="1" y1="10" x2="23" y2="10"></line>
      </svg>

      <!-- Products / Inventory -->
      <svg v-else-if="iconKey === 'products'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
        <line x1="12" y1="22.08" x2="12" y2="12"></line>
      </svg>

      <!-- Default / Users -->
      <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
    </div>
    <div>
      <div class="stat-card-title">{{ title }}</div>
      <div class="stat-card-value">{{ value }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    default: ''
  },
  bgClass: {
    type: String,
    default: ''
  }
});

const iconKey = computed(() => {
  if (props.icon) return props.icon;
  const t = (props.title || '').toLowerCase();
  if (t.includes('pending')) return 'pending';
  if (t.includes('completed')) return 'completed';
  if (t.includes('spent') || t.includes('revenue') || t.includes('sales')) return 'money';
  if (t.includes('product')) return 'products';
  if (t.includes('order')) return 'orders';
  return 'default';
});
</script>

<style scoped>
.stat-card-box {
  background: #ffffff;
  border: 1px solid #efe8de;
  border-radius: 14px;
  padding: 20px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  transition: box-shadow 0.2s ease;
}

.stat-card-box:hover {
  box-shadow: 0 4px 18px rgba(0, 0, 0, 0.07);
}

/* Icon container */
.stat-card-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: #f5ede4;
  color: #8b6f5e;
}

/* Background / colour variants passed via bgClass prop */
.forest-bg   { background: #edf4ee; color: #3e6b4a; }
.amber-bg    { background: #fef7ec; color: #b07a2a; }
.terracotta-bg { background: #fceee9; color: #b84f33; }
.sand-bg     { background: #f5f0ea; color: #7a6255; }
.sage-bg     { background: #eef4f0; color: #4e7c5f; }
.rose-bg     { background: #fdeef0; color: #b54a5e; }

/* Text */
.stat-card-title {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #9e8f85;
  margin-bottom: 4px;
}

.stat-card-value {
  font-size: 1.65rem;
  font-weight: 800;
  color: #2d2420;
  line-height: 1.1;
}
</style>
