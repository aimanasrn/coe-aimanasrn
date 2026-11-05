<script setup lang="ts">
  import type { TableColumn } from '@nuxt/ui'
  import type {
    Category,
    CategoryDialogMode,
    CategoryProductFilter,
    CategorySort
  } from '~/types/catalog'
  import TableSortButton from '~/components/admin/TableSortButton.vue'

  const props = defineProps<{
    categories: Category[]
    total: number
    search: string
    filter: CategoryProductFilter
    page: number
    itemsPerPage: number
    lastPage: number
    sort: CategorySort
    loading: boolean
  }>()

  const emit = defineEmits<{
    'update:search': [value: string]
    'update:filter': [value: CategoryProductFilter]
    'update:page': [value: number]
    'update:itemsPerPage': [value: number]
    'update:sort': [value: CategorySort]
    action: [mode: CategoryDialogMode, category: Category, event: MouseEvent]
  }>()

  const tabs = [
    { value: 'all', label: 'All categories' },
    { value: 'with', label: 'With products' },
    { value: 'empty', label: 'Empty' }
  ]
  const columns: TableColumn<Category>[] = [
    { id: 'name', header: 'Category' },
    { id: 'products', header: 'Products' },
    { id: 'actions', header: 'Actions' }
  ]
  const pageSizeOptions = [5, 10, 15, 20, 25, 50]
  const filtered = computed(() => Boolean(props.search.trim()) || props.filter !== 'all')

  function sortBy(column: CategorySort['column']) {
    emit('update:sort', {
      column,
      direction: props.sort.column === column && props.sort.direction === 'asc' ? 'desc' : 'asc'
    })
  }

  function clearFilters() {
    emit('update:search', '')
    emit('update:filter', 'all')
  }
</script>

<template>
  <UCard
    as="section"
    aria-labelledby="category-catalog-title"
    :aria-busy="loading"
    :ui="{ body: 'p-0 sm:p-0', header: 'flex flex-col gap-4' }"
  >
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <h2
          id="category-catalog-title"
          class="text-lg font-semibold text-highlighted"
        >
          Catalog groups
        </h2>
        <span class="text-sm text-muted">
          {{ total }} {{ total === 1 ? 'category' : 'categories' }}
        </span>
      </div>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <UTabs
          :model-value="filter"
          :items="tabs"
          :content="false"
          size="sm"
          aria-label="Filter categories by products"
          @update:model-value="emit('update:filter', $event as CategoryProductFilter)"
        />
        <UInput
          :model-value="search"
          icon="i-lucide-search"
          type="search"
          placeholder="Search categories..."
          aria-label="Search categories"
          :maxlength="150"
          class="w-full sm:w-72"
          @update:model-value="emit('update:search', String($event))"
        />
      </div>
    </template>

    <UTable
      :data="categories"
      :columns="columns"
      :loading="loading"
      :ui="{
        th: 'px-4 py-3 text-sm font-medium text-muted first:pl-6 last:pr-6 last:text-right',
        td: 'px-4 py-3 first:pl-6 last:pr-6 last:text-right'
      }"
    >
      <template #name-header>
        <TableSortButton
          label="Category"
          :active="sort.column === 'name'"
          :direction="sort.direction"
          :disabled="loading"
          @click="sortBy('name')"
        />
      </template>
      <template #products-header>
        <TableSortButton
          label="Products"
          :active="sort.column === 'products_count'"
          :direction="sort.direction"
          :disabled="loading"
          @click="sortBy('products_count')"
        />
      </template>

      <template #name-cell="{ row }">
        <div class="flex min-w-60 items-start gap-3">
          <span
            class="mt-0.5 grid size-9 flex-none place-items-center rounded-md bg-primary/10 text-primary"
          >
            <UIcon
              name="i-lucide-tag"
              class="size-4"
              aria-hidden="true"
            />
          </span>
          <div class="min-w-0">
            <p class="font-semibold text-highlighted wrap-anywhere">{{ row.original.name }}</p>
            <p
              class="mt-0.5 line-clamp-2 max-w-xl text-sm whitespace-normal text-muted wrap-anywhere"
            >
              {{ row.original.description || 'No description added.' }}
            </p>
          </div>
        </div>
      </template>
      <template #products-cell="{ row }">
        <UBadge
          :label="(row.original.products_count ?? 0).toLocaleString()"
          color="neutral"
          variant="subtle"
          class="rounded-full"
        />
      </template>
      <template #actions-cell="{ row }">
        <div class="flex items-center justify-end gap-1 whitespace-nowrap">
          <UTooltip text="View category">
            <UButton
              icon="i-lucide-eye"
              color="neutral"
              variant="ghost"
              :aria-label="`View ${row.original.name}`"
              aria-haspopup="dialog"
              @click="emit('action', 'view', row.original, $event)"
            />
          </UTooltip>
          <UTooltip text="Edit category">
            <UButton
              icon="i-lucide-pencil"
              color="neutral"
              variant="ghost"
              :aria-label="`Edit ${row.original.name}`"
              aria-haspopup="dialog"
              @click="emit('action', 'edit', row.original, $event)"
            />
          </UTooltip>
          <UTooltip text="Delete category">
            <UButton
              icon="i-lucide-trash-2"
              color="error"
              variant="ghost"
              :aria-label="`Delete ${row.original.name}`"
              aria-haspopup="dialog"
              @click="emit('action', 'delete', row.original, $event)"
            />
          </UTooltip>
        </div>
      </template>

      <template #empty>
        <EmptyState
          icon="i-lucide-tags"
          heading-tag="h3"
          :title="
            loading
              ? 'Loading categories...'
              : filtered
                ? 'No categories found'
                : 'No categories yet'
          "
          :description="
            loading
              ? undefined
              : filtered
                ? 'Try another search or product filter.'
                : 'Add your first category to start organizing products.'
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
        <span aria-live="polite">Showing {{ categories.length }} of {{ total }} categories</span>
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex items-center gap-2 whitespace-nowrap">
            <label for="category-page-size">Rows per page</label>
            <USelect
              id="category-page-size"
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
