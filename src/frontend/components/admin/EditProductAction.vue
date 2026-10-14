<script setup lang="ts">
import type { Product } from '~/types/catalog'
defineProps<{ product: Product }>()
</script>

<template>
  <UTooltip text="Edit product">
    <NuxtLink
      :to="`/admin/products/${product.id}/edit`"
      class="sm-admin-row-action sm-admin-row-action-info"
      :aria-label="`Edit ${product.name}`"
    >
      <UIcon
        name="i-lucide-pencil"
        mode="svg"
        aria-hidden="true"
      />
    </NuxtLink>
  </UTooltip>
</template>
