<script setup lang="ts">
  import DashboardSummary from '~/components/admin/DashboardSummary.vue'
  import InventoryTable from '~/components/admin/InventoryTable.vue'
  import AddProductButton from '~/components/admin/AddProductButton.vue'

  definePageMeta({ layout: 'admin' })
  useHead({ title: 'Dashboard · StudyMart Admin' })
  const { search, query, stock } = useInventoryFilters()
  const { data, status, error, refresh } = useDashboard(query, stock)
  const dashboard = computed(() => data.value?.data)

  const health = computed(() => {
    const counts = dashboard.value?.stock_counts
    if (counts?.out_of_stock)
      return {
        title: 'Some shelves are empty',
        description: 'Restock products that are currently unavailable.',
        icon: 'i-lucide-circle-alert',
        tone: 'text-error'
      }
    if (counts?.low_stock)
      return {
        title: 'Time to restock',
        description: 'Some products are running low on stock.',
        icon: 'i-lucide-circle-alert',
        tone: 'text-warning'
      }
    if (!dashboard.value?.summary.total_products)
      return {
        title: 'No stock to track yet',
        description: 'Add products to see your stock health.',
        icon: 'i-lucide-package',
        tone: 'text-muted'
      }
    return {
      title: 'All stocked up',
      description: `No products have ${dashboard.value.low_stock_threshold} units or fewer.`,
      icon: 'i-lucide-circle-check',
      tone: 'text-success'
    }
  })

  const stockRows = computed(() => [
    { label: 'In stock', value: dashboard.value?.stock_counts.in_stock, dot: 'bg-success' },
    { label: 'Low stock', value: dashboard.value?.stock_counts.low_stock, dot: 'bg-warning' },
    { label: 'Out of stock', value: dashboard.value?.stock_counts.out_of_stock, dot: 'bg-error' }
  ])
</script>

<template>
  <section>
    <PageHeader
      title="Dashboard"
      description="An overview of your catalog and stock."
    >
      <template #actions>
        <AddProductButton />
      </template>
    </PageHeader>

    <UAlert
      v-if="error && status === 'error'"
      class="mb-6"
      color="error"
      variant="subtle"
      icon="i-lucide-circle-alert"
      title="We could not load your dashboard."
      :actions="[
        { label: 'Try again', color: 'error', variant: 'outline', onClick: () => refresh() }
      ]"
    />

    <div
      v-if="!dashboard && (status === 'idle' || status === 'pending')"
      class="grid min-h-[420px] place-items-center"
    >
      <StudyMartLoader label="Loading dashboard" />
    </div>

    <template v-if="dashboard">
      <DashboardSummary :summary="dashboard.summary" />
      <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <InventoryTable
          v-model:search="search"
          v-model:stock="stock"
          :products="dashboard.products"
          :total="dashboard.filtered_total"
          :threshold="dashboard.low_stock_threshold"
          :loading="status === 'pending'"
          view-all
          @deleted="refresh()"
        />

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-1">
          <UCard
            as="section"
            aria-labelledby="stock-health-title"
          >
            <template #header>
              <h2
                id="stock-health-title"
                class="text-lg font-semibold text-highlighted"
              >
                Stock health
              </h2>
            </template>
            <div class="flex items-start gap-3">
              <UIcon
                :name="health.icon"
                class="size-8 shrink-0"
                :class="health.tone"
                aria-hidden="true"
              />
              <div class="min-w-0">
                <h3 class="font-semibold text-highlighted">{{ health.title }}</h3>
                <p class="mt-1 text-sm text-muted">{{ health.description }}</p>
              </div>
            </div>
            <USeparator class="my-5" />
            <dl class="flex flex-col gap-3 text-sm">
              <div
                v-for="row in stockRows"
                :key="row.label"
                class="flex items-center justify-between"
              >
                <dt class="flex items-center gap-2 text-muted">
                  <span
                    class="size-2 rounded-full"
                    :class="row.dot"
                  />
                  {{ row.label }}
                </dt>
                <dd class="font-semibold text-highlighted">{{ row.value }}</dd>
              </div>
            </dl>
            <template #footer>
              <p class="text-sm text-muted">
                Low-stock threshold: {{ dashboard.low_stock_threshold }} units
              </p>
            </template>
          </UCard>

          <UCard
            as="section"
            aria-labelledby="categories-title"
          >
            <template #header>
              <div class="flex items-center justify-between gap-3">
                <h2
                  id="categories-title"
                  class="text-lg font-semibold text-highlighted"
                >
                  Categories
                </h2>
                <UButton
                  to="/admin/categories"
                  label="View all"
                  trailing-icon="i-lucide-arrow-right"
                  variant="link"
                  class="px-0"
                />
              </div>
            </template>
            <ul
              v-if="dashboard.categories.length"
              class="flex flex-col gap-4"
            >
              <li
                v-for="category in dashboard.categories"
                :key="category.id"
              >
                <div class="mb-1.5 flex items-baseline justify-between gap-3 text-sm">
                  <span class="truncate font-medium text-highlighted">{{ category.name }}</span>
                  <span class="whitespace-nowrap text-xs text-muted">
                    {{ category.products_count }}
                    {{ category.products_count === 1 ? 'product' : 'products' }}
                  </span>
                </div>
                <UProgress
                  :model-value="category.products_count || 0"
                  :max="dashboard.summary.total_products || 1"
                  size="sm"
                  :aria-label="`${category.name} share of products`"
                />
              </li>
            </ul>
            <p
              v-else
              class="text-sm text-muted"
            >
              No categories yet.
            </p>
          </UCard>
        </div>
      </div>
    </template>
  </section>
</template>
