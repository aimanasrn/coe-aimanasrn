<script setup lang="ts">
  import InventoryTable from '~/components/admin/InventoryTable.vue'
  import AddProductButton from '~/components/admin/AddProductButton.vue'
  definePageMeta({ layout: 'admin' })
  useHead({ title: 'Products · StudyMart Admin' })
  const { search, query, stock, page, itemsPerPage, sort } = useInventoryFilters()
  const { data, status, error, refresh } = useProducts(query, stock, page, itemsPerPage, sort)
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
    <header class="page-header">
      <div>
        <h1>Products</h1>
        <p>Your catalog, all in one place.</p>
      </div>
      <AddProductButton />
    </header>
    <div
      v-if="error"
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      We could not load products.
      <button
        class="catalog-link"
        @click="refresh()"
      >
        Try again
      </button>
    </div>
    <div
      v-if="!data && !error"
      class="grid min-h-[420px] place-items-center"
    >
      <StudyMartLoader label="Loading products" />
    </div>
    <InventoryTable
      v-if="data"
      v-model:search="search"
      v-model:stock="stock"
      v-model:page="page"
      v-model:items-per-page="itemsPerPage"
      v-model:sort="sort"
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
