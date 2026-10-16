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
    <header class="mb-[31px] flex items-start justify-between gap-6 max-md:mb-7 max-md:flex-col max-md:gap-5 [&_h1]:m-0 [&_h1]:text-[46px] [&_h1]:leading-[1.15] [&_h1]:tracking-[-1.5px] [&_h1]:font-bold [&_h1]:text-heading [&_h1]:max-md:text-[34px] [&_h1]:max-md:tracking-[-1px] [&_p]:mt-1 [&_p]:text-xl [&_p]:leading-[26px] [&_p]:text-[#6b7871] [&_p]:max-md:mt-[9px] [&_p]:max-md:text-[15px] [&>button]:mt-2 [&>button]:max-md:mt-0">
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
