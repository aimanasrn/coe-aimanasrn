<script setup lang="ts">
  withDefaults(defineProps<{ label?: string }>(), { label: 'Loading StudyMart...' })
</script>

<template>
  <div
    class="flex flex-col items-center gap-4 p-6"
    role="status"
    :aria-label="label"
    aria-live="polite"
    aria-atomic="true"
  >
    <img
      src="/studymart-mark.svg"
      alt=""
      width="38"
      height="38"
      class="size-9.5 animate-bag-bob motion-reduce:animate-none"
      aria-hidden="true"
    />
    <span class="text-xs text-muted">{{ label }}</span>
    <div
      class="h-0.5 w-28 overflow-hidden rounded-full bg-primary-light"
      aria-hidden="true"
    >
      <div
        class="
          h-full w-2/5 rounded-full bg-primary animate-loading-progress motion-reduce:translate-x-3/4
          motion-reduce:animate-none
        "
      />
    </div>
  </div>
</template>
