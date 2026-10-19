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
  <article class="shop-product">
    <NuxtLink
      :to="'/shop/' + product.id"
      class="shop-product__image"
      :aria-label="'View ' + product.name"
    >
      <img
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
        class="shop-sold-out"
        >Out of stock</span
      >
    </NuxtLink>
    <div class="shop-product__body">
      <p class="text-xs text-muted">{{ product.category?.name || 'Study essentials' }}</p>
      <h2>
        <NuxtLink :to="'/shop/' + product.id">{{ product.name }}</NuxtLink>
      </h2>
      <div class="shop-product__bottom">
        <p class="shop-price">RM {{ Number(product.price).toFixed(2) }}</p>
        <button
          type="button"
          class="shop-add"
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
