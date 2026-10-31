<template>
  <div class="stat-card-box">
    <div class="stat-card-icon" :class="bgClass">
      {{ icon }}
    </div>
    <div>
      <div class="stat-card-title">{{ title }}</div>
      <div class="stat-card-value">{{ value }}</div>
    </div>
  </div>
</template>

<script setup>
defineProps({
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
    default: '📊'
  },
  bgClass: {
    type: String,
    default: ''
  }
});
</script>
