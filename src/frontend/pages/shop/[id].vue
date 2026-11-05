<script setup lang="ts">
  import type { ShopProduct } from '~/types/shop'
  import ProductGallery from '~/components/products/ProductGallery.vue'
  import { formatMoney } from '~/utils/catalog'

  definePageMeta({ layout: 'shop' })
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
  const unitLabels = {
    item: 'Item',
    pack: 'Pack',
    set: 'Set',
    box: 'Box',
    download: 'Digital download'
  }
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
</script>

<template>
  <section>
    <UButton
      to="/shop"
      label="Back to shop"
      icon="i-lucide-arrow-left"
      color="neutral"
      variant="ghost"
      class="-ml-2.5"
    />

    <EmptyState
      v-if="error && status === 'error'"
      role="alert"
      heading-tag="h1"
      :icon="error.statusCode === 404 ? 'i-lucide-package-search' : 'i-lucide-cloud-off'"
      :title="error.statusCode === 404 ? 'Product not found' : 'Could not load this product'"
      :description="
        error.statusCode === 404
          ? 'This product may no longer be available.'
          : 'Please check your connection and try again.'
      "
    >
      <UButton
        v-if="error.statusCode !== 404"
        label="Try again"
        icon="i-lucide-refresh-cw"
        @click="refresh()"
      />
      <UButton
        v-else
        to="/shop"
        label="Browse the shop"
      />
    </EmptyState>

    <div
      v-else-if="product && status !== 'pending'"
      class="mt-6 grid items-start gap-8 md:grid-cols-2 lg:gap-14"
    >
      <UCard :ui="{ body: 'p-3 sm:p-3' }">
        <ProductGallery
          :images="product.image_urls"
          :name="product.name"
        />
      </UCard>

      <div class="py-2">
        <UBadge
          v-if="product.category"
          :label="product.category.name"
          variant="subtle"
          class="rounded-full"
        />
        <h1
          class="mt-3 text-3xl font-bold tracking-tight text-highlighted wrap-anywhere sm:text-4xl"
        >
          {{ product.name }}
        </h1>
        <p class="mt-4 text-3xl font-bold text-primary">{{ formatMoney(product.price) }}</p>
        <p class="mt-5 leading-relaxed whitespace-pre-wrap text-muted wrap-anywhere">
          {{ product.description || 'A new addition to your everyday study essentials.' }}
        </p>

        <dl
          v-if="product.brand || product.unit"
          class="mt-6 grid grid-cols-2 gap-4 text-sm"
        >
          <div v-if="product.brand">
            <dt class="text-muted">Brand</dt>
            <dd class="mt-1 font-medium text-highlighted">{{ product.brand }}</dd>
          </div>
          <div v-if="product.unit">
            <dt class="text-muted">Sold by</dt>
            <dd class="mt-1 font-medium text-highlighted">{{ unitLabels[product.unit] }}</dd>
          </div>
        </dl>

        <UCard class="mt-8">
          <form
            class="flex flex-col gap-4"
            @submit.prevent="add(product.id, quantity || 1)"
          >
            <div class="flex items-center justify-between gap-3">
              <span class="text-sm font-medium text-highlighted">Availability</span>
              <UBadge
                :label="
                  product.stock_quantity
                    ? `${product.stock_quantity} available`
                    : 'Currently out of stock'
                "
                :color="product.stock_quantity ? 'success' : 'error'"
                variant="subtle"
                class="rounded-full"
              />
            </div>
            <div class="flex flex-wrap items-end gap-3">
              <UFormField
                label="Quantity"
                name="quantity"
              >
                <UInputNumber
                  id="purchase-quantity"
                  v-model="quantity"
                  :min="1"
                  :max="Math.max(1, maxQuantity)"
                  :disabled="busy || !maxQuantity"
                  size="lg"
                  class="w-32"
                />
              </UFormField>
              <UButton
                type="submit"
                size="lg"
                icon="i-lucide-shopping-cart"
                :label="busy ? 'Updating cart...' : 'Add to cart'"
                :loading="busy"
                :disabled="!maxQuantity"
                class="flex-1 justify-center"
              />
            </div>
            <p
              v-if="product.stock_quantity && !maxQuantity"
              class="text-sm text-muted"
            >
              You’ve already added the available quantity.
              <ULink
                to="/cart"
                class="font-semibold text-primary underline"
                >View cart</ULink
              >
            </p>
          </form>
        </UCard>
      </div>
    </div>

    <div
      v-else
      class="grid min-h-87.5 place-items-center"
    >
      <StudyMartLoader label="Loading product" />
    </div>
  </section>
</template>
