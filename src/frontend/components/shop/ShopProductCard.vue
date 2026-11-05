<script setup lang="ts">
  import type { ShopProduct } from '~/types/shop'
  import { formatMoney } from '~/utils/catalog'

  const props = defineProps<{ product: ShopProduct }>()
  const { add, busy } = useCart()
  const failed = ref(false)
  watch(
    () => props.product.image_urls[0],
    () => {
      failed.value = false
    }
  )
</script>

<template>
  <UCard
    as="article"
    class="group flex min-w-0 flex-col transition-shadow hover:shadow-lg hover:shadow-primary/5"
    :ui="{ body: 'flex flex-1 flex-col p-0 sm:p-0' }"
  >
    <NuxtLink
      :to="'/shop/' + product.id"
      class="relative grid aspect-square place-items-center overflow-hidden bg-elevated"
      :aria-label="'View ' + product.name"
      tabindex="-1"
    >
      <img
        v-if="product.image_urls[0] && !failed"
        class="size-full object-cover transition-transform duration-300 group-hover:scale-[1.03] motion-reduce:transition-none"
        :src="product.image_urls[0]"
        :alt="product.name"
        loading="lazy"
        @error="failed = true"
      />
      <UIcon
        v-else
        name="i-lucide-book-open"
        class="size-12 text-primary/40"
        aria-hidden="true"
      />
      <UBadge
        v-if="!product.stock_quantity"
        label="Out of stock"
        color="neutral"
        variant="solid"
        class="absolute top-3 left-3"
      />
    </NuxtLink>
    <div class="flex flex-1 flex-col gap-1 p-4">
      <p class="text-xs font-medium text-primary">
        {{ product.category?.name || 'Study essentials' }}
      </p>
      <h2 class="line-clamp-2 font-semibold text-highlighted wrap-anywhere">
        <NuxtLink
          :to="'/shop/' + product.id"
          class="hover:text-primary"
        >
          {{ product.name }}
        </NuxtLink>
      </h2>
      <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-3">
        <p class="font-bold text-highlighted">{{ formatMoney(product.price) }}</p>
        <UButton
          icon="i-lucide-plus"
          label="Add"
          size="sm"
          variant="soft"
          :aria-label="'Add ' + product.name + ' to cart'"
          :disabled="busy || !product.stock_quantity"
          @click="add(product.id, 1)"
        />
      </div>
    </div>
  </UCard>
</template>
