<script setup lang="ts">
  import DashboardSummary from '~/components/admin/DashboardSummary.vue'
  import InventoryTable from '~/components/admin/InventoryTable.vue'
  import AddProductButton from '~/components/admin/AddProductButton.vue'
  definePageMeta({ layout: 'admin' })
  useHead({ title: 'Dashboard · StudyMart Admin' })
  const { search, query, stock } = useInventoryFilters()
  const { data, status, error, refresh } = useDashboard(query, stock)
  const dashboard = computed(() => data.value?.data)
  async function deleted() {
    await refresh()
  }
  const health = computed(() => {
    const counts = dashboard.value?.stock_counts
    if (counts?.out_of_stock)
      return {
        title: 'Some shelves are empty',
        description: 'Restock products that are currently unavailable.',
        icon: 'i-lucide-circle-alert',
        state: 'out'
      }
    if (counts?.low_stock)
      return {
        title: 'Time to restock',
        description: 'Some products are running low on stock.',
        icon: 'i-lucide-circle-alert',
        state: 'low'
      }
    if (!dashboard.value?.summary.total_products)
      return {
        title: 'No stock to track yet',
        description: 'Add products to see your stock health.',
        icon: 'i-lucide-package',
        state: 'empty'
      }
    return {
      title: 'All stocked up',
      description: 'No products have ' + dashboard.value.low_stock_threshold + ' units or fewer.',
      icon: 'i-lucide-circle-check',
      state: 'in'
    }
  })
</script>

