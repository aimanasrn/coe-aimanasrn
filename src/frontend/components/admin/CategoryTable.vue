<script setup lang="ts">
  import type {
    Category,
    CategoryDialogMode,
    CategoryProductFilter,
    CategorySort
  } from '~/types/catalog'

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

  const tabs: { value: CategoryProductFilter; label: string }[] = [
    { value: 'all', label: 'All categories' },
    { value: 'with', label: 'With products' },
    { value: 'empty', label: 'Empty categories' }
  ]
  const columns: { key: string; label: string; sort?: CategorySort['column'] }[] = [
    { key: 'name', label: 'Category', sort: 'name' },
    { key: 'products', label: 'Products', sort: 'products_count' },
    { key: 'action', label: 'Action' }
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
  <section
    class="min-w-0 overflow-hidden border border-border rounded-xl bg-white flex flex-col"
    aria-labelledby="category-catalog-title"
    :aria-busy="loading"
  >
    <div
      class="
        flex items-center justify-between gap-4 pt-6 px-6 pb-5 max-md:flex-wrap max-md:gap-2.5
        max-md:pt-5 max-md:px-4.5 max-md:pb-4.5
      "
    >
      <h2
        id="category-catalog-title"
        class="m-0 text-heading text-xl font-semibold leading-[1.4] tracking-[-0.4px] max-md:text-lg"
      >
        Catalog groups
      </h2>
      <span class="text-sm leading-[1.5] text-muted">
        {{ total }} {{ total === 1 ? 'category' : 'categories' }}
      </span>
    </div>
    <div
      role="group"
      aria-label="Filter categories by products"
      class="
        flex overflow-x-auto gap-7 mx-6 border-b border-border max-md:gap-2 max-md:mx-4.5
      "
    >
      <button
        v-for="tab in tabs"
        :key="tab.value"
        type="button"
        :aria-pressed="filter === tab.value"
        class="
          relative pb-[15px] text-sm leading-[22px] font-medium whitespace-nowrap cursor-pointer
          max-md:text-xs
        "
        :class="
          filter === tab.value
            ? 'text-primary after:absolute after:-bottom-px after:inset-x-0 after:h-0.5 after:bg-primary'
            : 'text-[#738078]'
        "
        @click="emit('update:filter', tab.value)"
      >
        {{ tab.label }}
      </button>
    </div>
    <label
      class="
        flex items-center gap-2.5 h-12 mt-4.5 mx-6 mb-5.5 px-3 border border-[#dce3df] rounded-lg
        bg-white text-[#929c96] focus-within:border-primary focus-within:ring-[3px]
        focus-within:ring-primary/10 max-md:mx-4.5
      "
    >
      <span class="sr-only">Search categories</span>
      <UIcon
        name="i-lucide-search"
        class="size-5 flex-none"
        aria-hidden="true"
      />
      <input
        type="search"
        placeholder="Search categories..."
        maxlength="150"
        :value="search"
        class="
          w-full min-w-0 h-full bg-transparent text-heading text-sm outline-none
          placeholder:text-[#929c96]
        "
        @input="emit('update:search', ($event.target as HTMLInputElement).value)"
      />
    </label>
    <div
      v-if="loading"
      class="grid min-h-[235px] place-items-center p-[26px]"
    >
      <StudyMartLoader label="Loading categories" />
    </div>
    <div
      v-else-if="categories.length"
      role="region"
      aria-label="Categories table"
      tabindex="0"
      class="overflow-x-auto"
    >
      <table
        class="
          w-full min-w-150 border-collapse text-left text-body text-sm leading-normal
          [&_tbody_tr:last-child_td]:border-b-0
        "
      >
        <thead>
          <tr>
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              class="
                py-3 px-5.5 border-b border-border text-muted text-[14px] font-medium whitespace-nowrap
                first:pl-6 last:pr-6
              "
              :aria-sort="
                column.sort
                  ? sort.column === column.sort
                    ? sort.direction === 'asc'
                      ? 'ascending'
                      : 'descending'
                    : 'none'
                  : undefined
              "
            >
              <button
                v-if="column.sort"
                type="button"
                class="inline-flex items-center gap-1.5 cursor-pointer hover:text-primary"
                :aria-label="
                  column.sort === 'name'
                    ? 'Sort categories by name'
                    : 'Sort categories by product count'
                "
                @click="sortBy(column.sort)"
              >
                {{ column.label }}
                <UIcon
                  :name="
                    sort.column === column.sort
                      ? sort.direction === 'asc'
                        ? 'i-lucide-arrow-up'
                        : 'i-lucide-arrow-down'
                      : 'i-lucide-arrow-up-down'
                  "
                  class="size-3.5"
                  aria-hidden="true"
                />
              </button>
              <span v-else>{{ column.label }}</span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            class="hover:bg-[#fafcfb]"
            v-for="category in categories"
            :key="category.id"
          >
            <td class="py-3.5 pr-5.5 pl-6 border-b border-[#edf0ee] align-middle">
              <span class="inline-flex items-center gap-3.5 font-[550] wrap-anywhere">
                <UIcon
                  class="size-5 shrink-0 text-primary"
                  name="i-lucide-tags"
                  aria-hidden="true"
                />
                {{ category.name }}
              </span>
              <p
                class="mt-[7px] line-clamp-2 max-w-[540px] text-sm leading-[1.5] text-[#7b867f] wrap-anywhere"
              >
                {{ category.description || 'No description added.' }}
              </p>
            </td>
            <td class="py-3.5 px-5.5 border-b border-[#edf0ee] align-middle">
              {{ (category.products_count ?? 0).toLocaleString() }}
            </td>
            <td class="w-42.5 py-3.5 pr-6 pl-5.5 border-b border-[#edf0ee] align-middle">
              <div class="flex items-center gap-2 whitespace-nowrap">
                <UTooltip text="View category">
                  <AppIconButton
                    type="button"
                    :aria-label="`View ${category.name}`"
                    aria-haspopup="dialog"
                    @click="emit('action', 'view', category, $event)"
                  >
                    <UIcon
                      name="i-lucide-eye"
                      mode="svg"
                      aria-hidden="true"
                    />
                  </AppIconButton>
                </UTooltip>
                <UTooltip text="Edit category">
                  <AppIconButton
                    type="button"
                    :aria-label="`Edit ${category.name}`"
                    aria-haspopup="dialog"
                    @click="emit('action', 'edit', category, $event)"
                  >
                    <UIcon
                      name="i-lucide-pencil"
                      mode="svg"
                      aria-hidden="true"
                    />
                  </AppIconButton>
                </UTooltip>
                <UTooltip text="Delete category">
                  <AppIconButton
                    type="button"
                    variant="danger"
                    :aria-label="`Delete ${category.name}`"
                    aria-haspopup="dialog"
                    @click="emit('action', 'delete', category, $event)"
                  >
                    <UIcon
                      name="i-lucide-trash-2"
                      mode="svg"
                      aria-hidden="true"
                    />
                  </AppIconButton>
                </UTooltip>
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
        text-muted
      "
    >
      <UIcon
        name="i-lucide-tags"
        size="32"
        aria-hidden="true"
      />
      <h3 class="text-[17px] font-semibold text-[#374f40]">
        {{ filtered ? 'No categories found' : 'No categories yet' }}
      </h3>
      <p class="max-w-[320px] text-sm">
        {{
          filtered
            ? 'Try another search or product filter.'
            : 'Add your first category to start organizing products.'
        }}
      </p>
      <button
        v-if="filtered"
        type="button"
        class="
          inline-flex items-center gap-2 text-sm font-medium text-primary cursor-pointer
          hover:underline underline-offset-4
        "
        @click="clearFilters"
      >
        Clear filters
        <UIcon
          name="i-lucide-arrow-right"
          class="size-4.5"
          aria-hidden="true"
        />
      </button>
    </div>
    <footer
      class="
        flex flex-wrap items-center justify-between gap-3.5 mt-auto py-5.5 px-6 border-t
        border-[#edf0ee] text-muted text-[14px] leading-normal max-md:p-4.5
      "
    >
      <span aria-live="polite">Showing {{ categories.length }} of {{ total }} categories</span>
      <div class="flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-1.5 whitespace-nowrap">
          <label for="category-page-size">Rows per page</label>
          <USelect
            id="category-page-size"
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
