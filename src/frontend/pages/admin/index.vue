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
    description:
      'No products have ' +
      dashboard.value.low_stock_threshold +
      ' units or fewer.',
    icon: 'i-lucide-circle-check',
    state: 'in'
  }
})
</script>

<template>
  <section>
    <header class="mb-[31px] flex items-start justify-between gap-6 max-md:mb-7 max-md:flex-col max-md:gap-5 [&_h1]:m-0 [&_h1]:text-[46px] [&_h1]:leading-[1.15] [&_h1]:tracking-[-1.5px] [&_h1]:font-bold [&_h1]:text-heading [&_h1]:max-md:text-[34px] [&_h1]:max-md:tracking-[-1px] [&_p]:mt-1 [&_p]:text-xl [&_p]:leading-[26px] [&_p]:text-[#6b7871] [&_p]:max-md:mt-[9px] [&_p]:max-md:text-[15px] [&>button]:mt-2 [&>button]:max-md:mt-0">
      <div>
        <h1>Dashboard</h1>
        <p>An overview of your catalog and stock.</p>
      </div>
      <AddProductButton />
    </header>
    <div
      v-if="error"
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      We could not load your dashboard.
      <button
        class="
          inline-flex items-center gap-[9px] text-[15px] font-[550] text-primary cursor-pointer hover:text-primary-hover
          hover:underline hover:underline-offset-4 [&_.iconify]:size-[18px] [&_.iconify]:shrink-0
        "
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-if="!dashboard && !error"
      class="grid min-h-[420px] place-items-center"
    >
      <StudyMartLoader label="Loading dashboard" />
    </div>
    <template v-if="dashboard">
      <DashboardSummary :summary="dashboard.summary" />
      <div class="grid grid-cols-[minmax(0,2.1fr)_minmax(300px,1fr)] items-start gap-5 max-xl:grid-cols-1">
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
        <div class="flex flex-col gap-5 max-xl:grid max-xl:grid-cols-2 max-xl:items-start max-md:flex max-md:items-stretch">
          <section
            class="
              overflow-hidden rounded-lg border border-[#e0e6e2] bg-white [&_h2]:m-0 [&_h2]:text-[26px] [&_h2]:leading-8
              [&_h2]:font-[650] [&_h2]:tracking-[-0.6px] [&_h2]:text-heading [&_h2]:max-md:text-[22px] px-6 pt-[21px]
              pb-[26px]
            "
            aria-labelledby="stock-health-title"
          >
            <h2 id="stock-health-title">Stock health</h2>
            <div
              class="
                mt-6 flex items-center gap-6 [&_.iconify]:size-[72px] [&_.iconify]:shrink-0 [&>div]:min-w-0 [&_p]:mt-[5px]
                [&_p]:text-sm [&_p]:leading-[1.5] [&_h3]:m-0 [&_h3]:text-[19px] [&_h3]:font-[650] [&_h3]:tracking-[-0.35px]
                [&_h3]:text-heading
              "
              :class="{ 'text-primary': health.state === 'in', 'text-[#8d610b]': health.state === 'low', 'text-[#b83d3d]': health.state === 'out', 'text-[#66746c]': health.state === 'empty' }"
            >
              <UIcon
                :name="health.icon"
                size="72"
                aria-hidden="true"
              />
              <div>
                <h3>{{ health.title }}</h3>
                <p class="m-0 text-[15px] leading-[1.65] text-[#7b867f]">
                  {{ health.description }}
                </p>
              </div>
            </div>
            <dl class="my-6 flex flex-col gap-3 border-t border-[#e5eae7] pt-[22px] text-[15px] [&>div]:flex [&>div]:justify-between [&_dt]:flex [&_dt]:items-center [&_dt]:gap-[11px] [&_dt]:text-[#69786f] [&_dd]:font-[550] [&_dd]:text-[#334f3d]">
              <div>
                <dt>
                  <span class="size-3 rounded-full bg-[#28924e]" />
                  In stock
                </dt>
                <dd>{{ dashboard.stock_counts.in_stock }}</dd>
              </div>
              <div>
                <dt>
                  <span class="size-3 rounded-full bg-[#e3af3c]" />
                  Low stock
                </dt>
                <dd>{{ dashboard.stock_counts.low_stock }}</dd>
              </div>
              <div>
                <dt>
                  <span class="size-3 rounded-full bg-[#d96c6c]" />
                  Out of stock
                </dt>
                <dd>{{ dashboard.stock_counts.out_of_stock }}</dd>
              </div>
            </dl>
            <p class="m-0 border-t border-[#e5eae7] pt-5 text-sm leading-[1.5] text-[#859087]">
              Low-stock threshold: {{ dashboard.low_stock_threshold }} units
            </p>
          </section>
          <section
            class="
              overflow-hidden rounded-lg border border-[#e0e6e2] bg-white [&_h2]:m-0 [&_h2]:text-[26px] [&_h2]:leading-8
              [&_h2]:font-[650] [&_h2]:tracking-[-0.6px] [&_h2]:text-heading [&_h2]:max-md:text-[22px] px-6 pt-[21px]
              pb-[26px] [&>a]:mt-[26px] [&>a]:flex [&>a]:border-t [&>a]:border-[#e5eae7] [&>a]:pt-[22px]
            "
            aria-labelledby="categories-title"
          >
            <h2 id="categories-title">Categories</h2>
            <div
              v-for="category in dashboard.categories"
              :key="category.id"
              class="
                mt-6 grid grid-cols-2 items-center gap-4 [&>div:first-child]:flex [&>div:first-child]:flex-col
                [&>div:first-child]:gap-[5px] [&>div:first-child]:text-base [&>div:first-child]:font-medium
                [&>div:first-child_span:last-child]:text-sm [&>div:first-child_span:last-child]:font-normal
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
                  base: 'h-2.5 rounded-[5px] bg-[#edf2ef]',
                  indicator: 'rounded-[inherit] bg-[#2a8d47] motion-reduce:transition-none'
                }"
              />
            </div>
            <p
              v-if="!dashboard.categories.length"
              class="m-0 text-[15px] leading-[1.65] text-[#7b867f]"
            >
              No categories yet.
            </p>
            <NuxtLink
              to="/admin/categories"
              class="
                inline-flex items-center gap-[9px] text-[15px] font-[550] text-primary cursor-pointer hover:text-primary-hover
                hover:underline hover:underline-offset-4 [&_.iconify]:size-[18px] [&_.iconify]:shrink-0
              "
            >
              View categories
              <UIcon
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
