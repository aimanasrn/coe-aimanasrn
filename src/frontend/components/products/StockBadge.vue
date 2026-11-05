<script setup lang="ts">
  const props = defineProps<{ quantity: number; threshold: number }>()
  const badge = computed(() =>
    props.quantity === 0
      ? { label: 'Out of stock', color: 'error' as const }
      : props.quantity <= props.threshold
        ? { label: 'Low stock', color: 'warning' as const }
        : { label: 'In stock', color: 'success' as const }
  )
</script>

<template>
  <UBadge
    :label="badge.label"
    :color="badge.color"
    variant="subtle"
    class="rounded-full"
  />
</template>
