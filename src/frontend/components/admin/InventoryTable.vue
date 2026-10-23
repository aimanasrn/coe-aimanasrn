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
      title: 'Recent products',
      page: 1,
      itemsPerPage: 5,
      lastPage: 1,
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
    class="min-w-0 overflow-hidden border border-border rounded-xl bg-white flex flex-col min-h-0"
    aria-labelledby="inventory-title"
  >
    <div
      class="
        flex items-center justify-between gap-4 pt-6 px-6 pb-5 max-md:flex-wrap max-md:gap-2.5
        max-md:pt-5 max-md:px-4.5 max-md:pb-4.5
      "
    >
      <h2
        class="m-0 text-heading text-xl font-semibold leading-[1.4] tracking-[-0.4px] max-md:text-lg"
        id="inventory-title"
      >
        {{ title }}
      </h2>
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
          inline-flex items-center gap-2 rounded-[3px] text-primary text-sm font-medium leading-normal
          cursor-pointer hover:text-primary-hover hover:underline hover:underline-offset-4
        "
      >
        View all products
        <UIcon
          class="w-4.5 h-4.5 flex-none"
          name="i-lucide-arrow-right"
          aria-hidden="true"
        />
      </NuxtLink>
    </div>
    <div
      class="
        flex overflow-x-auto gap-7 my-0 mx-6 border-b border-border max-md:gap-5 max-md:mr-4.5
        max-md:ml-4.5 max-[361px]:gap-3
      "
      role="group"
      aria-label="Filter products by stock"
    >
      <button
        class="
          relative pb-[15px] text-sm leading-[22px] font-medium whitespace-nowrap cursor-pointer
          max-md:text-[14px]
        "
        v-for="tab in tabs"
        :key="tab.value"
        type="button"
        :aria-pressed="stock === tab.value"
        :class="{
          'text-primary after:absolute after:-bottom-px after:inset-x-0 after:h-0.5 after:bg-primary':
            stock === tab.value,
          'text-[#738078]': stock !== tab.value
        }"
        @click="emit('update:stock', tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>
    <label
      class="
        flex items-center gap-2.5 h-12 mt-4.5 mx-6 mb-5.5 py-0 px-3 border border-[#dce3df]
        rounded-lg bg-white text-[#929c96] focus-within:border-primary focus-within:ring-[3px]
        focus-within:ring-primary/10 max-md:mr-4.5 max-md:ml-4.5
      "
    >
      <span class="sr-only">Search products</span>
      <UIcon
        class="w-5 h-5 flex-none"
        name="i-lucide-search"
        aria-hidden="true"
      />
      <input
        class="
          w-full min-w-0 h-full bg-transparent text-heading text-sm outline-none
          placeholder:text-[#929c96]
        "
        type="search"
        placeholder="Search products..."
        maxlength="150"
        :value="search"
        @input="emit('update:search', ($event.target as HTMLInputElement).value)"
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
        class="relative min-w-0 my-0 mx-6 overflow-x-auto max-md:mr-4.5 max-md:ml-4.5"
        role="region"
        aria-label="Products table"
        tabindex="0"
      >
        <table
          class="
            w-full min-w-197.5 border-collapse text-left text-body text-sm leading-normal
            [&_th:first-child]:pl-0 [&_td:first-child]:pl-0 [&_th:last-child]:pr-0
            [&_td:last-child]:pr-0 [&_tbody_tr:last-child_td]:border-b-0
          "
        >
          <thead>
            <tr>
              <th
                class="
                  py-3 px-2.5 border-b border-border text-muted text-[14px] font-medium whitespace-nowrap
                "
                v-for="column in columns"
                :key="column.key"
                scope="col"
                :aria-sort="
                  !viewAll && column.sort
                    ? sort.column === column.sort
                      ? sort.direction === 'asc'
                        ? 'ascending'
                        : 'descending'
                      : 'none'
                    : undefined
                "
              >
                <button
                  v-if="!viewAll && column.sort"
                  type="button"
                  class="
                    inline-flex items-center gap-1.5 rounded cursor-pointer hover:text-primary
                    focus-visible:outline-2 focus-visible:outline-primary focus-visible:outline-offset-4
                  "
                  :class="{ 'text-primary': sort.column === column.sort }"
                  :disabled="loading"
                  :aria-label="`Sort by ${column.label} ${nextSortDirection(column.sort) === 'asc' ? 'ascending' : 'descending'}`"
                  @click="sortBy(column.sort)"
                >
                  {{ column.label }}
                  <UIcon
                    class="size-3.5"
                    :name="
                      sort.column === column.sort
                        ? sort.direction === 'asc'
                          ? 'i-lucide-arrow-up'
                          : 'i-lucide-arrow-down'
                        : 'i-lucide-arrow-up-down'
                    "
                    aria-hidden="true"
                  />
                </button>
                <template v-else>{{ column.label }}</template>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              class="hover:bg-[#fafcfb]"
              v-for="product in products"
              :key="product.id"
            >
              <td class="py-3.5 px-2.5 border-b border-[#edf0ee] align-middle">
                <div class="flex min-w-52.5 items-center gap-3">
                  <span
                    class="
                      grid w-14 h-14.5 flex-none place-items-center overflow-hidden rounded-lg bg-[#f1f5f3]
                      text-[#547060]
                    "
                  >
                    <img
                      class="w-full h-full object-cover"
                      v-if="product.image_urls?.length"
                      :src="product.image_urls[0]"
                      alt=""
                      loading="lazy"
                    />
                    <UIcon
                      class="w-[25px] h-[25px]"
                      v-else
                      name="i-lucide-book-open"
                      aria-hidden="true"
                    />
                  </span>
                  <div>
                    <NuxtLink
                      class="
                        block max-w-60 text-heading text-sm font-medium leading-normal wrap-anywhere
                        hover:text-primary hover:underline hover:underline-offset-3
                      "
                      :to="'/products/' + product.id"
                    >
                      {{ product.name }}
                    </NuxtLink>
                    <span class="block mt-[5px] text-muted text-xs leading-normal wrap-anywhere">{{
                      product.sku
                    }}</span>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-2.5 border-b border-[#edf0ee] align-middle">
                {{ product.category?.name || 'Uncategorized' }}
              </td>
              <td class="whitespace-nowrap py-3.5 px-2.5 border-b border-[#edf0ee] align-middle">
                {{ formatMoney(product.price) }}
              </td>
              <td class="whitespace-nowrap py-3.5 px-2.5 border-b border-[#edf0ee] align-middle">
                {{ product.stock_quantity }} units
              </td>
              <td class="py-3.5 px-2.5 border-b border-[#edf0ee] align-middle">
                <StockBadge
                  :quantity="product.stock_quantity"
                  :threshold="threshold"
                />
              </td>
              <td class="py-3.5 px-2.5 border-b border-[#edf0ee] align-middle">
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                  <EditProductAction :product="product" />
                  <UTooltip text="View product">
                    <AppIconButton
                      :to="'/products/' + product.id"
                      :aria-label="'View product: ' + product.name"
                    >
                      <UIcon
                        name="i-lucide-eye"
                        mode="svg"
                        aria-hidden="true"
                      />
                    </AppIconButton>
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
          flex min-h-[225px] flex-col items-center justify-center gap-3 px-7 py-[50px] text-center
          text-[#7b867f]
        "
      >
        <UIcon
          name="i-lucide-package-search"
          size="32"
          aria-hidden="true"
        />
        <h3 class="text-[17px] font-semibold text-[#374f40]">
          {{
            total === 0 && !search && stock === 'all'
              ? 'Your catalog is ready to grow'
              : 'No products found'
          }}
        </h3>
        <p class="max-w-[320px] text-sm">
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
            inline-flex items-center gap-2 rounded-[3px] text-primary text-sm font-medium leading-normal
            cursor-pointer hover:text-primary-hover hover:underline hover:underline-offset-4
          "
          @click="clearFilters"
        >
          Clear filters
          <UIcon
            class="w-4.5 h-4.5 flex-none"
            name="i-lucide-arrow-right"
            aria-hidden="true"
          />
        </button>
      </div>
    </template>
    <footer
      class="
        flex flex-wrap items-center justify-between gap-3.5 mt-auto py-5.5 px-6 border-t
        border-[#edf0ee] text-muted text-[14px] leading-normal max-md:p-4.5
      "
    >
      <span aria-live="polite"> Showing {{ products.length }} of {{ total }} products </span>
      <div
        v-if="!viewAll"
        class="flex flex-wrap items-center gap-4"
      >
        <div class="flex items-center gap-1.5 whitespace-nowrap">
          <label for="inventory-page-size">Rows per page</label>
          <USelect
            id="inventory-page-size"
            :model-value="itemsPerPage"
            :items="pageSizeOptions"
            :disabled="loading"
            size="md"
            class="w-24"
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
          size="md"
          @update:page="emit('update:page', $event)"
        />
      </div>
    </footer>
  </section>
</template>
