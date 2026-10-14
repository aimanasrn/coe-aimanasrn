<script setup lang="ts">
const props = defineProps<{ quantity: number; threshold: number }>()
const state = computed(() =>
  props.quantity === 0
    ? 'out'
    : props.quantity <= props.threshold
      ? 'low'
      : 'in'
)
const labels = { in: 'In stock', low: 'Low stock', out: 'Out of stock' }
</script>

<template>
  <span
    class="sm-admin-badge"
    :class="'sm-admin-badge-' + state"
  >
    {{ labels[state] }}
  </span>
</template>
