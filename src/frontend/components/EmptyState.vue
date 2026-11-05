<script setup lang="ts">
  withDefaults(
    defineProps<{
      icon: string
      title: string
      description?: string
      headingTag?: 'h1' | 'h2' | 'h3'
    }>(),
    { headingTag: 'h2' }
  )
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
    <div class="mb-1 grid size-14 place-items-center rounded-full bg-primary/10 text-primary">
      <UIcon
        :name="icon"
        class="size-7"
        aria-hidden="true"
      />
    </div>
    <component
      :is="headingTag"
      class="text-lg font-semibold text-highlighted"
    >
      {{ title }}
    </component>
    <p
      v-if="description"
      class="max-w-sm text-sm text-muted"
    >
      {{ description }}
    </p>
    <div
      v-if="$slots.default"
      class="mt-2 flex flex-wrap justify-center gap-2"
    >
      <slot />
    </div>
  </div>
</template>
