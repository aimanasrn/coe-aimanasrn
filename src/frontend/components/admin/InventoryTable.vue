<script setup lang="ts">
import type { Product, ProductSort, ProductSortColumn, StockFilter } from '~/types/catalog'
import StockBadge from '~/components/products/StockBadge.vue'
import EditProductAction from '~/components/admin/EditProductAction.vue'
import DeleteProductModal from '~/components/admin/DeleteProductModal.vue'
import { formatMoney } from '~/utils/catalog'
const props = withDefaults(
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
    itemsPerPage?: number
    lastPage?: number
    sort?: ProductSort
  }>(),
  {
    title: 'Recent products', page: 1, itemsPerPage: 5, lastPage: 1,
    sort: () => ({ column: 'id', direction: 'asc' })
  }
)
const emit = defineEmits<{
  'update:search': [value: string]
  'update:stock': [value: StockFilter]
  'update:page': [value: number]
  'update:itemsPerPage': [value: number]
  'update:sort': [value: ProductSort]
  deleted: []
}>()
const tabs: { value: StockFilter; label: string }[] = [
  { value: 'all', label: 'All products' },
  { value: 'low', label: 'Low stock' },
  { value: 'out', label: 'Out of stock' }
]

const pageSizeOptions = [5, 10, 15, 20, 25, 50]
const columns: { key: string; label: string; sort?: ProductSortColumn }[] = [
  { key: 'product', label: 'Product', sort: 'name' },
  { key: 'category', label: 'Category' },
  { key: 'price', label: 'Price', sort: 'price' },
  { key: 'stock', label: 'Stock', sort: 'stock_quantity' },
  { key: 'status', label: 'Status' },
  { key: 'action', label: 'Action' }
]

function nextSortDirection(column: ProductSortColumn) {
  return props.sort.column === column && props.sort.direction === 'asc' ? 'desc' : 'asc'
}

function sortBy(column: ProductSortColumn) {
  emit('update:sort', { column, direction: nextSortDirection(column) })
}

function clearFilters() {
  emit('update:search', '')
  emit('update:stock', 'all')
}
</script>

