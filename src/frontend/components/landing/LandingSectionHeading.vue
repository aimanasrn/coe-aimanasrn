<script setup lang="ts">
  defineProps<{ id: string; eyebrow: string; title: string; description?: string }>()
</script>

<template>
  <div
    v-reveal
    class="mx-auto mb-12 max-w-2xl text-center"
  >
    <p class="text-sm font-semibold tracking-widest text-primary uppercase">{{ eyebrow }}</p>
    <h2
      :id="id"
      class="mt-3 text-3xl font-extrabold tracking-tight text-highlighted sm:text-4xl"
    >
      {{ title }}
    </h2>
    <p
      v-if="description"
      class="mt-4 text-lg text-muted"
    >
      {{ description }}
    </p>
  </div>
</template>
