<script setup lang="ts">
  import type { ShopProduct } from '~/types/shop'
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
  <article class="min-w-0">
    <NuxtLink
      :to="'/shop/' + product.id"
      class="group aspect-square grid place-items-center overflow-hidden rounded-[14px] bg-[#edf2ef] relative"
      :aria-label="'View ' + product.name"
    >
      <img
        class="
          w-full h-full object-cover transition-transform duration-250 group-hover:scale-[1.025]
          motion-reduce:transition-none
        "
        v-if="product.image_urls[0] && !failed"
        :src="product.image_urls[0]"
        :alt="product.name"
        loading="lazy"
        @error="failed = true"
      />
      <UIcon
        v-else
        name="i-lucide-book-open"
        class="size-14 text-primary/35"
        aria-hidden="true"
      />
      <span
        v-if="!product.stock_quantity"
        class="absolute top-3.5 left-3.5 py-1.5 px-2.5 text-[12px] rounded-md bg-white text-muted"
        >Out of stock</span
      >
    </NuxtLink>
    <div class="pt-4.5 px-0.5 pb-2.5">
      <p class="text-xs text-muted">{{ product.category?.name || 'Study essentials' }}</p>
      <h2
        class="text-[17px] leading-[1.4] font-semibold mt-1.5 mx-0 mb-3.5 wrap-anywhere max-[481px]:text-base"
      >
        <NuxtLink :to="'/shop/' + product.id">{{ product.name }}</NuxtLink>
      </h2>
      <div
        class="
          flex items-center justify-between gap-2.5 max-[481px]:items-start max-[481px]:flex-col
          max-[481px]:gap-3
        "
      >
        <p class="text-[17px] text-heading font-[650] max-[481px]:text-base">
          RM {{ Number(product.price).toFixed(2) }}
        </p>
        <button
          type="button"
          class="
            flex items-center gap-[5px] bg-white border border-border rounded-[9px] py-[9px] px-3
            text-[14px] font-semibold text-primary cursor-pointer hover:bg-secondary
            hover:border-[#bdd6c6] disabled:opacity-45 disabled:cursor-not-allowed max-[481px]:w-full
            max-[481px]:justify-center
          "
          :aria-label="'Add ' + product.name + ' to cart'"
          :disabled="busy || !product.stock_quantity"
          @click="add(product.id, 1)"
        >
          <UIcon
            name="i-lucide-plus"
            class="size-5"
            aria-hidden="true"
          /><span>Add</span>
        </button>
      </div>
    </div>
  </article>
</template>
