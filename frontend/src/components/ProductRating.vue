<template>
  <div class="star-rating-display" :class="`size-${size}`">
    <span
      v-for="n in 5"
      :key="n"
      class="star-icon"
      :class="starClass(n)"
      aria-hidden="true"
    >
      <svg :width="starSize" :height="starSize" viewBox="0 0 24 24" fill="none">
        <!-- Full fill -->
        <defs>
          <clipPath :id="`half-${uid}-${n}`">
            <rect x="0" y="0" :width="halfClip(n)" height="24" />
          </clipPath>
        </defs>
        <polygon
          points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
          :fill="fillColor(n)"
          stroke="none"
        />
        <!-- Half-star overlay -->
        <polygon
          v-if="isHalf(n)"
          points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
          fill="#e8ddd4"
          stroke="none"
          :clip-path="`url(#half-${uid}-${n})`"
          style="transform: scaleX(-1); transform-origin: center;"
        />
      </svg>
    </span>
    <span v-if="showCount" class="rating-meta">
      <span class="rating-score">{{ displayRating }}</span>
      <span class="rating-count">({{ count }} {{ count === 1 ? 'review' : 'reviews' }})</span>
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  rating: { type: Number, default: 0 },
  count:  { type: Number, default: 0 },
  size:   { type: String, default: 'md' }, // sm | md | lg
  showCount: { type: Boolean, default: true },
});

// Unique ID to prevent SVG clipPath collisions when multiple instances render
const uid = Math.random().toString(36).slice(2, 7);

const starSize = computed(() => ({ sm: 14, md: 18, lg: 24 }[props.size] ?? 18));
const displayRating = computed(() => props.rating ? props.rating.toFixed(1) : '—');

/** Is star n fully filled? */
function isFull(n) { return props.rating >= n; }

/** Is star n a half-star? */
function isHalf(n) {
  const frac = props.rating - (n - 1);
  return frac > 0 && frac < 1;
}

/** Fill color for each star */
function fillColor(n) {
  if (isFull(n) || isHalf(n)) return '#c97d3a'; // amber-terracotta
  return '#e8ddd4'; // empty
}

/** Half clip width — fills left half of star */
function halfClip(n) {
  if (!isHalf(n)) return 24;
  return Math.round((props.rating - (n - 1)) * 24);
}

function starClass(n) {
  if (isFull(n)) return 'star-full';
  if (isHalf(n)) return 'star-half';
  return 'star-empty';
}
</script>

<style scoped>
.star-rating-display {
  display: inline-flex;
  align-items: center;
  gap: 3px;
}
.star-icon {
  display: inline-flex;
  align-items: center;
  line-height: 1;
}
.rating-meta {
  display: inline-flex;
  align-items: baseline;
  gap: 5px;
  margin-left: 6px;
}
.rating-score {
  font-weight: 700;
  color: #2d2420;
  font-size: 0.95em;
}
.rating-count {
  color: #9e8f85;
  font-size: 0.85em;
}
.size-sm .rating-score { font-size: 0.82em; }
.size-lg .rating-score { font-size: 1.1em; }
</style>
