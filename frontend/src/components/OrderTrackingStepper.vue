<template>
  <div class="tracking-stepper-container">
    <!-- Cancelled / Refunded Alert Banner -->
    <div v-if="isCancelled" class="stepper-cancelled-banner">
      <span class="cancelled-icon">⚠️</span>
      <div>
        <strong>Order {{ statusLabel }}</strong>
        <p>This handcrafted order was cancelled and any held escrow funds were refunded.</p>
      </div>
    </div>

    <!-- Active Progression Stepper -->
    <div v-else class="stepper-wrapper">
      <div class="stepper-steps">
        <div 
          v-for="(step, idx) in steps" 
          :key="step.key" 
          class="stepper-step"
          :class="{
            'step-completed': currentStepIndex > idx,
            'step-active': currentStepIndex === idx,
            'step-upcoming': currentStepIndex < idx
          }"
        >
          <!-- Connecting Bar Before Node -->
          <div v-if="idx > 0" class="step-connector" :class="{ 'connector-active': currentStepIndex >= idx }"></div>

          <!-- Step Icon Node -->
          <div class="step-node" :title="step.title">
            <span v-if="currentStepIndex > idx" class="node-check">✓</span>
            <span v-else class="node-icon">{{ step.icon }}</span>
          </div>

          <!-- Step Text Labels -->
          <div class="step-text">
            <span class="step-title">{{ step.title }}</span>
            <small class="step-desc">{{ step.desc }}</small>
          </div>
        </div>
      </div>

      <!-- Courier / Tracking Number Sub-bar -->
      <div v-if="trackingNumber" class="stepper-tracking-pill">
        <span class="pill-icon">🚚</span>
        <span class="pill-label">Courier Tracking:</span>
        <strong class="pill-code">{{ trackingNumber }}</strong>
        <span v-if="courier" class="pill-courier">({{ courier }})</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: 'pending'
  },
  trackingNumber: {
    type: String,
    default: ''
  },
  courier: {
    type: String,
    default: ''
  }
});

const steps = [
  { key: 'placed', icon: '📝', title: 'Placed', desc: 'Order received' },
  { key: 'confirmed', icon: '💳', title: 'Confirmed', desc: 'Escrow secured' },
  { key: 'processing', icon: '🔨', title: 'In Workshop', desc: 'Crafting & packing' },
  { key: 'shipped', icon: '🚚', title: 'Dispatched', desc: 'With courier' },
  { key: 'delivered', icon: '📦', title: 'Delivered', desc: 'Escrow released' },
];

const normalizedStatus = computed(() => (props.status || '').toLowerCase().trim());

const isCancelled = computed(() => {
  const s = normalizedStatus.value;
  return s.includes('cancel') || s.includes('refund');
});

const statusLabel = computed(() => {
  if (normalizedStatus.value.includes('refund')) return 'Refunded';
  return 'Cancelled';
});

const currentStepIndex = computed(() => {
  const s = normalizedStatus.value;
  if (s.includes('delivered') || s.includes('complete')) return 4;
  if (s.includes('ship') || s.includes('transit')) return 3;
  if (s.includes('process')) return 2;
  if (s.includes('confirm')) return 1;
  return 0; // pending / placed
});
</script>

<style scoped>
.tracking-stepper-container {
  margin: 16px 0;
  padding: 18px 16px;
  background: #FAF8F5;
  border: 1px solid #E8DFD5;
  border-radius: 12px;
}

.stepper-cancelled-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #FFF5F5;
  border: 1px solid #FEB2B2;
  border-radius: 8px;
  padding: 12px 16px;
  color: #9B2C2C;
}

.stepper-cancelled-banner strong {
  display: block;
  font-size: 0.95rem;
  margin-bottom: 2px;
}

.stepper-cancelled-banner p {
  margin: 0;
  font-size: 0.84rem;
  color: #742A2A;
}

.cancelled-icon {
  font-size: 1.6rem;
}

.stepper-wrapper {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.stepper-steps {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  position: relative;
}

.stepper-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  flex: 1;
  position: relative;
  z-index: 2;
}

.step-connector {
  position: absolute;
  top: 18px;
  right: 50%;
  width: 100%;
  height: 3px;
  background: #E0D7CC;
  z-index: -1;
  transition: background 0.3s ease;
}

.step-connector.connector-active {
  background: #2E7D32;
}

.step-node {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.92rem;
  background: #FFF;
  border: 2px solid #D1C7BD;
  color: #7A6F68;
  transition: all 0.25s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

.step-completed .step-node {
  background: #2E7D32;
  border-color: #2E7D32;
  color: #FFF;
  font-weight: 700;
}

.step-active .step-node {
  background: #C25E3E;
  border-color: #C25E3E;
  color: #FFF;
  transform: scale(1.12);
  box-shadow: 0 4px 12px rgba(194, 94, 62, 0.28);
}

.step-upcoming .step-node {
  background: #F3EFE9;
  border-color: #DCD4CA;
  color: #A0948A;
}

.step-text {
  margin-top: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.step-title {
  font-size: 0.82rem;
  font-weight: 700;
  color: #2C221E;
}

.step-active .step-title {
  color: #C25E3E;
}

.step-desc {
  font-size: 0.72rem;
  color: #7A6F68;
  margin-top: 2px;
}

.stepper-tracking-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #FFF;
  border: 1px solid #D6EBD6;
  border-radius: 20px;
  padding: 6px 14px;
  font-size: 0.84rem;
  color: #2C221E;
  align-self: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.pill-icon {
  font-size: 1rem;
}

.pill-label {
  color: #5C524B;
  font-weight: 600;
}

.pill-code {
  font-family: monospace;
  background: #E8F5E9;
  color: #1B5E20;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.85rem;
}

.pill-courier {
  color: #7A6F68;
  font-size: 0.78rem;
}

@media (max-width: 640px) {
  .step-node {
    width: 30px;
    height: 30px;
    font-size: 0.8rem;
  }
  .step-connector {
    top: 15px;
  }
  .step-title {
    font-size: 0.72rem;
  }
  .step-desc {
    display: none;
  }
}
</style>
