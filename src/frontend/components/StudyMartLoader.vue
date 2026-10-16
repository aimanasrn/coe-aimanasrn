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
    class="relative flex flex-col items-center justify-center gap-0 p-6"
    role="status"
    aria-live="polite"
  >
    <span class="sr-only">{{ label }}</span>

    <div
      class="origin-bottom animate-bag-bob motion-reduce:animate-none"
      aria-hidden="true"
    >
      <svg
        class="block size-[140px]"
        viewBox="0 0 200 200"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M40 70 C40 60, 50 55, 65 55 L135 55 C150 55, 160 60, 160 70 L170 160 C172 175, 160 185, 145 185 L55 185 C40 185, 28 175, 30 160 Z"
          fill="#0A873D"
        />
        <path
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
          transform="translate(42, 85)"
        >
          <path
            fill="#FFFFFF"
            stroke="#0A873D"
            stroke-width="2.5"
            d="M58 65 C38 52, 15 54, 5 60 L5 12 C18 6, 38 4, 58 18 Z"
          />
          <path
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
            fill="#FFB800"
            d="M76 12 L92 12 L92 48 L84 40 L76 48 Z"
          />
        </g>
      </svg>
    </div>

    <div
      class="h-2 w-[76px] rounded-full bg-[#0a873d] opacity-[0.12] animate-bag-shadow motion-reduce:animate-none"
      aria-hidden="true"
    />
    <div
      class="mt-5 font-sans text-[28px] leading-[1.2] font-extrabold tracking-[-0.04em]"
      aria-hidden="true"
    >
      <span class="text-heading">Study</span>
      <span class="text-[#0a873d]">Mart</span>
    </div>
    <div
      class="mt-5 h-1 w-40 overflow-hidden rounded-full bg-primary-light"
      aria-hidden="true"
    >
      <div class="h-full w-2/5 rounded-[inherit] bg-[#0a873d] animate-loading-progress motion-reduce:translate-x-3/4 motion-reduce:animate-none" />
    </div>
  </div>
</template>
