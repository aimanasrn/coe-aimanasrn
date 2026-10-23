<script setup lang="ts">
  import type { Product } from '~/types/catalog'
  defineProps<{ product: Product }>()
</script>

<template>
  <UTooltip text="Edit product">
    <AppIconButton
      :to="`/admin/products/${product.id}/edit`"
      :aria-label="`Edit ${product.name}`"
    >
      <UIcon
        name="i-lucide-pencil"
        mode="svg"
        aria-hidden="true"
      />
    </AppIconButton>
  </UTooltip>
</template>