<template>
  <section
    class="
      overflow-hidden rounded-lg border border-[#e0e6e2] bg-white [&_h2]:m-0 [&_h2]:text-[26px] [&_h2]:leading-8
      [&_h2]:font-[650] [&_h2]:tracking-[-0.6px] [&_h2]:text-heading [&_h2]:max-md:text-[22px] flex min-h-[618px]
      flex-col max-md:min-h-[520px]
    "
    aria-labelledby="inventory-title"
  >
    <div class="flex items-center justify-between gap-5 px-6 pt-[21px] pb-[25px] max-md:flex-col max-md:items-start max-md:gap-2.5 max-md:px-5 max-md:py-[22px]">
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
        class="
          inline-flex items-center gap-[9px] text-[15px] font-[550] text-primary cursor-pointer hover:text-primary-hover
          hover:underline hover:underline-offset-4 [&_.iconify]:size-[18px] [&_.iconify]:shrink-0
        "
      >
        View all products
        <UIcon
          name="i-lucide-arrow-right"
          aria-hidden="true"
        />
      </NuxtLink>
    </div>
    <div
      class="mx-6 flex gap-[27px] border-b border-[#e4e9e6] max-md:mx-5 max-md:gap-[22px]"
      role="group"
      aria-label="Filter products by stock"
    >
      <button
        v-for="tab in tabs"
        :key="tab.value"
        type="button"
        :aria-pressed="stock === tab.value"
        :class="{ 'text-primary after:absolute after:-bottom-px after:inset-x-0 after:h-0.5 after:bg-primary': stock === tab.value, 'text-[#738078]': stock !== tab.value }"
        class="relative cursor-pointer pb-4 text-[15px] leading-6 font-medium whitespace-nowrap max-md:text-[13px]"
        @click="emit('update:stock', tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>
    <label class="mx-6 mt-[18px] mb-6 flex h-[50px] items-center gap-3 rounded-md border border-[#dce4df] bg-white px-3.5 text-[#849088] focus-within:outline-2 focus-within:outline-primary focus-within:outline-offset-2 max-md:m-5 [&_.iconify]:size-[19px] [&_.iconify]:shrink-0 [&_input]:h-full [&_input]:w-full [&_input]:min-w-0 [&_input]:bg-transparent [&_input]:text-[15px] [&_input]:text-[#37463f] [&_input]:outline-none [&_input]:placeholder:text-[#849088]">
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
      class="grid min-h-[235px] place-items-center p-[26px]"
    >
      <StudyMartLoader label="Loading products" />
    </div>
    <template v-else>
      <div
        v-if="products.length"
        class="relative mx-6 overflow-x-auto max-md:mx-5"
        role="region"
        aria-label="Products table"
        tabindex="0"
      >
        <table class="w-full border-collapse text-left text-[15px] max-md:min-w-[680px] [&_th]:border-b [&_th]:border-[#e5eae7] [&_th]:px-2.5 [&_th]:py-[13px] [&_th]:text-[15px] [&_th]:font-medium [&_th]:text-[#7b867f] [&_th]:whitespace-nowrap [&_th]:first:pl-0 [&_th]:last:pr-0 [&_td]:border-b [&_td]:border-[#e5eae7] [&_td]:px-2.5 [&_td]:py-4 [&_td]:align-middle [&_td]:first:pl-0 [&_td]:last:pr-0">
          <thead>
            <tr>
              <th
                v-for="column in columns"
                :key="column.key"
                scope="col"
                :aria-sort="!viewAll && column.sort
                  ? sort.column === column.sort
                    ? sort.direction === 'asc' ? 'ascending' : 'descending'
                    : 'none'
                  : undefined"
              >
                <button
                  v-if="!viewAll && column.sort"
                  type="button"
                  class="
                    inline-flex items-center gap-1.5 rounded cursor-pointer hover:text-primary focus-visible:outline-2
                    focus-visible:outline-primary focus-visible:outline-offset-4 [&_.iconify]:size-3.5
                  "
                  :class="{ 'text-primary': sort.column === column.sort }"
                  :disabled="loading"
                  :aria-label="`Sort by ${column.label} ${nextSortDirection(column.sort) === 'asc' ? 'ascending' : 'descending'}`"
                  @click="sortBy(column.sort)"
                >
                  {{ column.label }}
                  <UIcon
                    :name="sort.column === column.sort
                      ? sort.direction === 'asc' ? 'i-lucide-arrow-up' : 'i-lucide-arrow-down'
                      : 'i-lucide-arrow-up-down'"
                    aria-hidden="true"
                  />
                </button>
                <template v-else>{{ column.label }}</template>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="product in products"
              :key="product.id"
            >
              <td>
                <div class="flex min-w-[195px] items-center gap-3 [&_a]:text-base [&_a]:font-medium [&_a]:text-[#263e31] [&_a]:whitespace-nowrap [&_a]:hover:text-primary [&_a]:hover:underline">
                  <span class="flex h-[58px] w-14 shrink-0 items-center justify-center rounded-[5px] bg-[#f2f5f3] text-[#45675a] [&_.iconify]:size-[23px] [&_img]:size-full [&_img]:rounded-[inherit] [&_img]:object-cover">
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
                    <span class="mt-1.5 block text-[13px] leading-[1.5] text-[#859088] whitespace-nowrap">{{ product.sku }}</span>
                  </div>
                </div>
              </td>
              <td>{{ product.category?.name || 'Uncategorized' }}</td>
              <td class="whitespace-nowrap">{{ formatMoney(product.price) }}</td>
              <td class="whitespace-nowrap">
                {{ product.stock_quantity }} units
              </td>
              <td>
                <StockBadge
                  :quantity="product.stock_quantity"
                  :threshold="threshold"
                />
              </td>
              <td>
                <div class="flex items-center gap-2 whitespace-nowrap">
                  <EditProductAction :product="product" />
                  <UTooltip text="View product">
                    <NuxtLink
                      :to="'/products/' + product.id"
                      :aria-label="'View product: ' + product.name"
                      class="
                        inline-flex size-10 shrink-0 items-center justify-center rounded-md border border-[#dce4df] bg-white p-2.5
                        text-[#374f40] cursor-pointer transition-colors duration-150 hover:border-primary hover:bg-primary
                        hover:text-white focus-visible:border-primary focus-visible:bg-primary focus-visible:text-white
                        focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-2 [&_.iconify]:size-[18px]
                      "
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
        class="
          flex min-h-[225px] flex-col items-center justify-center gap-3 px-7 py-[50px] text-center text-[#7b867f]
          [&_h3]:text-[17px] [&_h3]:font-semibold [&_h3]:text-[#374f40] [&_p]:max-w-[320px] [&_p]:text-sm
        "
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
          class="
            inline-flex items-center gap-[9px] text-[15px] font-[550] text-primary cursor-pointer hover:text-primary-hover
            hover:underline hover:underline-offset-4 [&_.iconify]:size-[18px] [&_.iconify]:shrink-0
          "
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
    <footer class="mt-auto flex flex-wrap items-center justify-between gap-[15px] px-6 pt-[25px] pb-8 text-sm leading-[1.5] text-[#89928c] max-md:px-5 max-md:py-[22px] max-md:text-xs">
      <span aria-live="polite">
        Showing {{ products.length }} of {{ total }} products
      </span>
      <div
        v-if="!viewAll"
        class="flex flex-wrap items-center gap-4"
      >
        <div class="flex items-center gap-2 whitespace-nowrap">
          <label for="inventory-page-size">Rows per page</label>
          <USelect
            id="inventory-page-size"
            :model-value="itemsPerPage"
            :items="pageSizeOptions"
            :disabled="loading"
            size="sm"
            class="w-20"
            @update:model-value="emit('update:itemsPerPage', Number($event))"
          />
        </div>
        <UPagination
          v-if="lastPage > 1"
          :page="page"
          :total="total"
          :items-per-page="itemsPerPage"
          :disabled="loading"
          :sibling-count="1"
          :ui="{ first: 'hidden sm:inline-flex', last: 'hidden sm:inline-flex' }"
          size="sm"
          @update:page="emit('update:page', $event)"
        />
      </div>
    </footer>
  </section>
</template>