<template>
  <section>
    <header
      class="
        flex items-start justify-between gap-5 mb-7 [&_>_:is(button,_a)]:flex-none max-md:flex-wrap
        max-md:gap-4.5 max-md:mb-6
      "
    >
      <div>
        <h1
          class="
            m-0 text-heading text-[34px] font-semibold leading-[1.2] tracking-[-0.9px] wrap-anywhere
            max-md:text-[30px]
          "
        >
          Dashboard
        </h1>
        <p class="mt-[9px] text-muted text-base leading-[1.6] max-md:text-sm">
          An overview of your catalog and stock.
        </p>
      </div>
      <AddProductButton />
    </header>
    <div
      v-if="error && status === 'error'"
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      We could not load your dashboard.
      <button
        class="
          inline-flex items-center gap-2 rounded-[3px] text-primary text-sm font-medium leading-normal
          cursor-pointer [&_.iconify]:w-4.5 [&_.iconify]:h-4.5 [&_.iconify]:flex-none
          hover:text-primary-hover hover:underline hover:underline-offset-4
        "
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-if="!dashboard && (status === 'idle' || status === 'pending')"
      class="grid min-h-[420px] place-items-center"
    >
      <StudyMartLoader label="Loading dashboard" />
    </div>
    <template v-if="dashboard">
      <DashboardSummary :summary="dashboard.summary" />
      <div class="grid grid-cols-[minmax(0,_1fr)_310px] items-start gap-6 max-[1481px]:grid-cols-1">
        <InventoryTable
          v-model:search="search"
          v-model:stock="stock"
          :products="dashboard.products"
          :total="dashboard.filtered_total"
          :threshold="dashboard.low_stock_threshold"
          :loading="status === 'pending'"
          view-all
          @deleted="deleted"
        />
        <div
          class="
            flex flex-col gap-6 min-w-0 max-[1481px]:grid max-[1481px]:grid-cols-2
            max-[1481px]:items-start max-md:flex max-md:flex-col max-md:items-stretch
          "
        >
          <section
            class="
              min-w-0 overflow-hidden border border-border rounded-xl bg-white p-6.5 max-md:py-5
              max-md:px-4.5
            "
            aria-labelledby="stock-health-title"
          >
            <h2
              class="
                m-0 text-heading text-xl font-semibold leading-[1.4] tracking-[-0.4px] max-md:text-lg mb-5.5
                max-md:mb-5
              "
              id="stock-health-title"
            >
              Stock health
            </h2>
            <div
              class="mt-5 flex items-start gap-3"
              :class="{
                'text-primary': health.state === 'in',
                'text-[#8d610b]': health.state === 'low',
                'text-[#b83d3d]': health.state === 'out',
                'text-[#66746c]': health.state === 'empty'
              }"
            >
              <UIcon
                class="size-9 shrink-0"
                :name="health.icon"
                size="36"
                aria-hidden="true"
              />
              <div class="min-w-0">
                <h3 class="m-0 text-[17px] font-[650] tracking-[-0.35px] text-heading">
                  {{ health.title }}
                </h3>
                <p class="m-0 text-[#7b867f] mt-[5px] text-sm leading-[1.5]">
                  {{ health.description }}
                </p>
              </div>
            </div>
            <dl class="my-5 flex flex-col gap-3 border-t border-[#e5eae7] pt-5 text-[15px]">
              <div class="flex justify-between">
                <dt class="flex items-center gap-2 text-[#69786f]">
                  <span class="size-2 rounded-full bg-[#28924e]" />
                  In stock
                </dt>
                <dd class="font-[550] text-[#334f3d]">{{ dashboard.stock_counts.in_stock }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="flex items-center gap-2 text-[#69786f]">
                  <span class="size-2 rounded-full bg-[#e3af3c]" />
                  Low stock
                </dt>
                <dd class="font-[550] text-[#334f3d]">{{ dashboard.stock_counts.low_stock }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="flex items-center gap-2 text-[#69786f]">
                  <span class="size-2 rounded-full bg-[#d96c6c]" />
                  Out of stock
                </dt>
                <dd class="font-[550] text-[#334f3d]">{{ dashboard.stock_counts.out_of_stock }}</dd>
              </div>
            </dl>
            <p class="m-0 border-t border-[#e5eae7] pt-5 text-sm leading-[1.5] text-muted">
              Low-stock threshold: {{ dashboard.low_stock_threshold }} units
            </p>
          </section>
          <section
            class="
              min-w-0 overflow-hidden border border-border rounded-xl bg-white p-6.5 max-md:py-5
              max-md:px-4.5
            "
            aria-labelledby="categories-title"
          >
            <h2
              class="
                m-0 text-heading text-xl font-semibold leading-[1.4] tracking-[-0.4px] max-md:text-lg mb-5.5
                max-md:mb-5
              "
              id="categories-title"
            >
              Categories
            </h2>
            <div
              v-for="category in dashboard.categories"
              :key="category.id"
              class="
                mt-5 grid grid-cols-2 items-center gap-4 [&>div:first-child]:flex
                [&>div:first-child]:flex-col [&>div:first-child]:gap-[5px] [&>div:first-child]:text-sm
                [&>div:first-child]:font-medium [&>div:first-child_span:last-child]:text-xs
                [&>div:first-child_span:last-child]:font-normal
                [&>div:first-child_span:last-child]:text-[#859087]
              "
            >
              <div>
                <span>{{ category.name }}</span>
                <span>
                  {{ category.products_count }}
                  {{ category.products_count === 1 ? 'product' : 'products' }}
                </span>
              </div>
              <UProgress
                :model-value="category.products_count || 0"
                :max="dashboard.summary.total_products || 1"
                :aria-label="`${category.name} share of products`"
                :ui="{
                  base: 'h-1.5 rounded-full bg-[#edf2ef]',
                  indicator: 'rounded-[inherit] bg-primary motion-reduce:transition-none'
                }"
              />
            </div>
            <p
              v-if="!dashboard.categories.length"
              class="m-0 text-sm leading-[1.6] text-[#7b867f]"
            >
              No categories yet.
            </p>
            <NuxtLink
              to="/admin/categories"
              class="
                inline-flex items-center gap-2 rounded-[3px] text-primary text-sm font-medium leading-normal
                cursor-pointer hover:text-primary-hover hover:underline hover:underline-offset-4 mt-5 flex
                border-t border-border pt-4.5
              "
            >
              View categories
              <UIcon
                class="w-4.5 h-4.5 flex-none"
                name="i-lucide-arrow-right"
                aria-hidden="true"
              />
            </NuxtLink>
          </section>
        </div>
      </div>
    </template>
  </section>
</template>
