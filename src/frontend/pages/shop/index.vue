<script setup lang="ts">
  import ShopProductCard from '~/components/shop/ShopProductCard.vue'
  import type { ShopPage } from '~/types/shop'
  import type { Category } from '~/types/catalog'

  definePageMeta({ layout: 'shop' })
  useHead({ title: 'Shop · StudyMart' })
  const search = ref('')
  const query = ref('')
  const category = ref('')
  const sort = ref('newest')
  const page = ref(1)
  const sortItems = [
    { label: 'Newest first', value: 'newest' },
    { label: 'Price: low to high', value: 'price_asc' },
    { label: 'Price: high to low', value: 'price_desc' },
    { label: 'Name: A–Z', value: 'name' }
  ]
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

  function clearSearch() {
    search.value = ''
    findProducts()
  }
</script>

<template>
  <section>
    <PageHeader
      title="Your study essentials"
      description="Books, stationery, and everything for your next chapter."
    >
      <template #actions>
        <UBadge
          v-if="data"
          :label="`${data.total} ${data.total === 1 ? 'product' : 'products'}`"
          color="neutral"
          variant="subtle"
          size="lg"
          class="rounded-full"
        />
      </template>
    </PageHeader>

    <div class="mb-8 flex flex-col gap-4">
      <div class="flex flex-col gap-3 sm:flex-row">
        <form
          role="search"
          class="flex flex-1 gap-2"
          @submit.prevent="findProducts"
        >
          <UInput
            v-model="search"
            type="search"
            icon="i-lucide-search"
            placeholder="Search your next essential..."
            aria-label="Search products"
            :maxlength="150"
            size="lg"
            class="flex-1"
          />
          <UButton
            type="submit"
            label="Search"
            size="lg"
          />
        </form>
        <USelect
          v-model="sort"
          :items="sortItems"
          icon="i-lucide-arrow-up-down"
          aria-label="Sort products"
          size="lg"
          class="sm:w-56"
        />
      </div>

      <div
        role="group"
        aria-label="Filter by category"
        class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1"
      >
        <UButton
          label="All"
          size="sm"
          class="shrink-0 rounded-full"
          :color="category === '' ? 'primary' : 'neutral'"
          :variant="category === '' ? 'solid' : 'outline'"
          :aria-pressed="category === ''"
          @click="category = ''"
        />
        <UButton
          v-for="item in categories?.data"
          :key="item.id"
          :label="item.name"
          size="sm"
          class="shrink-0 rounded-full"
          :color="category === String(item.id) ? 'primary' : 'neutral'"
          :variant="category === String(item.id) ? 'solid' : 'outline'"
          :aria-pressed="category === String(item.id)"
          @click="category = String(item.id)"
        />
      </div>

      <p
        v-if="query"
        class="flex flex-wrap items-center gap-2 text-sm text-muted"
      >
        Results for <strong class="text-highlighted">“{{ query }}”</strong>
        <UButton
          label="Clear search"
          size="xs"
          color="neutral"
          variant="link"
          @click="clearSearch"
        />
      </p>
    </div>

    <EmptyState
      v-if="error && status === 'error'"
      role="alert"
      icon="i-lucide-cloud-off"
      title="We couldn’t load the shop."
      description="Please check your connection and try again."
    >
      <UButton
        label="Try again"
        icon="i-lucide-refresh-cw"
        @click="refresh()"
      />
    </EmptyState>
    <div
      v-else-if="status === 'idle' || status === 'pending'"
      class="grid min-h-87.5 place-items-center"
    >
      <StudyMartLoader label="Loading products" />
    </div>
    <EmptyState
      v-else-if="!data?.data.length"
      icon="i-lucide-search-x"
      title="No products found"
      description="Try another search or category."
    />
    <div
      v-else
      class="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-4"
    >
      <ShopProductCard
        v-for="product in data.data"
        :key="product.id"
        :product="product"
      />
    </div>

    <nav
      v-if="data && data.last_page > 1"
      class="mt-10 flex justify-center"
      aria-label="Product pages"
    >
      <UPagination
        v-model:page="page"
        :total="data.total"
        :items-per-page="data.per_page"
        :disabled="status === 'pending'"
        :sibling-count="1"
      />
    </nav>
  </section>
</template>
