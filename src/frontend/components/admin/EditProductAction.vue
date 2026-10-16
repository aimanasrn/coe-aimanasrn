<script setup lang="ts">
import type { Product } from '~/types/catalog'
defineProps<{ product: Product }>()
</script>

<template>
  <UTooltip text="Edit product">
    <NuxtLink
      :to="`/admin/products/${product.id}/edit`"
      class="
        inline-flex size-10 shrink-0 items-center justify-center rounded-md border border-[#dce4df] bg-white p-2.5
        text-[#374f40] cursor-pointer transition-colors duration-150 hover:border-info hover:bg-info hover:text-white
        focus-visible:border-info focus-visible:bg-info focus-visible:text-white focus-visible:outline-2
        focus-visible:outline-info focus-visible:outline-offset-2 [&_.iconify]:size-[18px]
      "
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
