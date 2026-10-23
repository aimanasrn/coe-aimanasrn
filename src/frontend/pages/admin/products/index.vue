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
          Products
        </h1>
        <p class="mt-[9px] text-muted text-base leading-[1.6] max-md:text-sm">
          Your catalog, all in one place.
        </p>
      </div>
      <AddProductButton />
    </header>
    <div
      v-if="error && status === 'error'"
      class="mb-5 rounded-[7px] border px-[18px] py-3.5 text-sm border-[#f0d5d5] bg-[#fff4f4] text-[#a03939]"
      role="alert"
    >
      We could not load products.
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
