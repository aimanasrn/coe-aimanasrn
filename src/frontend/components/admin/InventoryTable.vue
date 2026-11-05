<script setup lang="ts">
  import type { TableColumn } from '@nuxt/ui'
  import type { Product, ProductSort, ProductSortColumn, StockFilter } from '~/types/catalog'
  import StockBadge from '~/components/products/StockBadge.vue'
  import EditProductAction from '~/components/admin/EditProductAction.vue'
  import DeleteProductModal from '~/components/admin/DeleteProductModal.vue'
  import TableSortButton from '~/components/admin/TableSortButton.vue'
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

  const tabs = [
    { value: 'all', label: 'All products' },
    { value: 'low', label: 'Low stock' },
    { value: 'out', label: 'Out of stock' }
  ]
  const pageSizeOptions = [5, 10, 15, 20, 25, 50]
  const sortColumns: Record<string, ProductSortColumn> = {
    product: 'name',
    price: 'price',
    stock: 'stock_quantity'
  }
  const columns: TableColumn<Product>[] = [
    { id: 'product', header: 'Product' },
    { id: 'category', header: 'Category' },
    { id: 'price', header: 'Price' },
    { id: 'stock', header: 'Stock' },
    { id: 'status', header: 'Status' },
    { id: 'actions', header: 'Actions' }
  ]
  const filtered = computed(() => Boolean(props.search) || props.stock !== 'all')

  function sortBy(column: ProductSortColumn) {
    const direction =
      props.sort.column === column && props.sort.direction === 'asc' ? 'desc' : 'asc'
    emit('update:sort', { column, direction })
  }

  function clearFilters() {
    emit('update:search', '')
    emit('update:stock', 'all')
  }
</script>

<template>
  <UCard
    as="section"
    aria-labelledby="inventory-title"
    :ui="{ body: 'p-0 sm:p-0', header: 'flex flex-col gap-4' }"
  >
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <h2
          id="inventory-title"
          class="text-lg font-semibold text-highlighted"
        >
          {{ title }}
        </h2>
        <UButton
          v-if="viewAll"
          :to="{
            path: '/admin/products',
            query: { q: search || undefined, stock: stock === 'all' ? undefined : stock }
          }"
          label="View all products"
          trailing-icon="i-lucide-arrow-right"
          variant="link"
          class="px-0"
        />
      </div>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <UTabs
          :model-value="stock"
          :items="tabs"
          :content="false"
          size="sm"
          aria-label="Filter products by stock"
          @update:model-value="emit('update:stock', $event as StockFilter)"
        />
        <UInput
          :model-value="search"
          icon="i-lucide-search"
          type="search"
          placeholder="Search name or SKU..."
          aria-label="Search products"
          :maxlength="150"
          class="w-full sm:w-72"
          @update:model-value="emit('update:search', String($event))"
        />
      </div>
    </template>

    <UTable
      :data="products"
      :columns="columns"
      :loading="loading"
      class="min-w-0"
      :ui="{
        th: 'px-4 py-3 text-sm font-medium text-muted first:pl-6 last:pr-6 last:text-right',
        td: 'px-4 py-3 first:pl-6 last:pr-6 last:text-right'
      }"
    >
      <template
        v-for="(sortColumn, columnId) in sortColumns"
        :key="columnId"
        #[`${columnId}-header`]="{ column }"
      >
        <span v-if="viewAll">{{ column.columnDef.header }}</span>
        <TableSortButton
          v-else
          :label="String(column.columnDef.header)"
          :active="sort.column === sortColumn"
          :direction="sort.direction"
          :disabled="loading"
          @click="sortBy(sortColumn)"
        />
      </template>

      <template #product-cell="{ row }">
        <div class="flex min-w-52 items-center gap-3">
          <span
            class="grid size-11 flex-none place-items-center overflow-hidden rounded-md bg-elevated text-dimmed"
          >
            <img
              v-if="row.original.image_urls?.length"
              class="size-full object-cover"
              :src="row.original.image_urls[0]"
              alt=""
              loading="lazy"
            />
            <UIcon
              v-else
              name="i-lucide-book-open"
              class="size-5"
              aria-hidden="true"
            />
          </span>
          <div class="min-w-0">
            <ULink
              :to="'/products/' + row.original.id"
              class="block max-w-60 font-semibold text-highlighted wrap-anywhere hover:text-primary"
            >
              {{ row.original.name }}
            </ULink>
            <span class="block text-xs text-muted wrap-anywhere">{{ row.original.sku }}</span>
          </div>
        </div>
      </template>
      <template #category-cell="{ row }">
        {{ row.original.category?.name || 'Uncategorized' }}
      </template>
      <template #price-cell="{ row }">
        <span class="whitespace-nowrap">{{ formatMoney(row.original.price) }}</span>
      </template>
      <template #stock-cell="{ row }">
        <span class="whitespace-nowrap">{{ row.original.stock_quantity }} units</span>
      </template>
      <template #status-cell="{ row }">
        <StockBadge
          :quantity="row.original.stock_quantity"
          :threshold="threshold"
        />
      </template>
      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
          <EditProductAction :product="row.original" />
          <UTooltip text="View product">
            <UButton
              :to="'/products/' + row.original.id"
              icon="i-lucide-eye"
              color="neutral"
              variant="ghost"
              :aria-label="'View product: ' + row.original.name"
            />
          </UTooltip>
          <DeleteProductModal
            :product="row.original"
            @deleted="emit('deleted')"
          />
        </div>
      </template>

      <template #empty>
        <EmptyState
          icon="i-lucide-package-search"
          heading-tag="h3"
          :title="
            loading
              ? 'Loading products...'
              : total === 0 && !filtered
                ? 'Your catalog is ready to grow'
                : 'No products found'
          "
          :description="
            loading
              ? undefined
              : search
                ? 'Try a different name or SKU.'
                : stock === 'all'
                  ? 'Add your first product to get started.'
                  : 'There are no products with this stock status.'
          "
        >
          <UButton
            v-if="filtered && !loading"
            label="Clear filters"
            color="neutral"
            variant="outline"
            @click="clearFilters"
          />
        </EmptyState>
      </template>
    </UTable>

    <template #footer>
      <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-muted">
        <span aria-live="polite">Showing {{ products.length }} of {{ total }} products</span>
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
            @update:page="emit('update:page', $event)"
          />
        </div>
      </div>
    </template>
  </UCard>
</template>
