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
    <PageHeader
      title="Products"
      description="Your catalog, all in one place."
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
      title="We could not load products."
      :actions="[
        { label: 'Try again', color: 'error', variant: 'outline', onClick: () => refresh() }
      ]"
    />

    <div
      v-if="!data && (status === 'idle' || status === 'pending')"
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
