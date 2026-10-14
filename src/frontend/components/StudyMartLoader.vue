<script setup lang="ts">
withDefaults(
  defineProps<{
    label?: string
  }>(),
  {
    label: 'Loading StudyMart…'
  }
)
</script>

<template>
  <div
    class="studymart-loader-container"
    role="status"
    aria-live="polite"
  >
    <span class="sr-only">{{ label }}</span>

    <div
      class="bag-wrapper style-3"
      aria-hidden="true"
    >
      <svg
        class="studymart-svg"
        viewBox="0 0 200 200"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          class="bag-body"
          d="M40 70 C40 60, 50 55, 65 55 L135 55 C150 55, 160 60, 160 70 L170 160 C172 175, 160 185, 145 185 L55 185 C40 185, 28 175, 30 160 Z"
          fill="#0A873D"
        />
        <path
          class="bag-handle"
          d="M70 55 C70 25, 130 25, 130 55"
          stroke="#0A873D"
          stroke-width="16"
          stroke-linecap="round"
          fill="none"
        />
        <circle
          cx="70"
          cy="55"
          r="5"
          fill="#FFFFFF"
        />
        <circle
          cx="130"
          cy="55"
          r="5"
          fill="#FFFFFF"
        />

        <g
          class="book-group"
          transform="translate(42, 85)"
        >
          <path
            class="page-left"
            fill="#FFFFFF"
            stroke="#0A873D"
            stroke-width="2.5"
            d="M58 65 C38 52, 15 54, 5 60 L5 12 C18 6, 38 4, 58 18 Z"
          />
          <path
            class="page-right"
            fill="#FFFFFF"
            stroke="#0A873D"
            stroke-width="2.5"
            d="M58 65 C78 52, 101 54, 111 60 L111 12 C98 6, 78 4, 58 18 Z"
          />
          <path
            d="M58 18 L58 65"
            stroke="#0A873D"
            stroke-width="3"
            stroke-linecap="round"
          />
          <path
            class="bookmark-tag"
            fill="#FFB800"
            d="M76 12 L92 12 L92 48 L84 40 L76 48 Z"
          />
        </g>
      </svg>
    </div>

    <div
      class="bag-shadow"
      aria-hidden="true"
    />
    <div
      class="brand-title"
      aria-hidden="true"
    >
      <span class="study">Study</span>
      <span class="mart">Mart</span>
    </div>
    <div
      class="progress-bar"
      aria-hidden="true"
    >
      <div class="progress-fill" />
    </div>
  </div>
</template>

<style scoped>
.studymart-loader-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  gap: 0;
  padding: 24px;
}

.studymart-svg {
  display: block;
  width: 140px;
  height: 140px;
}

.bag-wrapper {
  animation: pulse 2s ease-in-out infinite;
  transform-origin: center bottom;
}

.bag-shadow {
  width: 76px;
  height: 8px;
  border-radius: 50%;
  background: #0a873d;
  opacity: 0.12;
  animation: shadow-pulse 2s ease-in-out infinite;
}

.brand-title {
  margin-top: 20px;
  font-family: ui-sans-serif, system-ui, sans-serif;
  font-size: 28px;
  font-weight: 800;
  line-height: 1.2;
  letter-spacing: -0.04em;
}

.study {
  color: #102d23;
}

.mart {
  color: #0a873d;
}

.progress-bar {
  width: 160px;
  height: 4px;
  margin-top: 20px;
  overflow: hidden;
  border-radius: 999px;
  background: #dcfce7;
}

.progress-fill {
  width: 40%;
  height: 100%;
  border-radius: inherit;
  background: #0a873d;
  animation: loading-progress 1.8s ease-in-out infinite;
}

@keyframes pulse {
  0%,
  100% {
    transform: translateY(0) scale(1);
  }

  50% {
    transform: translateY(-4px) scale(1.035);
  }
}

@keyframes shadow-pulse {
  0%,
  100% {
    transform: scaleX(1);
    opacity: 0.12;
  }

  50% {
    transform: scaleX(0.8);
    opacity: 0.08;
  }
}

@keyframes loading-progress {
  0% {
    transform: translateX(-100%);
  }

  100% {
    transform: translateX(250%);
  }
}

@media (prefers-reduced-motion: reduce) {
  .bag-wrapper,
  .bag-shadow,
  .progress-fill {
    animation: none;
  }

  .progress-fill {
    transform: translateX(75%);
  }
}
</style>
