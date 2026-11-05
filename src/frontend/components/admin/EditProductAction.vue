<script setup lang="ts">
  import type { Product } from '~/types/catalog'
  defineProps<{ product: Product }>()
</script>

<template>
  <UTooltip text="Edit product">
    <UButton
      :to="`/admin/products/${product.id}/edit`"
      icon="i-lucide-pencil"
      color="neutral"
      variant="ghost"
      :aria-label="`Edit ${product.name}`"
    />
  </UTooltip>
</template>
