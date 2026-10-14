<script setup lang="ts">
import ProductBreadcrumbs from '~/components/products/ProductBreadcrumbs.vue'
import ProductGallery from '~/components/products/ProductGallery.vue'
import ProductLoadState from '~/components/products/ProductLoadState.vue'
import StockBadge from '~/components/products/StockBadge.vue'
import DeleteProductModal from '~/components/admin/DeleteProductModal.vue'
import { formatMoney } from '~/utils/catalog'

definePageMeta({
  layout: 'admin',
  colorMode: 'light',
  alias: ['/admin/products/:id']
})
const route = useRoute()
const { data, status, error, refresh } = useProduct(() =>
  String(route.params.id)
)
const product = computed(() => data.value?.data)
const loading = computed(
  () => status.value === 'idle' || status.value === 'pending'
)
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
        {
          label: 'Category',
          value: product.value.category?.name ?? 'Uncategorised'
        },
        {
          label: 'Brand / publisher',
          value: product.value.brand || 'Not provided'
        },
        {
          label: 'Barcode / ISBN',
          value: product.value.barcode || 'Not provided'
        },
        {
          label: 'Selling unit',
          value: unitLabels[product.value.unit ?? 'item']
        },
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
      <header class="sm-product-header">
        <div>
          <h1>{{ product.name }}</h1>
          <p>
            {{ product.sku }}
            <span aria-hidden="true">·</span>
            {{ product.category?.name ?? 'Uncategorised' }}
          </p>
        </div>
        <div class="sm-product-header-actions">
          <DeleteProductModal
            :product="product"
            @deleted="navigateTo('/admin/products')"
          />
          <NuxtLink
            :to="editTo"
            class="sm-admin-button"
          >
            <UIcon
              name="i-lucide-pencil"
              mode="svg"
              aria-hidden="true"
            />
            Edit product
          </NuxtLink>
        </div>
      </header>
      <div class="sm-product-detail-grid">
        <ProductGallery
          :images="product.image_urls ?? []"
          :name="product.name"
          :edit-to="editTo"
        />
        <article class="sm-product-details-panel">
          <p class="sm-product-price">{{ formatMoney(product.price) }}</p>
          <div class="sm-product-availability">
            <StockBadge
              :quantity="product.stock_quantity"
              :threshold="5"
            />
            <span>
              {{ product.stock_quantity }}
              {{ product.stock_quantity === 1 ? 'unit' : 'units' }} available
            </span>
          </div>
          <section
            class="sm-product-specifications"
            aria-labelledby="product-details-heading"
          >
            <h2 id="product-details-heading">Product details</h2>
            <dl>
              <div
                v-for="detail in details"
                :key="detail.label"
              >
                <dt>{{ detail.label }}</dt>
                <dd>{{ detail.value }}</dd>
              </div>
            </dl>
          </section>
          <section
            class="sm-product-description"
            aria-labelledby="product-description-heading"
          >
            <h2 id="product-description-heading">Description</h2>
            <p>
              {{ product.description || 'No description has been added yet.' }}
            </p>
          </section>
        </article>
      </div>
    </template>
  </section>
</template>
