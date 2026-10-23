<script setup lang="ts">
  import type { ShopProduct } from '~/types/shop'
  definePageMeta({ layout: 'shop', colorMode: 'light' })
  const route = useRoute()
  const { data, status, error, refresh } = useLazyFetch<{ data: ShopProduct }>(
    () => '/api/shop/products/' + route.params.id,
    { server: false }
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
      class="inline-flex items-center gap-2 text-sm text-primary font-semibold"
      ><UIcon
        class="w-4.5 h-4.5"
        name="i-lucide-arrow-left"
        aria-hidden="true"
      />Back to shop</NuxtLink
    >
    <div
      v-if="error && status === 'error'"
      class="
        flex min-h-87.5 flex-col items-center justify-center text-center gap-4.5 py-10 px-4
        [&_h2]:text-[24px] [&_h2]:leading-[1.4] [&_h2]:font-semibold [&_p]:text-muted
        [&_p]:leading-[1.7]
      "
      role="alert"
    >
      <h1>{{ error.statusCode === 404 ? 'Product not found' : 'Could not load this product' }}</h1>
      <AppButton
        v-if="error.statusCode !== 404"
        @click="refresh()"
      >
        Try again
      </AppButton>
    </div>
    <div
      v-else-if="product && status !== 'pending'"
      class="grid grid-cols-2 gap-16 mt-7.5 max-[1101px]:gap-8 max-[761px]:grid-cols-1 max-[761px]:gap-5"
    >
      <div>
        <div class="aspect-square bg-[#edf2ef] rounded-2xl grid place-items-center overflow-hidden">
          <img
            class="w-full h-full object-contain"
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
          class="flex gap-3 mt-4"
        >
          <button
            class="
              w-18 h-18 rounded-lg border-0.5 border-transparent p-[3px] cursor-pointer
              aria-[pressed=true]:border-primary
            "
            v-for="(image, index) in product.image_urls"
            :key="image"
            :aria-label="'View photo ' + (index + 1)"
            :aria-pressed="selectedImage === index"
            @click="selectedImage = index"
          >
            <img
              class="w-full h-full rounded object-cover"
              :src="image"
              alt=""
            />
          </button>
        </div>
      </div>
      <div class="py-5 px-0 max-[761px]:py-2 max-[761px]:px-0">
        <p class="text-sm text-primary">{{ product.category?.name }}</p>
        <h1
          class="
            text-[36px] leading-[1.2] font-[650] tracking-[-1px] text-heading wrap-anywhere mt-3
            max-[761px]:text-[30px]
          "
        >
          {{ product.name }}
        </h1>
        <p class="my-5.5 mx-0 text-primary font-[650] text-[28px]">
          RM {{ Number(product.price).toFixed(2) }}
        </p>
        <p class="text-base leading-[1.8] whitespace-pre-wrap wrap-anywhere text-muted">
          {{ product.description || 'A new addition to your everyday study essentials.' }}
        </p>
        <dl
          v-if="product.brand || product.unit"
          class="my-6 mx-0 flex flex-wrap gap-8 text-[14px]"
        >
          <div v-if="product.brand">
            <dt class="text-muted mb-[5px]">Brand</dt>
            <dd class="text-heading capitalize">{{ product.brand }}</dd>
          </div>
          <div v-if="product.unit">
            <dt class="text-muted mb-[5px]">Sold by</dt>
            <dd class="text-heading capitalize">{{ product.unit }}</dd>
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
          class="grid gap-3 mt-6 pt-6 border-t border-border"
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
              class="w-20 p-3 border border-border bg-white rounded-lg text-base"
            />
            <AppButton
              type="submit"
              :disabled="busy || !maxQuantity"
            >
              {{ busy ? 'Updating cart...' : 'Add to cart'
              }}<UIcon
                name="i-lucide-shopping-cart"
                aria-hidden="true"
              />
            </AppButton>
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
      class="
        flex min-h-87.5 flex-col items-center justify-center text-center gap-4.5 py-10 px-4
        [&_h2]:text-[24px] [&_h2]:leading-[1.4] [&_h2]:font-semibold [&_p]:text-muted
        [&_p]:leading-[1.7]
      "
    >
      <StudyMartLoader label="Loading product" />
    </div>
  </section>
</template>
