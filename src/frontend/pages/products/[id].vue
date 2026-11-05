<script setup lang="ts">
  import ProductBreadcrumbs from '~/components/products/ProductBreadcrumbs.vue'
  import ProductGallery from '~/components/products/ProductGallery.vue'
  import ProductLoadState from '~/components/products/ProductLoadState.vue'
  import StockBadge from '~/components/products/StockBadge.vue'
  import DeleteProductModal from '~/components/admin/DeleteProductModal.vue'
  import { formatMoney } from '~/utils/catalog'

  definePageMeta({
    layout: 'admin',
    alias: ['/admin/products/:id']
  })
  const route = useRoute()
  const { data, status, error, refresh } = useProduct(() => String(route.params.id))
  const product = computed(() => data.value?.data)
  const loading = computed(() => status.value === 'idle' || status.value === 'pending')
  const editTo = computed(() => `/admin/products/${product.value?.id}/edit`)
  const unitLabels = {
    item: 'Item',
    pack: 'Pack',
    set: 'Set',
    box: 'Box',
    download: 'Digital download'
  }

  function formatDate(value?: string) {
    if (!value) return 'Not provided'
    return new Intl.DateTimeFormat('en-MY', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      timeZone: 'Asia/Kuala_Lumpur'
    }).format(new Date(value))
  }

  const details = computed(() =>
    product.value
      ? [
          { label: 'SKU', value: product.value.sku },
          { label: 'Category', value: product.value.category?.name ?? 'Uncategorised' },
          { label: 'Brand / publisher', value: product.value.brand || 'Not provided' },
          { label: 'Barcode / ISBN', value: product.value.barcode || 'Not provided' },
          { label: 'Selling unit', value: unitLabels[product.value.unit ?? 'item'] },
          { label: 'Added', value: formatDate(product.value.created_at) },
          { label: 'Last updated', value: formatDate(product.value.updated_at) }
        ]
      : []
  )

  useHead(() => ({
    title: product.value
      ? `${product.value.name} · StudyMart Admin`
      : 'Product details · StudyMart Admin'
  }))
</script>

<template>
  <section>
    <ProductBreadcrumbs
      mode="view"
      :product="product"
    />
    <ProductLoadState
      v-if="loading || error || !product"
      :loading="loading"
      :not-found="error?.statusCode === 404"
      @retry="refresh()"
    />
    <template v-else>
      <PageHeader :title="product.name">
        <template #description>
          {{ product.sku }}
          <span aria-hidden="true">·</span>
          {{ product.category?.name ?? 'Uncategorised' }}
        </template>
        <template #actions>
          <DeleteProductModal
            :product="product"
            labeled
            @deleted="navigateTo('/admin/products')"
          />
          <UButton
            :to="editTo"
            icon="i-lucide-pencil"
            label="Edit product"
          />
        </template>
      </PageHeader>

      <div class="grid items-start gap-6 lg:grid-cols-2">
        <UCard :ui="{ body: 'p-3 sm:p-3' }">
          <ProductGallery
            :images="product.image_urls ?? []"
            :name="product.name"
            :edit-to="editTo"
          />
        </UCard>

        <UCard as="article">
          <p class="text-3xl font-bold tracking-tight text-highlighted">
            {{ formatMoney(product.price) }}
          </p>
          <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-muted">
            <StockBadge
              :quantity="product.stock_quantity"
              :threshold="5"
            />
            <span>
              {{ product.stock_quantity }}
              {{ product.stock_quantity === 1 ? 'unit' : 'units' }} available
            </span>
          </div>

          <USeparator class="my-6" />

          <section aria-labelledby="product-details-heading">
            <h2
              id="product-details-heading"
              class="mb-4 font-semibold text-highlighted"
            >
              Product details
            </h2>
            <dl class="grid gap-3 text-sm">
              <div
                v-for="detail in details"
                :key="detail.label"
                class="grid grid-cols-[minmax(110px,1fr)_minmax(0,1.4fr)] gap-4"
              >
                <dt class="text-muted">{{ detail.label }}</dt>
                <dd class="text-highlighted wrap-anywhere">{{ detail.value }}</dd>
              </div>
            </dl>
          </section>

          <USeparator class="my-6" />

          <section aria-labelledby="product-description-heading">
            <h2
              id="product-description-heading"
              class="mb-3 font-semibold text-highlighted"
            >
              Description
            </h2>
            <p class="leading-relaxed whitespace-pre-wrap text-muted wrap-anywhere">
              {{ product.description || 'No description has been added yet.' }}
            </p>
          </section>
        </UCard>
      </div>
    </template>
  </section>
</template>
