<script setup lang="ts">
import type { Product, StockFilter } from '~/types/catalog'
import StockBadge from '~/components/products/StockBadge.vue'
import EditProductAction from '~/components/admin/EditProductAction.vue'
import DeleteProductModal from '~/components/admin/DeleteProductModal.vue'
import { formatMoney } from '~/utils/catalog'
withDefaults(
  defineProps<{
    products: Product[]
    total: number
    search: string
    stock: StockFilter
    threshold: number
    loading?: boolean
    title?: string
    viewAll?: boolean
    page?: number
    lastPage?: number
  }>(),
  { title: 'Recent products', page: 1, lastPage: 1 }
)
const emit = defineEmits<{
  'update:search': [value: string]
  'update:stock': [value: StockFilter]
  'update:page': [value: number]
  deleted: []
}>()
const tabs: { value: StockFilter; label: string }[] = [
  { value: 'all', label: 'All products' },
  { value: 'low', label: 'Low stock' },
  { value: 'out', label: 'Out of stock' }
]

function clearFilters() {
  emit('update:search', '')
  emit('update:stock', 'all')
}
</script>

<template>
  <section
    class="sm-admin-panel sm-admin-inventory"
    aria-labelledby="inventory-title"
  >
    <div class="sm-admin-panel-heading">
      <h2 id="inventory-title">{{ title }}</h2>
      <NuxtLink
        v-if="viewAll"
        :to="{
          path: '/admin/products',
          query: {
            q: search || undefined,
            stock: stock === 'all' ? undefined : stock
          }
        }"
        class="sm-admin-link"
      >
        View all products
        <UIcon
          name="i-lucide-arrow-right"
          aria-hidden="true"
        />
      </NuxtLink>
    </div>
    <div
      class="sm-admin-tabs"
      role="group"
      aria-label="Filter products by stock"
    >
      <button
        v-for="tab in tabs"
        :key="tab.value"
        type="button"
        :aria-pressed="stock === tab.value"
        :class="{ selected: stock === tab.value }"
        @click="emit('update:stock', tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>
    <label class="sm-admin-search">
      <span class="sr-only">Search products</span>
      <UIcon
        name="i-lucide-search"
        aria-hidden="true"
      />
      <input
        type="search"
        placeholder="Search products..."
        maxlength="150"
        :value="search"
        @input="
          emit('update:search', ($event.target as HTMLInputElement).value)
        "
      />
    </label>
    <div
      v-if="loading"
      class="sm-admin-table-loading"
    >
      <StudyMartLoader label="Loading products" />
    </div>
    <template v-else>
      <div
        v-if="products.length"
        class="sm-admin-table-scroll"
        role="region"
        aria-label="Products table"
        tabindex="0"
      >
        <table class="sm-admin-table">
          <thead>
            <tr>
              <th scope="col">Product</th>
              <th scope="col">Category</th>
              <th scope="col">Price</th>
              <th scope="col">Stock</th>
              <th scope="col">Status</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="product in products"
              :key="product.id"
            >
              <td>
                <div class="sm-admin-product">
                  <span class="sm-admin-product-icon">
                    <img
                      v-if="product.image_urls?.length"
                      :src="product.image_urls[0]"
                      alt=""
                      loading="lazy"
                    />
                    <UIcon
                      v-else
                      name="i-lucide-book-open"
                      aria-hidden="true"
                    />
                  </span>
                  <div>
                    <NuxtLink :to="'/products/' + product.id">
                      {{ product.name }}
                    </NuxtLink>
                    <span class="sm-admin-sku">{{ product.sku }}</span>
                  </div>
                </div>
              </td>
              <td>{{ product.category?.name || 'Uncategorized' }}</td>
              <td class="sm-admin-nowrap">{{ formatMoney(product.price) }}</td>
              <td class="sm-admin-nowrap">
                {{ product.stock_quantity }} units
              </td>
              <td>
                <StockBadge
                  :quantity="product.stock_quantity"
                  :threshold="threshold"
                />
              </td>
              <td>
                <div class="sm-admin-row-actions">
                  <EditProductAction :product="product" />
                  <UTooltip text="View product">
                    <NuxtLink
                      :to="'/products/' + product.id"
                      :aria-label="'View product: ' + product.name"
                      class="sm-admin-row-action"
                    >
                      <UIcon
                        name="i-lucide-eye"
                        mode="svg"
                        aria-hidden="true"
                      />
                    </NuxtLink>
                  </UTooltip>
                  <DeleteProductModal
                    :product="product"
                    @deleted="emit('deleted')"
                  />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div
        v-else
        class="sm-admin-empty"
      >
        <UIcon
          name="i-lucide-package-search"
          size="32"
          aria-hidden="true"
        />
        <h3>
          {{
            total === 0 && !search && stock === 'all'
              ? 'Your catalog is ready to grow'
              : 'No products found'
          }}
        </h3>
        <p>
          {{
            search
              ? 'Try a different name or SKU.'
              : stock === 'all'
                ? 'Add your first product to get started.'
                : 'There are no products with this stock status.'
          }}
        </p>
        <button
          v-if="search || stock !== 'all'"
          class="sm-admin-link"
          @click="clearFilters"
        >
          Clear filters
          <UIcon
            name="i-lucide-arrow-right"
            aria-hidden="true"
          />
        </button>
      </div>
    </template>
    <footer class="sm-admin-table-footer">
      <span aria-live="polite">
        Showing {{ products.length }} of {{ total }} products
      </span>
      <div
        v-if="lastPage > 1"
        class="sm-admin-pagination"
      >
        <button
          :disabled="page <= 1 || loading"
          aria-label="Previous page"
          @click="emit('update:page', page - 1)"
        >
          <UIcon name="i-lucide-chevron-left" />
        </button>
        <span>{{ page }} / {{ lastPage }}</span>
        <button
          :disabled="page >= lastPage || loading"
          aria-label="Next page"
          @click="emit('update:page', page + 1)"
        >
          <UIcon name="i-lucide-chevron-right" />
        </button>
      </div>
    </footer>
  </section>
</template>
