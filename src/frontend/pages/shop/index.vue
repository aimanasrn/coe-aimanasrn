<script setup lang="ts">
  import ShopProductCard from '~/components/shop/ShopProductCard.vue'
  import type { ShopPage } from '~/types/shop'
  import type { Category } from '~/types/catalog'
  definePageMeta({ layout: 'shop', colorMode: 'light' })
  useHead({ title: 'Shop · StudyMart' })
  const search = ref('')
  const query = ref('')
  const category = ref('')
  const sort = ref('newest')
  const page = ref(1)
  const { data, status, error, refresh } = useLazyFetch<ShopPage>('/api/shop/products', {
    server: false,
    query: { q: query, category: computed(() => category.value || undefined), sort, page }
  })
  const { data: categories } = useLazyFetch<{ data: Category[] }>('/api/shop/categories', {
    server: false
  })
  useRequestNotification(error, 'the shop')
  watch([category, sort], () => {
    page.value = 1
  })
  function findProducts() {
    page.value = 1
    query.value = search.value.trim()
  }
</script>
<template>
  <section>
    <header class="flex items-end justify-between flex-wrap gap-5 mb-8">
      <div>
        <h1
          class="
            text-[36px] leading-[1.2] font-[650] tracking-[-1px] text-heading wrap-anywhere
            max-[761px]:text-[30px]
          "
        >
          Your study essentials
        </h1>
        <p class="mt-3 text-base leading-[1.6] text-muted">
          Books, stationery, and everything for your next chapter.
        </p>
      </div>
      <span
        v-if="data"
        class="text-sm text-muted"
        >{{ data.total }} products</span
      >
    </header>
    <div class="flex gap-3 mb-8 items-center max-[1101px]:flex-wrap max-[481px]:gap-2.5">
      <form
        class="
          flex items-center gap-3 pl-4 border border-border bg-white rounded-[10px] flex-1 min-w-0
          focus-within:outline-2 focus-within:outline-solid focus-within:outline-primary
          focus-within:outline-offset-2 max-[1101px]:basis-full max-[481px]:pl-3 max-[481px]:gap-2
        "
        role="search"
        @submit.prevent="findProducts"
      >
        <UIcon
          name="i-lucide-search"
          class="size-5 text-muted"
          aria-hidden="true"
        />
        <input
          class="w-full min-w-0 text-sm outline-none py-3.5 px-0 max-[481px]:text-[14px]"
          v-model="search"
          type="search"
          aria-label="Search products"
          placeholder="Search your next essential..."
          maxlength="150"
        />
        <AppButton
          class="m-1 py-2.5 px-4 min-h-10 max-[481px]:p-2.5 max-[481px]:text-xs"
          type="submit"
        >
          Search
        </AppButton>
      </form>
      <select
        v-model="category"
        aria-label="Filter by category"
        class="
          max-w-full pt-3.5 pr-8 pb-3.5 pl-3.5 min-h-12.5 border border-border rounded-[10px] bg-white
          text-sm text-body focus-visible:outline-2 focus-visible:outline-solid
          focus-visible:outline-primary focus-visible:outline-offset-2 max-[761px]:flex-1
          max-[761px]:min-w-0 max-[761px]:text-[14px]
        "
      >
        <option value="">All categories</option>
        <option
          v-for="item in categories?.data"
          :key="item.id"
          :value="String(item.id)"
        >
          {{ item.name }}
        </option>
      </select>
      <select
        v-model="sort"
        aria-label="Sort products"
        class="
          max-w-full pt-3.5 pr-8 pb-3.5 pl-3.5 min-h-12.5 border border-border rounded-[10px] bg-white
          text-sm text-body focus-visible:outline-2 focus-visible:outline-solid
          focus-visible:outline-primary focus-visible:outline-offset-2 max-[761px]:flex-1
          max-[761px]:min-w-0 max-[761px]:text-[14px]
        "
      >
        <option value="newest">Newest first</option>
        <option value="price_asc">Price: low to high</option>
        <option value="price_desc">Price: high to low</option>
        <option value="name">Name: A–Z</option>
      </select>
    </div>
    <div
      v-if="error && status === 'error'"
      class="
        flex min-h-87.5 flex-col items-center justify-center text-center gap-4.5 py-10 px-4
        [&_p]:text-muted [&_p]:leading-[1.7]
      "
      role="alert"
    >
      <h2 class="text-[24px] leading-[1.4] font-semibold">We couldn’t load the shop.</h2>
      <AppButton @click="refresh()"> Try again </AppButton>
    </div>
    <div
      v-else-if="status === 'idle' || status === 'pending'"
      class="
        flex min-h-87.5 flex-col items-center justify-center text-center gap-4.5 py-10 px-4
        [&_h2]:text-[24px] [&_h2]:leading-[1.4] [&_h2]:font-semibold [&_p]:text-muted
        [&_p]:leading-[1.7]
      "
    >
      <StudyMartLoader label="Loading products" />
    </div>
    <div
      v-else-if="!data.data.length"
      class="flex min-h-87.5 flex-col items-center justify-center text-center gap-4.5 py-10 px-4"
    >
      <UIcon
        name="i-lucide-search"
        class="size-10 text-muted"
        aria-hidden="true"
      />
      <h2 class="text-[24px] leading-[1.4] font-semibold">No products found</h2>
      <p class="text-muted leading-[1.7]">Try another search or category.</p>
    </div>
    <div
      v-else
      class="
        grid grid-cols-4 gap-6 max-[1101px]:grid-cols-3 max-[761px]:grid-cols-2 max-[761px]:gap-y-6
        max-[761px]:gap-x-4 max-[481px]:gap-y-6 max-[481px]:gap-x-3.5
      "
      :aria-busy="status === 'pending'"
    >
      <ShopProductCard
        v-for="product in data.data"
        :key="product.id"
        :product="product"
      />
    </div>
    <nav
      v-if="data && data.last_page > 1"
      class="mt-10 flex flex-wrap items-center justify-center gap-4.5 text-[14px]"
      aria-label="Product pages"
    >
      <AppButton
        variant="secondary"
        :disabled="page <= 1 || status === 'pending'"
        @click="page--"
      >
        Previous
      </AppButton>
      <span>Page {{ data.current_page }} of {{ data.last_page }}</span>
      <AppButton
        variant="secondary"
        :disabled="page >= data.last_page || status === 'pending'"
        @click="page++"
      >
        Next
      </AppButton>
    </nav>
  </section>
</template>
