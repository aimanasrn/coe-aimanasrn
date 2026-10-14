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
    <header class="sm-admin-header">
      <div>
        <h1>Dashboard</h1>
        <p>An overview of your catalog and stock.</p>
      </div>
      <AddProductButton />
    </header>
    <div
      v-if="error"
      class="sm-admin-notice sm-admin-notice-error"
      role="alert"
    >
      We could not load your dashboard.
      <button
        class="sm-admin-link"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-if="!dashboard && !error"
      class="sm-admin-loading"
    >
      <StudyMartLoader label="Loading dashboard" />
    </div>
    <template v-if="dashboard">
      <DashboardSummary :summary="dashboard.summary" />
      <div class="sm-admin-dashboard-grid">
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
        <div class="sm-admin-rail">
          <section
            class="sm-admin-panel sm-admin-health"
            aria-labelledby="stock-health-title"
          >
            <h2 id="stock-health-title">Stock health</h2>
            <div
              class="sm-admin-health-heading"
              :class="'sm-admin-health-' + health.state"
            >
              <UIcon
                :name="health.icon"
                size="72"
                aria-hidden="true"
              />
              <div>
                <h3>{{ health.title }}</h3>
                <p class="sm-admin-health-description">
                  {{ health.description }}
                </p>
              </div>
            </div>
            <dl class="sm-admin-stock-counts">
              <div>
                <dt>
                  <span class="sm-admin-dot sm-admin-dot-in" />
                  In stock
                </dt>
                <dd>{{ dashboard.stock_counts.in_stock }}</dd>
              </div>
              <div>
                <dt>
                  <span class="sm-admin-dot sm-admin-dot-low" />
                  Low stock
                </dt>
                <dd>{{ dashboard.stock_counts.low_stock }}</dd>
              </div>
              <div>
                <dt>
                  <span class="sm-admin-dot sm-admin-dot-out" />
                  Out of stock
                </dt>
                <dd>{{ dashboard.stock_counts.out_of_stock }}</dd>
              </div>
            </dl>
            <p class="sm-admin-threshold">
              Low-stock threshold: {{ dashboard.low_stock_threshold }} units
            </p>
          </section>
          <section
            class="sm-admin-panel sm-admin-categories"
            aria-labelledby="categories-title"
          >
            <h2 id="categories-title">Categories</h2>
            <div
              v-for="category in dashboard.categories"
              :key="category.id"
              class="sm-admin-category"
            >
              <div>
                <span>{{ category.name }}</span>
                <span>
                  {{ category.products_count }}
                  {{ category.products_count === 1 ? 'product' : 'products' }}
                </span>
              </div>
              <div class="sm-admin-category-track">
                <div
                  :style="{
                    width:
                      (dashboard.summary.total_products
                        ? ((category.products_count || 0) /
                            dashboard.summary.total_products) *
                          100
                        : 0) + '%'
                  }"
                />
              </div>
            </div>
            <p
              v-if="!dashboard.categories.length"
              class="sm-admin-health-description"
            >
              No categories yet.
            </p>
            <NuxtLink
              to="/admin/categories"
              class="sm-admin-link"
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
