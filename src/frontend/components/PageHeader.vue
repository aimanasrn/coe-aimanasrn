<script setup lang="ts">
  defineProps<{ title: string; description?: string }>()
</script>

<template>
  <header class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div class="min-w-0">
      <slot name="eyebrow" />
      <h1 class="text-2xl font-bold tracking-tight text-highlighted wrap-anywhere sm:text-3xl">
        {{ title }}
      </h1>
      <p
        v-if="description || $slots.description"
        class="mt-1.5 text-muted"
      >
        <slot name="description">{{ description }}</slot>
      </p>
    </div>
    <div
      v-if="$slots.actions"
      class="flex flex-wrap items-center gap-2"
    >
      <slot name="actions" />
    </div>
  </header>
</template>
