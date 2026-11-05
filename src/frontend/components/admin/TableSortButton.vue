<script setup lang="ts">
  const props = defineProps<{
    label: string
    active: boolean
    direction: 'asc' | 'desc'
    disabled?: boolean
  }>()

  const icon = computed(() =>
    !props.active
      ? 'i-lucide-arrow-up-down'
      : props.direction === 'asc'
        ? 'i-lucide-arrow-up'
        : 'i-lucide-arrow-down'
  )
  const nextDirection = computed(() =>
    props.active && props.direction === 'asc' ? 'descending' : 'ascending'
  )
</script>

<template>
  <UButton
    :label="label"
    :trailing-icon="icon"
    color="neutral"
    variant="ghost"
    size="sm"
    class="-mx-2.5 font-medium"
    :class="active ? 'text-highlighted' : 'text-muted'"
    :disabled="disabled"
    :aria-label="`Sort by ${label.toLowerCase()}, ${nextDirection}`"
  />
</template>
