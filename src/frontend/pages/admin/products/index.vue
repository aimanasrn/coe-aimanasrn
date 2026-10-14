<script setup lang="ts">
import InventoryTable from '~/components/admin/InventoryTable.vue'
import AddProductButton from '~/components/admin/AddProductButton.vue'
definePageMeta({ layout: 'admin' })
useHead({ title: 'Products · StudyMart Admin' })
const { search, query, stock, page } = useInventoryFilters()
const { data, status, error, refresh } = useProducts(query, stock, page)
async function deleted() {
  if (data.value?.data.length === 1 && page.value > 1) {
    page.value -= 1
  } else {
    await refresh()
  }
}
</script>

<template>
  <section>
    <header class="sm-admin-header">
      <div>
        <h1>Products</h1>
        <p>Your catalog, all in one place.</p>
      </div>
      <AddProductButton />
    </header>
    <div
      v-if="error"
      class="sm-admin-notice sm-admin-notice-error"
      role="alert"
    >
      We could not load products.
      <button
        class="sm-admin-link"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-if="!data && !error"
      class="sm-admin-loading"
    >
      <StudyMartLoader label="Loading products" />
    </div>
    <InventoryTable
      v-if="data"
      v-model:search="search"
      v-model:stock="stock"
      v-model:page="page"
      title="Product catalog"
      :products="data.data"
      :total="data.total"
      :threshold="data.low_stock_threshold"
      :loading="status === 'pending'"
      :last-page="data.last_page"
      @deleted="deleted"
    />
  </section>
</template>
