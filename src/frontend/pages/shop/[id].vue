<script setup lang="ts">
  import type { ShopProduct } from '~/types/shop'
  definePageMeta({ layout: 'shop', colorMode: 'light' })
  const route = useRoute()
  const { data, error, refresh } = await useFetch<{ data: ShopProduct }>(
    () => '/api/shop/products/' + route.params.id
  )
  const product = computed(() => data.value?.data)
  useHead(() => ({ title: (product.value?.name || 'Product') + ' · StudyMart' }))
  useRequestNotification(error, 'this product')
  const { cart, add, busy } = useCart()
  const quantity = ref(1)
  const selectedImage = ref(0)
  const failed = ref(false)
  const maxQuantity = computed(() =>
    Math.max(
      0,
      Math.min(99, product.value?.stock_quantity || 0) -
        (cart.value?.items.find((item) => item.product.id === product.value?.id)?.quantity || 0)
    )
  )
  watch(maxQuantity, (value) => {
    quantity.value = Math.max(1, Math.min(quantity.value, value))
  })
  watch(selectedImage, () => {
    failed.value = false
  })
</script>
<template>
  <section>
    <NuxtLink
      to="/shop"
      class="shop-back"
      ><UIcon
        name="i-lucide-arrow-left"
        aria-hidden="true"
      />Back to shop</NuxtLink
    >
    <div
      v-if="error"
      class="shop-empty"
      role="alert"
    >
      <h1>{{ error.statusCode === 404 ? 'Product not found' : 'Could not load this product' }}</h1>
      <button
        v-if="error.statusCode !== 404"
        class="catalog-button catalog-button--primary"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-else-if="product"
      class="shop-detail"
    >
      <div>
        <div class="shop-detail__image">
          <img
            v-if="product.image_urls[selectedImage] && !failed"
            :src="product.image_urls[selectedImage]"
            :alt="product.name"
            @error="failed = true"
          />
          <UIcon
            v-else
            name="i-lucide-book-open"
            class="size-24 text-primary/35"
            aria-hidden="true"
          />
        </div>
        <div
          v-if="product.image_urls.length > 1"
          class="shop-thumbnails"
        >
          <button
            v-for="(image, index) in product.image_urls"
            :key="image"
            :aria-label="'View photo ' + (index + 1)"
            :aria-pressed="selectedImage === index"
            @click="selectedImage = index"
          >
            <img
              :src="image"
              alt=""
            />
          </button>
        </div>
      </div>
      <div class="shop-detail__info">
        <p class="text-sm text-primary">{{ product.category?.name }}</p>
        <h1>{{ product.name }}</h1>
        <p class="shop-detail__price">RM {{ Number(product.price).toFixed(2) }}</p>
        <p class="shop-description">
          {{ product.description || 'A new addition to your everyday study essentials.' }}
        </p>
        <dl
          v-if="product.brand || product.unit"
          class="shop-facts"
        >
          <div v-if="product.brand">
            <dt>Brand</dt>
            <dd>{{ product.brand }}</dd>
          </div>
          <div v-if="product.unit">
            <dt>Sold by</dt>
            <dd>{{ product.unit }}</dd>
          </div>
        </dl>
        <p
          class="text-sm"
          :class="product.stock_quantity ? 'text-primary' : 'text-danger'"
        >
          {{
            product.stock_quantity
              ? product.stock_quantity + ' available'
              : 'Currently out of stock'
          }}
        </p>
        <form
          class="shop-purchase"
          @submit.prevent="add(product.id, quantity)"
        >
          <label
            for="purchase-quantity"
            class="text-sm"
            >Quantity</label
          >
          <div class="flex flex-wrap gap-3">
            <input
              id="purchase-quantity"
              v-model.number="quantity"
              type="number"
              min="1"
              :max="maxQuantity"
              required
              :disabled="busy || !maxQuantity"
              class="shop-quantity-input"
            />
            <button
              type="submit"
              class="catalog-button catalog-button--primary"
              :disabled="busy || !maxQuantity"
            >
              {{ busy ? 'Updating cart...' : 'Add to cart'
              }}<UIcon
                name="i-lucide-shopping-cart"
                aria-hidden="true"
              />
            </button>
          </div>
          <p
            v-if="product.stock_quantity && !maxQuantity"
            class="text-xs text-muted"
          >
            You’ve already added the available quantity.
            <NuxtLink
              to="/cart"
              class="text-primary underline"
              >View cart</NuxtLink
            >
          </p>
        </form>
      </div>
    </div>
    <div
      v-else
      class="shop-empty"
    >
      <StudyMartLoader label="Loading product" />
    </div>
  </section>
</template>
